<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class RuanganController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:ruangan-list|ruangan-create|ruangan-edit|ruangan-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:ruangan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ruangan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ruangan-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $ruangans = Ruangan::latest()->paginate(5);

        return view('admin.akademik.ruangan.index', compact('ruangans'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create(): View
    {
        return view('admin.akademik.ruangan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        // Simpan data ke database
        Ruangan::create([
            'nama' => $request->nama,
        ]);

        activity_log('tambah_ruangan', 'Admin menambah ruangan: '.$request->nama);

        // Redirect dengan pesan sukses
        Alert::toast('ruangan berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.ruangan.index');
    }

    public function edit(Ruangan $ruangan): View
    {
        return view('admin.akademik.ruangan.edit', compact('ruangan'));
    }

    public function update(Request $request, Ruangan $ruangan): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
        ]);

        // Update data
        $ruangan->update($request->all());

        activity_log('update_ruangan', 'Admin memperbarui ruangan: '.$ruangan->nama);

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Ruangan berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.ruangan.index');
    }

    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        activity_log('hapus_ruangan', 'Admin menghapus ruangan: '.$ruangan->nama);
        $ruangan->delete();

        Alert::toast('Ruangan berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.ruangan.index');
    }
}
