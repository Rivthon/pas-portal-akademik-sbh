<?php

namespace App\Http\Controllers;


use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;


class ProgramStudiController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:program-studi-list|program-studi-create|program-studi-edit|program-studi-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:program-studi-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:program-studi-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:program-studi-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $programStudis = ProgramStudi::latest()->paginate(5);
        return view('program-studi.index', compact('programStudis'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    public function create(): View
    {
        return view('program-studi.create');
    }


   public function store(Request $request): RedirectResponse
{
    // Validasi input
    $request->validate([
        'jurusan_id' => 'required|integer|exists:program_studi,jurusan_id',
        'nama' => 'required|string|max:255',
        'kaprod' => 'required|string|max:255',
        'jenjang' => 'required|string|in:D3,S1,S2,S3', // Validasi pilihan jenjang
        'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi untuk file TTD
        'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Header BAAK
        'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Kaprodi BAAK
        'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Dospem BAAK
    ]);

    // Simpan file gambar jika ada
    $data = $request->all();
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

    // Simpan data ke database
    ProgramStudi::create([
        'jurusan_id' => $data['jurusan_id'],
        'nama' => $data['nama'],
        'kaprod' => $data['kaprod'],
        'jenjang' => $data['jenjang'],
        'ttd' => $data['ttd'] ?? null,
        'header_baak' => $data['header_baak'] ?? null,
        'header_kapro' => $data['header_kapro'] ?? null,
        'header_dospem' => $data['header_dospem'] ?? null,
    ]);

    // Redirect dengan pesan sukses
    Alert::toast('Program studi berhasil dibuat.', 'success')
        ->position('bottom-end') // Posisi toast
        ->autoClose(3000);       // Durasi dalam milidetik

    return redirect()->route('admin.program-studi.index');
}



    public function show(progrProgramStudi $programStudi): View
    {
        return view('program-studi.show', compact('programStudi'));
    }


    public function edit(ProgramStudi $programStudi): View
    {
        return view('program-studi.edit', compact('programStudi'));
    }


    public function update(Request $request, ProgramStudi $programStudi): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
            'ttd' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_baak' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_kapro' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'header_dospem' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Data untuk di-update
        $data = $request->all();

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

        // Update data
        $programStudi->update($data);

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Program studi berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.program-studi.index');
        }
        public function destroy(ProgramStudi $programStudi): RedirectResponse
        {
            $programStudi->delete();

            Alert::toast('Program studi berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

            return redirect()->route('admin.program-studi.index');

        }
}