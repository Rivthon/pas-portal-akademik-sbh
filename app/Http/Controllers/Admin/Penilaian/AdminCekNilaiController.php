<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class AdminCekNilaiController extends Controller
{
    /**
     * Halaman Cek Nilai UTS/UAS Admin
     */
    public function index(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();
        $programStudiList = ProgramStudi::orderBy('nama')->get();

        if (! $tahunAjaran) {
            return redirect()->route('admin.home')->with('error', 'Tidak ada Tahun Ajaran aktif.');
        }

        return view('admin.penilaian.cek-nilai.index', compact(
            'tahunAjaran',
            'programStudiList'
        ));
    }

    /**
     * AJAX: Ambil data nilai berdasarkan filter
     */
    public function getNilaiByFilter(Request $request)
    {
        $tahunAjaran = TahunAkademik::where('status_ta', 1)->first();

        if (! $tahunAjaran) {
            return response()->json(['message' => 'Tidak ada Tahun Ajaran aktif.'], 400);
        }

        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');
        $search = $request->input('search');

        // Query KRS untuk mendapatkan baris mata kuliah per mahasiswa
        $query = Krs::with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->where('ta_id', $tahunAjaran->ta_id)
            ->whereHas('mahasiswa', function ($q) {
                $q->where('status_mhs', 'aktif');
            });

        if ($jurusanId) {
            $query->whereHas('mahasiswa', function ($q) use ($jurusanId) {
                $q->where('jurusan_id', $jurusanId);
            });
        }

        if ($semester) {
            $query->whereHas('kurikulum.mataKuliah', function ($q) use ($semester) {
                $q->where('smt', $semester);
            });
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                // Cari berdasarkan nama/NIM mahasiswa
                $q->whereHas('mahasiswa', function ($mq) use ($search) {
                    $mq->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nim', 'like', '%'.$search.'%');
                })
                // ATAU cari berdasarkan nama/kode mata kuliah
                    ->orWhereHas('kurikulum.mataKuliah', function ($kq) use ($search) {
                        $kq->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('matakuliah_id', 'like', '%'.$search.'%');
                    });
            });
        }

        $krsData = $query->paginate(20)->appends($request->query());

        return response()->json([
            'html' => view('admin.penilaian.cek-nilai.partials_list', compact('krsData'))->render(),
        ]);
    }
}
