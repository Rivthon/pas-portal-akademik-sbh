<?php

namespace App\Http\Controllers\Admin\Keuangan;

use App\Http\Controllers\Controller;
use App\Models\Gelombang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class GelombangController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:gelombang-list|gelombang-create|gelombang-edit|gelombang-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:gelombang-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:gelombang-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:gelombang-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $gelombangs = Gelombang::orderBy('id', 'asc')->paginate(5);

        return view('admin.keuangan.gelombang.index', compact('gelombangs'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }

    public function create(): View
    {
        return view('admin.keuangan.gelombang.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        // Simpan data ke database
        Gelombang::create([
            'nama' => $request->nama,
        ]);

        activity_log('tambah_gelombang', 'Admin menambah gelombang: '.$request->nama);

        // Redirect dengan pesan sukses
        Alert::toast('Gelombang berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.gelombang.index');
    }

    public function edit(Gelombang $gelombang): View
    {
        return view('admin.keuangan.gelombang.edit', compact('gelombang'));
    }

    public function update(Request $request, Gelombang $gelombang): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
        ]);

        // Update data
        $gelombang->update($request->all());

        activity_log('update_gelombang', 'Admin memperbarui gelombang: '.$gelombang->nama);

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Gelombang berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.gelombang.index');
    }

    public function destroy(Gelombang $gelombang): RedirectResponse
    {
        activity_log('hapus_gelombang', 'Admin menghapus gelombang: '.$gelombang->nama);
        $gelombang->delete();

        Alert::toast('Gelombang berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.gelombang.index');
    }
}
