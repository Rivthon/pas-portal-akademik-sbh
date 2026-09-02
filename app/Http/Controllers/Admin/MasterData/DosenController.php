<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Services\DosenCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class DosenController extends Controller
{
    public function __construct(private readonly DosenCodeService $dosenCodeService)
    {
        $this->middleware('permission:dosen-list|dosen-create|dosen-edit|dosen-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:dosen-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:dosen-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:dosen-delete', ['only' => ['destroy']]);
        $this->middleware('permission:dosen-reset-password', ['only' => ['resetPassword']]);
    }

    public function index(Request $request)
    {
        // Ambil input pencarian (jika ada)
        $search = $request->input('search');

        // Query data dosen dengan filter pencarian
        $query = Dosen::with('programStudi');

        if ($search) {
            $query->where('nama', 'like', '%'.$search.'%')
                ->orWhere('kd_dosen', 'like', '%'.$search.'%')
                ->orWhereHas('programStudi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%'.$search.'%');
                });
        }

        // Pagination hasil query
        $dosen = $query->paginate(10)->appends($request->query());

        // Hitung nomor indeks untuk paginasi
        $pageIndex = ($dosen->currentPage() - 1) * $dosen->perPage();

        // Kirim data ke view biasa
        return view('admin.master-data.dosen.index', compact('dosen', 'pageIndex', 'search'));
    }

    public function create(): View
    {
        $programStudi = ProgramStudi::all();

        return view('admin.master-data.dosen.create', compact('programStudi'));
    }

    public function edit(Dosen $dosen): View
    {
        $programStudi = ProgramStudi::all();

        return view('admin.master-data.dosen.edit', compact('dosen', 'programStudi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'nidn' => 'nullable|string|max:20',
            'email' => 'required|email|unique:dosen,email',
            'password' => 'required|string|min:8', // Validasi tanpa konfirmasi
            'tempat' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'no_telp' => 'nullable|string|max:13',
            'alamat' => 'nullable|string|max:120',
        ]);

        if (! $this->dosenCodeService->formatForJurusan($request->jurusan_id)) {
            return back()
                ->withInput()
                ->withErrors([
                    'jurusan_id' => 'Format kode dosen untuk program studi ini belum dikonfigurasi.',
                ]);
        }

        $dosen = DB::transaction(function () use ($request): Dosen {
            Dosen::query()
                ->where('jurusan_id', $request->jurusan_id)
                ->lockForUpdate()
                ->get(['dosen_id']);

            $dosen = new Dosen;
            $dosen->fill($request->except(['password', 'kd_dosen']));
            $dosen->kd_dosen = $this->dosenCodeService->nextCode($request->jurusan_id);
            $dosen->password = bcrypt($request->password);

            if ($request->hasFile('avatar')) {
                $dosen->avatar = $request->file('avatar')->store('avatars', 'public');
            }

            $dosen->save();

            return $dosen;
        });
        activity_log('tambah_dosen', 'Admin menambah dosen baru: '.$dosen->nama);
        Alert::toast('Dosen berhasil ditambahkan.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.dosen.index');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'nidn' => 'nullable|string|max:20',
            'email' => 'required|email|unique:dosen,email,'.$id.',dosen_id',
            'password' => 'nullable|string|min:8', // Password opsional tanpa konfirmasi
            'tempat' => 'nullable|string|max:50',
            'tanggal_lahir' => 'nullable|date',
            'no_telp' => 'nullable|string|max:13',
            'alamat' => 'nullable|string|max:120',
        ]);

        $dosen = Dosen::findOrFail($id);
        $dosen->fill($request->except('password')); // Kecualikan password dari mass assignment

        if ($request->filled('password')) {
            $dosen->password = bcrypt($request->password); // Hash password jika diisi
        }

        if ($request->hasFile('avatar')) {
            if ($dosen->avatar) {
                \Storage::disk('public')->delete($dosen->avatar);
            }

            $filePath = $request->file('avatar')->store('avatars', 'public');
            $dosen->avatar = $filePath;
        }

        $dosen->save();
        activity_log('update_dosen', 'Admin memperbarui dosen: '.$dosen->nama);
        Alert::toast('Dosen berhasil diperbaharui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.dosen.index');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        activity_log('hapus_dosen', 'Admin menghapus dosen: '.$dosen->nama);
        $dosen->delete();

        Alert::toast('Dosen berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.dosen.index');
    }

    public function resetPassword(Dosen $dosen): RedirectResponse
    {
        $nidn = trim((string) $dosen->nidn);
        $code = trim((string) $dosen->kd_dosen);

        if ($this->isUsableResetValue($nidn)) {
            $newPassword = $nidn;
            $source = 'NIDN';
        } elseif ($this->isUsableResetValue($code)) {
            $newPassword = $code;
            $source = 'kode dosen';
        } else {
            $newPassword = Str::password(12, letters: true, numbers: true, symbols: false);
            $source = 'password acak';
        }

        $dosen->forceFill([
            'password' => Hash::make($newPassword),
        ])->save();

        activity_log('reset_password_dosen', 'Admin mereset password dosen: '.$dosen->nama);

        Alert::toast('Password dosen berhasil direset.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()
            ->route('admin.dosen.index')
            ->with('reset_password_result', [
                'nama' => $dosen->nama,
                'password' => $newPassword,
                'source' => $source,
            ]);
    }

    private function isUsableResetValue(string $value): bool
    {
        return $value !== ''
            && ! in_array(strtolower($value), ['null', '0', '-'], true);
    }
}
