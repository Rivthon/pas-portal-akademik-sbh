<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;


class GelombangController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:gelombang-list|gelombang-create|gelombang-edit|gelombang-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:gelombang-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:gelombang-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:gelombang-delete', ['only' => ['destroy']]);
    }

    public function index(): View
    {
        $gelombangs = Gelombang::orderBy('id', 'asc')->paginate(5);
        return view('gelombang.index', compact('gelombangs'))
            ->with('i', (request()->input('page', 1) - 1) * 5);
    }


    public function create(): View
    {
        return view('gelombang.create');
    }


    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        // Simpan data ke database
        gelombang::create([
            'nama' => $request->nama,
        ]);

        // Redirect dengan pesan sukses
        Alert::toast('gelombang berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.gelombang.index');
    }



    public function edit(gelombang $gelombang): View
    {
        return view('gelombang.edit', compact('gelombang'));
    }


    public function update(Request $request, Gelombang $gelombang): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
        ]);

        // Update data
        $gelombang->update($request->all());

        // Tampilkan notifikasi SweetAlert
        Alert::toast('gelombang berhasil diperbarui.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        // Redirect ke halaman indeks
        return redirect()->route('admin.gelombang.index');
    }

    public function destroy(Gelombang $gelombang): RedirectResponse
    {
        $gelombang->delete();

        Alert::toast('gelombang berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.gelombang.index');
    }
}