<?php

namespace App\Http\Controllers;

use App\Models\Dosen;
use App\Models\Saran;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PenilaianController extends Controller
{
         public function index(Request $request)
        {
            // Ambil daftar tahun ajaran
            $ta_id = $request->input('ta_id');
            $jurusan_id = $request->input('jurusan_id');  // Menggunakan jurusan_id (Program Studi)
            $kurikulum_id = $request->input('kurikulum_id');
            $dosen_id = $request->input('dosen_id');

            $sarans = Saran::with(['mahasiswa', 'dosen'])
                ->when($dosen_id, fn($query) => $query->where('dosen_id', $dosen_id))
                ->get();
            $tahunAjaran = TahunAkademik::orderBy('created_at', 'desc')->get();

            return view('penilaian.index', compact('tahunAjaran','sarans'));
        }


  public function filter(Request $request)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');  // Menggunakan jurusan_id (Program Studi)
        $kurikulum_id = $request->input('kurikulum_id');
        $dosen_id = $request->input('dosen_id');
        $jenis_dosen = $request->input('jenis_dosen'); // Tambahkan filter jenis_dosen

        // Query filter hasil evaluasi
        $penilaian = Penilaian::with('evaluasi')
            ->when($ta_id, fn($query) =>
                $query->whereHas('kurikulum', fn($subQuery) =>
                    $subQuery->where('ta_id', $ta_id)
                )
            )
            ->when($jurusan_id, fn($query) =>
                $query->whereHas('kurikulum.programStudi', fn($subQuery) =>
                    $subQuery->where('jurusan_id', $jurusan_id)
                )
            )
            ->when($kurikulum_id, fn($query) => $query->where('kurikulum_id', $kurikulum_id))
            ->when($dosen_id, fn($query) => $query->where('dosen_id', $dosen_id))
            ->when($jenis_dosen, fn($query) => $query->where('jenis_dosen', $jenis_dosen)) // Tambahkan filter jenis_dosen
            ->get();

        // Menghitung rata-rata nilai per aspek penilaian (evaluasi_id)
        $rataRataNilai = $penilaian->groupBy('evaluasi_id')->map(fn($group) =>
            $group->avg('nilai')
        );

        // Fungsi untuk menentukan kriteria
        $kriteria = function ($nilai) {
            if ($nilai == 5) return 'Sangat Baik';
            if ($nilai == 4) return 'Baik';
            if ($nilai == 3) return 'Cukup';
            if ($nilai == 2) return 'Kurang';
            return 'Sangat Kurang';
        };

        // Data untuk combobox (Dropdown)
        $tahunAjaran = TahunAkademik::orderBy('created_at', 'desc')->get();
        $programStudi = ProgramStudi::all();
        $kurikulum = Kurikulum::when($ta_id, function ($query) use ($ta_id) {
            return $query->where('ta_id', $ta_id);
        })
        ->when($jurusan_id, function ($query) use ($jurusan_id) {
            return $query->whereHas('programStudi', function ($subQuery) use ($jurusan_id) {
                $subQuery->where('jurusan_id', $jurusan_id);
            });
        })
        ->get();

        $dosen = Dosen::all();

        // 🔹 Ambil data saran terkait mahasiswa dan dosen
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->when($dosen_id, fn($query) => $query->where('dosen_id', $dosen_id))
            ->when($kurikulum_id, fn($query) => $query->where('kurikulum_id', $kurikulum_id))
             ->when($jenis_dosen, fn($query) => $query->where('jenis_dosen', $jenis_dosen)) // Tambahkan filter jenis_dosen
            ->get();

        // Menampilkan view dengan data yang sudah difilter
        return view('penilaian.index', compact(
            'penilaian', 'tahunAjaran', 'programStudi', 'kurikulum', 'dosen',
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen', 'rataRataNilai', 'kriteria', 'sarans'
        ));
    }
    public function resetEdom()
    {
        DB::transaction(function () {
            Mahasiswa::query()->update(['status_edom' => 0]);
        });

        Alert::success('Success', 'EDOM telah di-reset.');
        return redirect()->back();
    }

    public function setupEdom()
    {
        DB::transaction(function () {
            Mahasiswa::query()->update(['status_edom' => 1]);
        });

        Alert::success('Success', 'EDOM telah dipulihkan kembali.');
        return redirect()->back();
    }

}
