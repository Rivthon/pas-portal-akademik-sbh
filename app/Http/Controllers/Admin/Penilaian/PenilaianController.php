<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;

use PDF;
use App\Models\Dosen;
use App\Models\Saran;
use App\Models\Setting;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\ProgramStudi;
use App\Models\DosenMatakuliah;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;

use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class PenilaianController extends Controller
{
    public function index(Request $request)
    {
        $tahunAjaran = TahunAkademik::orderBy('created_at', 'desc')->get();
        $programStudi = ProgramStudi::all();
        $sarans = collect();

        return view('admin.penilaian.penilaian.index', compact('tahunAjaran', 'programStudi', 'sarans'));
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
            if ($nilai >= 4.5) return 'Sangat Baik';
            if ($nilai >= 3.5) return 'Baik';
            if ($nilai >= 2.5) return 'Cukup';
            if ($nilai >= 1.5) return 'Kurang';
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

        // Ambil dosen yang ter-assign ke mata kuliah sesuai filter
        $dosenQuery = Dosen::query();
        if ($kurikulum_id) {
            $dosenQuery->whereHas('dosenMatakuliah', function($q) use ($kurikulum_id, $jenis_dosen) {
                $q->where('kurikulum_id', $kurikulum_id);
                if ($jenis_dosen) {
                    $q->where('jenis_dosen', $jenis_dosen);
                }
            });
        }
        $dosen = $dosenQuery->get();

        // Tandai dosen yang sudah punya data EDOM pada kurikulum ini
        $dosenSudahEdom = [];
        if ($kurikulum_id) {
            $dosenSudahEdom = Penilaian::where('kurikulum_id', $kurikulum_id)
                ->when($jenis_dosen, fn($q) => $q->where('jenis_dosen', $jenis_dosen))
                ->distinct()
                ->pluck('dosen_id')
                ->toArray();
        }

        // Ambil mahasiswa yang sudah isi penilaian pada kurikulum & dosen yang dipilih
        $mahasiswaPenilaianIds = Penilaian::query()
            ->when($kurikulum_id, fn($q) => $q->where('kurikulum_id', $kurikulum_id))
            ->when($dosen_id, fn($q) => $q->where('dosen_id', $dosen_id))
            ->when($jenis_dosen, fn($q) => $q->where('jenis_dosen', $jenis_dosen))
            ->distinct()
            ->pluck('mahasiswa_id')
            ->toArray();

        // Ambil saran hanya dari mahasiswa yang sudah isi penilaian pada konteks filter ini
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->when($dosen_id, fn($query) => $query->where('dosen_id', $dosen_id))
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        // Menampilkan view dengan data yang sudah difilter
        return view('admin.penilaian.penilaian.index', compact(
            'penilaian', 'tahunAjaran', 'programStudi', 'kurikulum', 'dosen',
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen',
            'rataRataNilai', 'kriteria', 'sarans', 'dosenSudahEdom'
        ));
    }

    public function cetakPdf(Request $request)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');
        $kurikulum_id = $request->input('kurikulum_id');
        $dosen_id = $request->input('dosen_id');
        $jenis_dosen = $request->input('jenis_dosen');

        // Query penilaian
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
            ->when($jenis_dosen, fn($query) => $query->where('jenis_dosen', $jenis_dosen))
            ->get();

        // Rata-rata nilai per aspek
        $rataRataNilai = $penilaian->groupBy('evaluasi_id')->map(fn($group) =>
            $group->avg('nilai')
        );

        // Kriteria
        $kriteria = function ($nilai) {
            if ($nilai >= 4.5) return 'Sangat Baik';
            if ($nilai >= 3.5) return 'Baik';
            if ($nilai >= 2.5) return 'Cukup';
            if ($nilai >= 1.5) return 'Kurang';
            return 'Sangat Kurang';
        };

        // Ambil mahasiswa yang sudah isi penilaian
        $mahasiswaPenilaianIds = Penilaian::query()
            ->when($kurikulum_id, fn($q) => $q->where('kurikulum_id', $kurikulum_id))
            ->when($dosen_id, fn($q) => $q->where('dosen_id', $dosen_id))
            ->when($jenis_dosen, fn($q) => $q->where('jenis_dosen', $jenis_dosen))
            ->distinct()
            ->pluck('mahasiswa_id')
            ->toArray();

        // Saran
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->when($dosen_id, fn($query) => $query->where('dosen_id', $dosen_id))
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        // Info label untuk PDF
        $tahunAjaranNama = $ta_id ? TahunAkademik::find($ta_id)?->nama . ' - ' . TahunAkademik::find($ta_id)?->semester : null;
        $programStudiNama = $jurusan_id ? ProgramStudi::where('jurusan_id', $jurusan_id)->first()?->nama : null;
        $mataKuliahNama = $kurikulum_id ? Kurikulum::with('mataKuliah')->find($kurikulum_id)?->mataKuliah?->nama : null;
        $dosenNama = $dosen_id ? Dosen::find($dosen_id)?->nama : null;
        $jenisDosen = $jenis_dosen;

        // Settings & Logo
        $settings = Setting::first();
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/' . $settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = PDF::loadView('penilaian.pdf', compact(
            'penilaian', 'rataRataNilai', 'kriteria', 'sarans',
            'tahunAjaranNama', 'programStudiNama', 'mataKuliahNama',
            'dosenNama', 'jenisDosen', 'settings', 'logoBase64'
        ))->setPaper('a4', 'portrait');

        $filename = 'EDOM-' . ($dosenNama ?? 'Laporan') . '-' . now()->format('YmdHis') . '.pdf';
        return $pdf->stream($filename);
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
