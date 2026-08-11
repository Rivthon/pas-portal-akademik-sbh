<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Evaluasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class EvaluasiController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:evaluasi-list|evaluasi-create|evaluasi-edit|evaluasi-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:evaluasi-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:evaluasi-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:evaluasi-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $evaluasis = Evaluasi::latest()->paginate(5);

        return view('admin.penilaian.evaluasi.index', compact('evaluasis'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create(): View
    {
        return view('admin.penilaian.evaluasi.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
            // 'kategori' => 'required|in:teori,praktik',
        ]);

        // Simpan data ke database
        Evaluasi::create([
            'nama' => $request->nama,
        ]);

        activity_log('tambah_evaluasi', 'Admin menambah pertanyaan evaluasi: '.$request->nama);

        // Redirect dengan pesan sukses
        Alert::toast('Evaluasi berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.evaluasi.index');
    }

    public function edit(Evaluasi $evaluasi): View
    {
        return view('admin.penilaian.evaluasi.edit', compact('evaluasi'));
    }

    public function update(Request $request, Evaluasi $evaluasi): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
            // 'kategori' => 'required|in:teori,praktik',
        ]);

        // Update data
        $evaluasi->update($request->all());

        activity_log('update_evaluasi', 'Admin memperbarui evaluasi: '.$evaluasi->nama);

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Evaluasi berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.evaluasi.index');
    }

    public function destroy(Evaluasi $evaluasi): RedirectResponse
    {
        activity_log('hapus_evaluasi', 'Admin menghapus evaluasi: '.$evaluasi->nama);
        $evaluasi->delete();

        Alert::toast('Evaluasi berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.evaluasi.index');
    }
}
