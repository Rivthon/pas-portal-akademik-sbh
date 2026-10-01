<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramStudiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:program-studi-list|program-studi-create|program-studi-edit|program-studi-delete', ['only' => ['index', 'show', 'asset']]);
        $this->middleware('permission:program-studi-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:program-studi-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:program-studi-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $programStudis = ProgramStudi::with(['kaprodi', 'sekprodi'])->latest()->paginate(5);

        return view('admin.master-data.program-studi.index', compact('programStudis'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create(): View
    {
        $programStudi = new ProgramStudi;
        $dosen = $this->dosenOptions();

        return view('admin.master-data.program-studi.create', compact('programStudi', 'dosen'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'jurusan_id' => 'required|string|max:10|unique:program_studi,jurusan_id',
            'nama' => 'required|string|max:255',
            'kaprodi_dosen_id' => 'required|integer|exists:dosen,dosen_id',
            'sekprodi_dosen_id' => 'nullable|integer|different:kaprodi_dosen_id|exists:dosen,dosen_id',
            'jenjang' => 'required|string|in:D3,S1,S2,S3', // Validasi pilihan jenjang
            'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk file TTD
            'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Header BAAK
            'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Kaprodi BAAK
            'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Dospem BAAK
            'header_mhs' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $kaprodi = Dosen::findOrFail($data['kaprodi_dosen_id']);
        $data['kaprod'] = $kaprodi->nama;
        $newUploads = $this->storeAssets($request);

        try {
            ProgramStudi::create([
                'jurusan_id' => $data['jurusan_id'],
                'nama' => $data['nama'],
                'kaprod' => $data['kaprod'],
                'kaprodi_dosen_id' => $data['kaprodi_dosen_id'],
                'sekprodi_dosen_id' => $data['sekprodi_dosen_id'] ?? null,
                'jenjang' => $data['jenjang'],
                'ttd' => $newUploads['ttd'] ?? null,
                'header_baak' => $newUploads['header_baak'] ?? null,
                'header_kapro' => $newUploads['header_kapro'] ?? null,
                'header_dospem' => $newUploads['header_dospem'] ?? null,
                'header_mhs' => $newUploads['header_mhs'] ?? null,
            ]);
        } catch (\Throwable $exception) {
            $this->deleteAssets($newUploads);
            throw $exception;
        }

        // Redirect dengan pesan sukses
        activity_log('tambah_prodi', 'Admin menambah program studi: '.$data['nama']);
        Alert::toast('Program studi berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);       // Durasi dalam milidetik

        return redirect()->route('admin.program-studi.index');
    }

    public function show(ProgramStudi $programStudi): View
    {
        return view('admin.master-data.program-studi.show', compact('programStudi'));
    }

    public function edit(ProgramStudi $programStudi): View
    {
        $dosen = $this->dosenOptions();

        return view('admin.master-data.program-studi.edit', compact('programStudi', 'dosen'));
    }

    public function update(Request $request, ProgramStudi $programStudi): RedirectResponse
    {
        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'jurusan_id' => 'required|in:'.$programStudi->jurusan_id,
            'jenjang' => 'required|string|in:D3,S1,S2,S3',
            'kaprodi_dosen_id' => 'nullable|integer|exists:dosen,dosen_id',
            'sekprodi_dosen_id' => 'nullable|integer|different:kaprodi_dosen_id|exists:dosen,dosen_id',
            'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_mhs' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->filled('kaprodi_dosen_id')) {
            $kaprodi = Dosen::findOrFail($request->kaprodi_dosen_id);
            $data['kaprod'] = $kaprodi->nama;
            $data['kaprodi_dosen_id'] = $kaprodi->dosen_id;
        } else {
            // Data Kaprodi lama tetap dipertahankan sampai admin memilih akun dosen.
            unset($data['kaprod'], $data['kaprodi_dosen_id']);
        }

        $newUploads = $this->storeAssets($request);
        $oldUploads = [];
        foreach ($newUploads as $field => $path) {
            $oldUploads[$field] = $programStudi->{$field};
            $data[$field] = $path;
        }

        try {
            $programStudi->update($data);
        } catch (\Throwable $exception) {
            $this->deleteAssets($newUploads);
            throw $exception;
        }
        $this->deleteAssets($oldUploads);

        // Tampilkan notifikasi SweetAlert
        activity_log('update_prodi', 'Admin memperbarui program studi: '.$programStudi->nama);
        Alert::toast('Program studi berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.program-studi.index');
    }

    public function destroy(ProgramStudi $programStudi): RedirectResponse
    {
        $oldUploads = collect($this->assetFields())
            ->mapWithKeys(fn (string $field) => [$field => $programStudi->{$field}])
            ->all();
        activity_log('hapus_prodi', 'Admin menghapus program studi: '.$programStudi->nama);
        $programStudi->delete();
        $this->deleteAssets($oldUploads);

        Alert::toast('Program studi berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.program-studi.index');

    }

    private function dosenOptions()
    {
        return Dosen::with('programStudi')
            ->orderBy('nama')
            ->get();
    }

    public function asset(ProgramStudi $programStudi, string $field): StreamedResponse
    {
        abort_unless(in_array($field, $this->assetFields(), true), 404);

        $path = $programStudi->{$field};
        abort_unless(StoredUpload::exists($path), 404);

        return StoredUpload::disk($path)->response($path);
    }

    private function assetFields(): array
    {
        return ['ttd', 'header_baak', 'header_kapro', 'header_dospem', 'header_mhs'];
    }

    private function storeAssets(Request $request): array
    {
        $paths = [];
        try {
            foreach ($this->assetFields() as $field) {
                if ($request->hasFile($field)) {
                    $paths[$field] = $request->file($field)->store('program_studi', 'private');
                }
            }
        } catch (\Throwable $exception) {
            $this->deleteAssets($paths);
            throw $exception;
        }

        return $paths;
    }

    private function deleteAssets(array $paths): void
    {
        foreach ($paths as $path) {
            StoredUpload::delete($path);
        }
    }
}
