<?php

namespace App\Http\Controllers;

use App\Models\TahunAkademik;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class TahunAkademikController extends Controller
{

    function __construct()
    {
        $this->middleware('permission:tahun-ajaran-list|tahun-ajaran-create|tahun-ajaran-edit|tahun-ajaran-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tahun-ajaran-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tahun-ajaran-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tahun-ajaran-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request): View|JsonResponse
{
    // Ambil input pencarian dari request atau session
    $search = $request->input('search', session('search_tahun_ajaran', ''));

    // Simpan nilai pencarian ke session jika ada input baru
    if ($request->has('search')) {
        session(['search_tahun_ajaran' => $search]);
    }

    // Ambil semua data Tahun Akademik (tanpa query builder)
    $tahunAjarans = TahunAkademik::when(!empty($search), function ($query) use ($search) {
        return $query->where('nama', 'like', "%$search%")
                     ->orWhere('semester', 'like', "%$search%");
    })->paginate(10)->appends(['search' => $search]);

    // Hitung nomor indeks untuk paginasi
    $pageIndex = ($tahunAjarans->currentPage() - 1) * $tahunAjarans->perPage();

    // Jika request adalah AJAX, kirim hanya partial view
    if ($request->ajax()) {
        return response()->json([
            'html' => view('tahun-ajaran.partials_list', compact('tahunAjarans', 'pageIndex'))->render(),
        ]);
    }

    // Jika bukan AJAX, kirim full view
    return view('tahun-ajaran.index', compact('tahunAjarans', 'pageIndex', 'search'));
}





    public function create(): View
    {
        return view('tahun-ajaran.create');
    }


    public function store(Request $request): RedirectResponse
    {
         // Validasi input
        $request->validate([
            // 'jurusan_id' => 'required|integer|exists:program_studi,jurusan_id',
            'nama' => 'required|string|max:255',
            'semester' => 'required|string|max:255',

        ]);

        // Simpan data ke database
        TahunAkademik::create([

            'nama' => $request->nama,
            'semester' => $request->semester,

        ]);

        // Redirect dengan pesan sukses
        Alert::toast('Tahun Ajaran berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.tahun-ajaran.index');
    }



    public function edit(TahunAkademik $tahunAjaran): View
    {
        return view('tahun-ajaran.edit', compact('tahunAjaran'));
    }


    public function update(Request $request, TahunAkademik $tahunAjaran): RedirectResponse
    {
        // Validasi input
        $request->validate([
            'nama' => 'required',
            'semester' => 'required',

        ]);

        // Update data
        $tahunAjaran->update($request->all());

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Tahun Ajaran berhasil diperbarui.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        // Redirect ke halaman indeks
        return redirect()->route('admin.tahun-ajaran.index');
    }

    public function destroy(TahunAkademik $tahunAjaran): RedirectResponse
    {
        $tahunAjaran->delete();

        Alert::toast('Tahun Ajaran berhasil dihapus.', 'info')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.tahun-ajaran.index');
    }
    public function updateStatus($id)
    {
        // Nonaktifkan semua status
        TahunAkademik::where('status_ta', 1)->update(['status_ta' => 0]);

        // Aktifkan status tahun ajaran yang dipilih
        $tahunAjaran = TahunAkademik::findOrFail($id);
        $tahunAjaran->status_ta = 1;
        $tahunAjaran->save();

        Alert::toast('Tahun Ajaran berhasil diubah.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik
        return redirect()->route('admin.tahun-ajaran.index');
    }
}
