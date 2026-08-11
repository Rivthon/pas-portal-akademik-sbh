<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\Penilaian;
use App\Models\ProgramStudi;
use App\Models\Saran;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use RealRashid\SweetAlert\Facades\Alert;

class PenilaianController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:evaluasi-list', ['only' => ['index', 'detail', 'getKurikulumAjax', 'getDosenAjax', 'cetakRegistry', 'cetakPdf']]);
        $this->middleware('permission:penilaian-reset-edom', ['only' => ['resetEdom', 'setupEdom']]);
    }

    public function index(Request $request)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');
        $kurikulum_id = $request->input('kurikulum_id');
        $dosen_id = $request->input('dosen_id');
        $jenis_dosen = $request->input('jenis_dosen');

        $tahunAjaran = TahunAkademik::orderBy('created_at', 'desc')->get();
        $programStudi = ProgramStudi::all();
        $sarans = collect();

        $assignments = collect();
        if ($ta_id || $jurusan_id || $kurikulum_id || $dosen_id || $jenis_dosen) {
            $assignmentsQuery = DosenMatakuliah::with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->when($ta_id, function ($q) use ($ta_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->where('ta_id', $ta_id));
                })
                ->when($jurusan_id, function ($q) use ($jurusan_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->whereHas('programStudi', fn ($s2) => $s2->where('jurusan_id', $jurusan_id)));
                })
                ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
                ->when($dosen_id, fn ($query) => $query->where('dosen_id', $dosen_id))
                ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen));

            $assignments = $assignmentsQuery->get();

            // Tandai status EDOM
            $evaluatedPairs = Penilaian::select('dosen_id', 'kurikulum_id', 'jenis_dosen')
                ->distinct()
                ->get()
                ->map(fn ($p) => $p->dosen_id.'-'.$p->kurikulum_id.'-'.$p->jenis_dosen)
                ->toArray();

            $assignments->map(function ($assign) use ($evaluatedPairs) {
                // Ensure jenis_dosen matches the evaluated ones
                $key = $assign->dosen_id.'-'.$assign->kurikulum_id.'-'.$assign->jenis_dosen;
                $assign->status_edom = in_array($key, $evaluatedPairs);

                return $assign;
            });
        }

        // Data array for dropdown if needed when reloading with query params
        $kurikulumList = [];
        $dosenList = [];
        if ($ta_id && $jurusan_id) {
            $kurikulumList = Kurikulum::with('mataKuliah')
                ->where('ta_id', $ta_id)
                ->whereHas('programStudi', fn ($q) => $q->where('jurusan_id', $jurusan_id))
                ->get();
        }
        if ($kurikulum_id) {
            $dosenQuery = Dosen::whereHas('dosenMatakuliah', function ($q) use ($kurikulum_id, $jenis_dosen) {
                $q->where('kurikulum_id', $kurikulum_id);
                if ($jenis_dosen) {
                    $q->where('jenis_dosen', $jenis_dosen);
                }
            });
            $dosenList = $dosenQuery->get();
        }

        return view('admin.penilaian.penilaian.index', compact(
            'tahunAjaran', 'programStudi', 'kurikulumList', 'dosenList',
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen',
            'assignments', 'sarans'
        ));
    }

    public function detail(Request $request, $dosen_id, $kurikulum_id, $jenis_dosen)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');

        $penilaian = Penilaian::with('evaluasi')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->where('jenis_dosen', $jenis_dosen)
            ->get();

        $rataRataNilai = $penilaian->groupBy('evaluasi_id')->map(fn ($group) => $group->avg('nilai'));

        $kriteria = function ($nilai) {
            if ($nilai >= 4.5) {
                return 'Sangat Baik';
            }
            if ($nilai >= 3.5) {
                return 'Baik';
            }
            if ($nilai >= 2.5) {
                return 'Cukup';
            }
            if ($nilai >= 1.5) {
                return 'Kurang';
            }

            return 'Sangat Kurang';
        };

        $mahasiswaPenilaianIds = $penilaian->pluck('mahasiswa_id')->unique()->toArray();
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->where('dosen_id', $dosen_id)
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        $dosen = Dosen::find($dosen_id);
        $kurikulum = Kurikulum::with(['mataKuliah', 'programStudi'])->find($kurikulum_id);

        return view('admin.penilaian.penilaian.detail', compact(
            'penilaian', 'rataRataNilai', 'kriteria', 'sarans',
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen',
            'dosen', 'kurikulum'
        ));
    }

    public function getKurikulumAjax(Request $request)
    {
        $ta_id = $request->ta_id;
        $jurusan_id = $request->jurusan_id;
        $kurikulum = Kurikulum::with('mataKuliah')
            ->when($ta_id, fn ($query) => $query->where('ta_id', $ta_id))
            ->when($jurusan_id, fn ($query) => $query->whereHas('programStudi', fn ($q) => $q->where('jurusan_id', $jurusan_id)))
            ->get();

        $formatted = $kurikulum->map(function ($k) {
            return [
                'id' => $k->kurikulum_id,
                'nama' => ($k->mataKuliah->nama ?? 'Unknown').' - SMT '.$k->semester,
            ];
        });

        return response()->json($formatted);
    }

    public function getDosenAjax(Request $request)
    {
        $kurikulum_id = $request->kurikulum_id;
        $jenis_dosen = $request->jenis_dosen;
        $dosen = Dosen::whereHas('dosenMatakuliah', function ($q) use ($kurikulum_id, $jenis_dosen) {
            if ($kurikulum_id) {
                $q->where('kurikulum_id', $kurikulum_id);
            }
            if ($jenis_dosen) {
                $q->where('jenis_dosen', $jenis_dosen);
            }
        })->get();

        return response()->json($dosen);
    }

    public function cetakRegistry(Request $request)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');
        $kurikulum_id = $request->input('kurikulum_id');
        $jenis_dosen = $request->input('jenis_dosen');

        $assignments = collect();
        if ($ta_id || $jurusan_id || $kurikulum_id || $jenis_dosen) {
            $assignmentsQuery = DosenMatakuliah::with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->when($ta_id, function ($q) use ($ta_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->where('ta_id', $ta_id));
                })
                ->when($jurusan_id, function ($q) use ($jurusan_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->whereHas('programStudi', fn ($s2) => $s2->where('jurusan_id', $jurusan_id)));
                })
                ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
                ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen));

            $assignments = $assignmentsQuery->get();

            $evaluatedPairs = Penilaian::select('dosen_id', 'kurikulum_id', 'jenis_dosen')
                ->distinct()
                ->get()
                ->map(fn ($p) => $p->dosen_id.'-'.$p->kurikulum_id.'-'.$p->jenis_dosen)
                ->toArray();

            $assignments->map(function ($assign) use ($evaluatedPairs) {
                $key = $assign->dosen_id.'-'.$assign->kurikulum_id.'-'.$assign->jenis_dosen;
                $assign->status_edom = in_array($key, $evaluatedPairs);

                return $assign;
            });
        }

        $tahunAjaranNama = $ta_id ? TahunAkademik::find($ta_id)?->nama.' - '.TahunAkademik::find($ta_id)?->semester : 'Semua Tahun Ajaran';
        $programStudiNama = $jurusan_id ? ProgramStudi::where('jurusan_id', $jurusan_id)->first()?->nama : 'Semua Program Studi';

        $settings = Setting::first();
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = PDF::loadView('admin.penilaian.penilaian.registry-pdf', compact(
            'assignments', 'tahunAjaranNama', 'programStudiNama', 'settings', 'logoBase64'
        ))->setPaper('a4', 'landscape');

        return $pdf->stream('Evaluation-Registry-'.now()->format('YmdHis').'.pdf');
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
            ->when($ta_id, fn ($query) => $query->whereHas('kurikulum', fn ($subQuery) => $subQuery->where('ta_id', $ta_id)
            )
            )
            ->when($jurusan_id, fn ($query) => $query->whereHas('kurikulum.programStudi', fn ($subQuery) => $subQuery->where('jurusan_id', $jurusan_id)
            )
            )
            ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
            ->when($dosen_id, fn ($query) => $query->where('dosen_id', $dosen_id))
            ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen))
            ->get();

        // Rata-rata nilai per aspek
        $rataRataNilai = $penilaian->groupBy('evaluasi_id')->map(fn ($group) => $group->avg('nilai')
        );

        // Kriteria
        $kriteria = function ($nilai) {
            if ($nilai >= 4.5) {
                return 'Sangat Baik';
            }
            if ($nilai >= 3.5) {
                return 'Baik';
            }
            if ($nilai >= 2.5) {
                return 'Cukup';
            }
            if ($nilai >= 1.5) {
                return 'Kurang';
            }

            return 'Sangat Kurang';
        };

        // Ambil mahasiswa yang sudah isi penilaian
        $mahasiswaPenilaianIds = Penilaian::query()
            ->when($kurikulum_id, fn ($q) => $q->where('kurikulum_id', $kurikulum_id))
            ->when($dosen_id, fn ($q) => $q->where('dosen_id', $dosen_id))
            ->when($jenis_dosen, fn ($q) => $q->where('jenis_dosen', $jenis_dosen))
            ->distinct()
            ->pluck('mahasiswa_id')
            ->toArray();

        // Saran
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->when($dosen_id, fn ($query) => $query->where('dosen_id', $dosen_id))
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        // Info label untuk PDF
        $tahunAjaranNama = $ta_id ? TahunAkademik::find($ta_id)?->nama.' - '.TahunAkademik::find($ta_id)?->semester : null;
        $programStudiNama = $jurusan_id ? ProgramStudi::where('jurusan_id', $jurusan_id)->first()?->nama : null;
        $mataKuliahNama = $kurikulum_id ? Kurikulum::with('mataKuliah')->find($kurikulum_id)?->mataKuliah?->nama : null;
        $dosenNama = $dosen_id ? Dosen::find($dosen_id)?->nama : null;
        $jenisDosen = $jenis_dosen;

        // Settings & Logo
        $settings = Setting::first();
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = PDF::loadView('penilaian.pdf', compact(
            'penilaian', 'rataRataNilai', 'kriteria', 'sarans',
            'tahunAjaranNama', 'programStudiNama', 'mataKuliahNama',
            'dosenNama', 'jenisDosen', 'settings', 'logoBase64'
        ))->setPaper('a4', 'portrait');

        $filename = 'EDOM-'.($dosenNama ?? 'Laporan').'-'.now()->format('YmdHis').'.pdf';

        return $pdf->stream($filename);
    }

    public function resetEdom()
    {
        DB::transaction(function () {
            Mahasiswa::query()->update(['status_edom' => 0]);
        });

        activity_log('reset_edom', 'Admin me-reset status EDOM seluruh mahasiswa');

        Alert::success('Success', 'EDOM telah di-reset.');

        return redirect()->back();
    }

    public function setupEdom()
    {
        DB::transaction(function () {
            Mahasiswa::query()->update(['status_edom' => 1]);
        });

        activity_log('setup_edom', 'Admin mengaktifkan kembali status EDOM seluruh mahasiswa');

        Alert::success('Success', 'EDOM telah dipulihkan kembali.');

        return redirect()->back();
    }
}
