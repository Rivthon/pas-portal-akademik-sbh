<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use Illuminate\View\View;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;
class DosenController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:dosen-list|dosen-create|dosen-edit|dosen-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:dosen-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:dosen-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:dosen-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        // Ambil input pencarian (jika ada)
        $search = $request->input('search');

        // Query data dosen dengan filter pencarian
        $query = Dosen::with('programStudi');

        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('kd_dosen', 'like', '%' . $search . '%')
                ->orWhereHas('programStudi', function ($q) use ($search) {
                    $q->where('nama', 'like', '%' . $search . '%');
                });
        }

        // Pagination hasil query
        $dosen = $query->paginate(10)->appends($request->query());

        // Jika request adalah AJAX, kirim response JSON
        if ($request->ajax()) {
            return response()->json([
                'html' => view('dosen.partials_list', compact('dosen'))->render()
            ]);
        }

        // Hitung nomor indeks untuk paginasi
        $pageIndex = ($dosen->currentPage() - 1) * $dosen->perPage();

        // Kirim data ke view biasa
        return view('dosen.index', compact('dosen', 'pageIndex', 'search'));
    }


    public function create(): View
    {
        $programStudi = ProgramStudi::all();
        return view('dosen.form', compact('programStudi'));
    }

    public function edit(Dosen $dosen): View
    {
        $programStudi = ProgramStudi::all();
        return view('dosen.form', compact('dosen', 'programStudi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|string',
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'email' => 'required|email|unique:dosen,email',
            'password' => 'required|string|min:8', // Validasi tanpa konfirmasi
        ]);

        $dosen = new Dosen();
        $dosen->fill($request->except('password')); // Kecualikan password dari mass assignment
        $dosen->password = bcrypt($request->password); // Hash password

        if ($request->hasFile('avatar')) {
            $filePath = $request->file('avatar')->store('avatars', 'public');
            $dosen->avatar = $filePath;
        }

        $dosen->save();
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
            'email' => 'required|email|unique:dosen,email,' . $id . ',dosen_id',
            'password' => 'nullable|string|min:8', // Password opsional tanpa konfirmasi
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
        Alert::toast('Dosen berhasil diperbaharui.', 'success')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.dosen.index');
    }

    public function destroy(Dosen $dosen): RedirectResponse
    {
        $dosen->delete();

        Alert::toast('Dosen berhasil dihapus.', 'info')
        ->position('bottom-end')
        ->autoClose(3000);
        return redirect()->route('admin.dosen.index');
    }
}
