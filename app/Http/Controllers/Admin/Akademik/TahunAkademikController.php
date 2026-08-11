<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\TahunAkademik;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

class TahunAkademikController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:tahun-ajaran-list|tahun-ajaran-create|tahun-ajaran-edit|tahun-ajaran-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:tahun-ajaran-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:tahun-ajaran-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:tahun-ajaran-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $tahunAjarans = TahunAkademik::when($search, function ($query, $search) {
            return $query->where('nama', 'like', "%{$search}%");
        })->orderBy('ta_id', 'desc')->paginate(10);

        if ($request->ajax()) {
            return view('admin.akademik.tahun-ajaran.table', compact('tahunAjarans'))->render(); // ini file blade AJAX
        }

        return view('admin.akademik.tahun-ajaran.index', compact('tahunAjarans'));
    }

    public function create(): View
    {
        return view('admin.akademik.tahun-ajaran.create');
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

        activity_log('tambah_tahun_ajaran', 'Admin menambah tahun ajaran: '.$request->nama.' ('.$request->semester.')');

        // Redirect dengan pesan sukses
        Alert::toast('Tahun Ajaran berhasil dibuat.', 'success')
            ->position('bottom-end') // Posisi toast
            ->autoClose(3000);    // Durasi dalam milidetik

        return redirect()->route('admin.tahun-ajaran.index');
    }

    public function edit(TahunAkademik $tahunAjaran): View
    {
        return view('admin.akademik.tahun-ajaran.edit', compact('tahunAjaran'));
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

        activity_log('update_tahun_ajaran', 'Admin memperbarui tahun ajaran: '.$tahunAjaran->nama);

        // Tampilkan notifikasi SweetAlert
        Alert::toast('Tahun Ajaran berhasil diperbarui.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        // Redirect ke halaman indeks
        return redirect()->route('admin.tahun-ajaran.index');
    }

    public function destroy(TahunAkademik $tahunAjaran): RedirectResponse
    {
        activity_log('hapus_tahun_ajaran', 'Admin menghapus tahun ajaran: '.$tahunAjaran->nama);
        $tahunAjaran->delete();

        Alert::toast('Tahun Ajaran berhasil dihapus.', 'info')
            ->position('bottom-end')
            ->autoClose(3000);

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

        activity_log('ubah_tahun_ajaran_aktif', 'Admin mengaktifkan tahun ajaran: '.$tahunAjaran->nama);

        Alert::toast('Tahun Ajaran berhasil diubah.', 'success')
            ->position('bottom-end')
            ->autoClose(3000);

        return redirect()->route('admin.tahun-ajaran.index');
    }
}
