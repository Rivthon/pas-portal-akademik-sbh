<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class RuanganController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:ruangan-list|ruangan-create|ruangan-edit|ruangan-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:ruangan-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:ruangan-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:ruangan-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $ruangans = Ruangan::latest()->paginate(5);
        return view('ruangan.index', compact('ruangans'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    public function create(): View
    {
        return view('ruangan.create');
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

        // Redirect dengan pesan sukses
        Alert::toast('ruangan berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.ruangan.index');
    }



    public function edit(Ruangan $ruangan): View
    {
        return view('ruangan.edit', compact('ruangan'));
    }


    public function update(Request $request, Ruangan $ruangan): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
        ]);

        // Update data
        $ruangan->update($request->all());

        // Tampilkan notifikasi SweetAlert
        Alert::toast('ruangan berhasil diperbarui.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        // Redirect ke halaman indeks
        return redirect()->route('admin.ruangan.index');
    }

    public function destroy(Ruangan $ruangan): RedirectResponse
    {
        $ruangan->delete();

        Alert::toast('ruangan berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.ruangan.index');
    }
}
