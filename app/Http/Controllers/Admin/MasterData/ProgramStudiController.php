<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class ProgramStudiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:program-studi-list|program-studi-create|program-studi-edit|program-studi-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:program-studi-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:program-studi-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:program-studi-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $programStudis = ProgramStudi::with('kaprodi')->latest()->paginate(5);

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
        // Validasi input
        $request->validate([
            'jurusan_id' => 'required|string|max:10|unique:program_studi,jurusan_id',
            'nama' => 'required|string|max:255',
            'kaprodi_dosen_id' => 'required|integer|exists:dosen,dosen_id',
            'jenjang' => 'required|string|in:D3,S1,S2,S3', // Validasi pilihan jenjang
            'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk file TTD
            'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Header BAAK
            'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Kaprodi BAAK
            'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Dospem BAAK
        ]);

        // Simpan file gambar jika ada
        $data = $request->all();
        $kaprodi = Dosen::findOrFail($data['kaprodi_dosen_id']);
        $data['kaprod'] = $kaprodi->nama;
        if ($request->hasFile('ttd')) {
            $data['ttd'] = $request->file('ttd')->store('program_studi', 'public');
        }
        if ($request->hasFile('header_baak')) {
            $data['header_baak'] = $request->file('header_baak')->store('program_studi', 'public');
        }
        if ($request->hasFile('header_kapro')) {
            $data['header_kapro'] = $request->file('header_kapro')->store('program_studi', 'public');
        }
        if ($request->hasFile('header_dospem')) {
            $data['header_dospem'] = $request->file('header_dospem')->store('program_studi', 'public');
        }
        if ($request->hasFile('header_mhs')) {
            $data['header_mhs'] = $request->file('header_mhs')->store('program_studi', 'public');
        }

        // Simpan data ke database
        ProgramStudi::create([
            'jurusan_id' => $data['jurusan_id'],
            'nama' => $data['nama'],
            'kaprod' => $data['kaprod'],
            'kaprodi_dosen_id' => $data['kaprodi_dosen_id'],
            'jenjang' => $data['jenjang'],
            'ttd' => $data['ttd'] ?? null,
            'header_baak' => $data['header_baak'] ?? null,
            'header_kapro' => $data['header_kapro'] ?? null,
            'header_dospem' => $data['header_dospem'] ?? null,
            'header_mhs' => $data['header_mhs'] ?? null,
        ]);

        // Redirect dengan pesan sukses
        activity_log('tambah_prodi', 'Admin menambah program studi: '.$data['nama']);
        Alert::toast('Program studi berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);       // Durasi dalam milidetik

        return redirect()->route('admin.program-studi.index');
    }

    public function show(progrProgramStudi $programStudi): View
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
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            'jurusan_id' => 'required|in:'.$programStudi->jurusan_id,
            'jenjang' => 'required|string|in:D3,S1,S2,S3',
            'kaprodi_dosen_id' => 'nullable|integer|exists:dosen,dosen_id',
            'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_mhs' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Data untuk di-update
        $data = $request->all();

        if ($request->filled('kaprodi_dosen_id')) {
            $kaprodi = Dosen::findOrFail($request->kaprodi_dosen_id);
            $data['kaprod'] = $kaprodi->nama;
            $data['kaprodi_dosen_id'] = $kaprodi->dosen_id;
        } else {
            // Data Kaprodi lama tetap dipertahankan sampai admin memilih akun dosen.
            unset($data['kaprod'], $data['kaprodi_dosen_id']);
        }

        // Proses upload gambar jika ada
        if ($request->hasFile('ttd')) {
            $data['ttd'] = $request->file('ttd')->store('program_studi', 'public');
        }

        if ($request->hasFile('header_baak')) {
            $data['header_baak'] = $request->file('header_baak')->store('program_studi', 'public');
        }

        if ($request->hasFile('header_kapro')) {
            $data['header_kapro'] = $request->file('header_kapro')->store('program_studi', 'public');
        }

        if ($request->hasFile('header_dospem')) {
            $data['header_dospem'] = $request->file('header_dospem')->store('program_studi', 'public');
        }

        if ($request->hasFile('header_mhs')) {
            $data['header_mhs'] = $request->file('header_mhs')->store('program_studi', 'public');
        }

        // Update data
        $programStudi->update($data);

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
        activity_log('hapus_prodi', 'Admin menghapus program studi: '.$programStudi->nama);
        $programStudi->delete();

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
}
