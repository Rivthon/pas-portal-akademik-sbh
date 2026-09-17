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
        $this->middleware('permission:evaluasi-list', ['only' => ['index', 'overview', 'detail', 'getKurikulumAjax', 'getDosenAjax', 'cetakRegistry', 'cetakPdf']]);
        $this->middleware('permission:penilaian-reset-edom', ['only' => ['resetEdom', 'setupEdom']]);
    }

    public function index(Request $request)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');
        $kurikulum_id = $request->input('kurikulum_id');
        $dosen_id = $request->input('dosen_id');
        $jenis_dosen = $request->input('jenis_dosen');
        $jenis_kelas = $this->normalizeJenisKelas($request->input('jenis_kelas'));

        $tahunAjaran = TahunAkademik::orderBy('created_at', 'desc')->get();
        $programStudi = ProgramStudi::all();
        $edomEnabled = (bool) (Setting::query()->value('edom_enabled') ?? true);
        $sarans = collect();

        $assignments = collect();
        if ($ta_id || $jurusan_id || $kurikulum_id || $dosen_id || $jenis_dosen || $jenis_kelas) {
            $assignmentsQuery = DosenMatakuliah::with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->when($ta_id, function ($q) use ($ta_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->where('ta_id', $ta_id));
                })
                ->when($jurusan_id, function ($q) use ($jurusan_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->whereHas('programStudi', fn ($s2) => $s2->where('jurusan_id', $jurusan_id)));
                })
                ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
                ->when($dosen_id, fn ($query) => $query->where('dosen_id', $dosen_id))
                ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen))
                ->when($jenis_kelas, function ($query) use ($jenis_kelas) {
                    $query->where(function ($kelasQuery) use ($jenis_kelas) {
                        $kelasQuery->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$jenis_kelas]);
                        if ($jenis_kelas === 'reguler') {
                            $kelasQuery->orWhereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                        }
                    });
                });

            $assignments = $assignmentsQuery->get();

            // Tandai status EDOM
            $evaluatedPairs = Penilaian::select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
                ->distinct()
                ->get()
                ->map(fn ($p) => $this->edomKey(
                    $p->dosen_id,
                    $p->kurikulum_id,
                    $p->jenis_dosen,
                    $p->jenis_kelas
                ))
                ->toArray();

            $assignments->map(function ($assign) use ($evaluatedPairs) {
                $key = $this->edomKey(
                    $assign->dosen_id,
                    $assign->kurikulum_id,
                    $assign->jenis_dosen,
                    $assign->jenis_kelas
                );
                $assign->status_edom = in_array($key, $evaluatedPairs, true);

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
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen', 'jenis_kelas',
            'assignments', 'sarans', 'edomEnabled'
        ));
    }

    public function overview(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $jurusanId = $request->integer('jurusan_id') ?: null;
        $sort = in_array($request->input('sort'), ['highest', 'lowest', 'name'], true)
            ? $request->input('sort')
            : 'highest';

        $dosenRows = Dosen::query()
            ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'dosen.jurusan_id')
            ->leftJoin('penilaian', 'penilaian.dosen_id', '=', 'dosen.dosen_id')
            ->leftJoin('kurikulum', 'kurikulum.kurikulum_id', '=', 'penilaian.kurikulum_id')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('dosen.nama', 'like', '%'.$search.'%')
                        ->orWhere('dosen.nidn', 'like', '%'.$search.'%')
                        ->orWhere('dosen.kd_dosen', 'like', '%'.$search.'%');
                });
            })
            ->when($jurusanId, fn ($query) => $query->where('dosen.jurusan_id', $jurusanId))
            ->select([
                'dosen.dosen_id',
                'dosen.nama',
                'dosen.nidn',
                'dosen.kd_dosen',
                'dosen.status_dosen',
                'program_studi.nama as program_studi',
                DB::raw('ROUND(AVG(CAST(penilaian.nilai AS DECIMAL(10,2))), 2) as rata_rata_edom'),
                DB::raw("COUNT(DISTINCT CONCAT(penilaian.mahasiswa_id, '|', penilaian.kurikulum_id, '|', COALESCE(penilaian.jenis_dosen, ''), '|', COALESCE(penilaian.jenis_kelas, ''))) as jumlah_formulir"),
                DB::raw('COUNT(DISTINCT penilaian.mahasiswa_id) as jumlah_mahasiswa'),
                DB::raw('COUNT(DISTINCT kurikulum.ta_id) as jumlah_tahun_ajaran'),
            ])
            ->groupBy(
                'dosen.dosen_id',
                'dosen.nama',
                'dosen.nidn',
                'dosen.kd_dosen',
                'dosen.status_dosen',
                'program_studi.nama'
            )
            ->when($sort === 'highest', fn ($query) => $query
                ->orderByRaw('AVG(penilaian.nilai) IS NULL')
                ->orderByDesc('rata_rata_edom'))
            ->when($sort === 'lowest', fn ($query) => $query
                ->orderByRaw('AVG(penilaian.nilai) IS NULL')
                ->orderBy('rata_rata_edom'))
            ->when($sort === 'name', fn ($query) => $query->orderBy('dosen.nama'))
            ->orderBy('dosen.nama')
            ->paginate(20)
            ->withQueryString();

        $statistics = $this->edomCompletionStatistics();
        $programStudi = ProgramStudi::query()->orderBy('nama')->get(['jurusan_id', 'nama']);

        activity_log('lihat_ringkasan_edom', 'Admin melihat ringkasan nilai dan statistik pengisian EDOM');

        return view('admin.penilaian.penilaian.overview', compact(
            'dosenRows',
            'statistics',
            'programStudi',
            'search',
            'jurusanId',
            'sort'
        ));
    }

    public function detail(Request $request, $dosen_id, $kurikulum_id, $jenis_dosen)
    {
        $ta_id = $request->input('ta_id');
        $jurusan_id = $request->input('jurusan_id');
        $jenis_kelas = $this->normalizeJenisKelas($request->input('jenis_kelas'));

        abort_if(! $jenis_kelas, 422, 'Kelas EDOM wajib dipilih.');

        $penilaian = Penilaian::with('evaluasi')
            ->where('kurikulum_id', $kurikulum_id)
            ->where('dosen_id', $dosen_id)
            ->where('jenis_dosen', $jenis_dosen)
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenis_kelas])
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
            ->where('kurikulum_id', $kurikulum_id)
            ->where('jenis_dosen', $jenis_dosen)
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenis_kelas])
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        $dosen = Dosen::find($dosen_id);
        $kurikulum = Kurikulum::with(['mataKuliah', 'programStudi'])->find($kurikulum_id);

        return view('admin.penilaian.penilaian.detail', compact(
            'penilaian', 'rataRataNilai', 'kriteria', 'sarans',
            'ta_id', 'jurusan_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen', 'jenis_kelas',
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
                'nama' => ($k->mataKuliah->nama ?? 'Unknown').' - SMT '.($k->mataKuliah?->smt ?? '-'),
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
        $jenis_kelas = $this->normalizeJenisKelas($request->input('jenis_kelas'));

        $assignments = collect();
        if ($ta_id || $jurusan_id || $kurikulum_id || $jenis_dosen || $jenis_kelas) {
            $assignmentsQuery = DosenMatakuliah::with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
                ->when($ta_id, function ($q) use ($ta_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->where('ta_id', $ta_id));
                })
                ->when($jurusan_id, function ($q) use ($jurusan_id) {
                    $q->whereHas('kurikulum', fn ($sub) => $sub->whereHas('programStudi', fn ($s2) => $s2->where('jurusan_id', $jurusan_id)));
                })
                ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
                ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen))
                ->when($jenis_kelas, function ($query) use ($jenis_kelas) {
                    $query->where(function ($kelasQuery) use ($jenis_kelas) {
                        $kelasQuery->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$jenis_kelas]);
                        if ($jenis_kelas === 'reguler') {
                            $kelasQuery->orWhereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                        }
                    });
                });

            $assignments = $assignmentsQuery->get();

            $evaluatedPairs = Penilaian::select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
                ->distinct()
                ->get()
                ->map(fn ($p) => $this->edomKey($p->dosen_id, $p->kurikulum_id, $p->jenis_dosen, $p->jenis_kelas))
                ->toArray();

            $assignments->map(function ($assign) use ($evaluatedPairs) {
                $key = $this->edomKey($assign->dosen_id, $assign->kurikulum_id, $assign->jenis_dosen, $assign->jenis_kelas);
                $assign->status_edom = in_array($key, $evaluatedPairs, true);

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
        $jenis_kelas = $this->normalizeJenisKelas($request->input('jenis_kelas'));

        abort_if(! $jenis_kelas, 422, 'Kelas EDOM wajib dipilih.');

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
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenis_kelas])
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
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenis_kelas])
            ->distinct()
            ->pluck('mahasiswa_id')
            ->toArray();

        // Saran
        $sarans = Saran::with(['mahasiswa', 'dosen'])
            ->when($dosen_id, fn ($query) => $query->where('dosen_id', $dosen_id))
            ->when($kurikulum_id, fn ($query) => $query->where('kurikulum_id', $kurikulum_id))
            ->when($jenis_dosen, fn ($query) => $query->where('jenis_dosen', $jenis_dosen))
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenis_kelas])
            ->whereIn('mahasiswa_id', $mahasiswaPenilaianIds)
            ->get();

        // Info label untuk PDF
        $tahunAjaranNama = $ta_id ? TahunAkademik::find($ta_id)?->nama.' - '.TahunAkademik::find($ta_id)?->semester : null;
        $programStudiNama = $jurusan_id ? ProgramStudi::where('jurusan_id', $jurusan_id)->first()?->nama : null;
        $mataKuliahNama = $kurikulum_id ? Kurikulum::with('mataKuliah')->find($kurikulum_id)?->mataKuliah?->nama : null;
        $dosenNama = $dosen_id ? Dosen::find($dosen_id)?->nama : null;
        $jenisDosen = $jenis_dosen;
        $jenisKelas = $jenis_kelas;

        // Settings & Logo
        $settings = Setting::first();
        $logoBase64 = null;
        if ($settings && $settings->logo) {
            $logoPath = public_path('storage/'.$settings->logo);
            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }
        }

        $pdf = PDF::loadView('admin.penilaian.penilaian.pdf', compact(
            'penilaian', 'rataRataNilai', 'kriteria', 'sarans',
            'tahunAjaranNama', 'programStudiNama', 'mataKuliahNama',
            'dosenNama', 'jenisDosen', 'jenisKelas', 'settings', 'logoBase64'
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

    public function toggleEdom(Request $request)
    {
        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        $setting = Setting::query()->firstOrFail();
        $enabled = (bool) $validated['enabled'];
        $setting->update(['edom_enabled' => $enabled]);

        activity_log(
            'toggle_edom_mahasiswa',
            'Admin '.($enabled ? 'mengaktifkan' : 'menonaktifkan').' akses EDOM mahasiswa'
        );

        Alert::success(
            'Berhasil',
            $enabled
                ? 'EDOM mahasiswa telah diaktifkan dan sekarang dapat diakses.'
                : 'EDOM mahasiswa telah dinonaktifkan.'
        );

        return redirect()->back();
    }

    private function edomCompletionStatistics(): array
    {
        // Satu baris jawaban per pertanyaan tidak boleh dihitung sebagai satu formulir.
        $submitted = DB::table('penilaian')
            ->select('mahasiswa_id', 'kurikulum_id', 'dosen_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()->get()->mapWithKeys(fn ($row) => [
                $row->mahasiswa_id.'|'.$this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas) => true,
            ]);

        $required = DB::table('krs')
            ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('dosen_mata_kuliah as assignment', 'assignment.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('dosen', 'dosen.dosen_id', '=', 'assignment.dosen_id')
            ->whereColumn('krs.ta_id', 'kurikulum.ta_id')
            ->whereIn('assignment.jenis_dosen', ['teori', 'praktik'])
            ->whereRaw("CASE WHEN LOWER(TRIM(mahasiswa.kelas)) IN ('karyawan', 'reguler b') THEN 'karyawan' ELSE 'reguler' END = CASE WHEN LOWER(TRIM(assignment.jenis_kelas)) IN ('karyawan', 'reguler b') THEN 'karyawan' ELSE 'reguler' END")
            ->select('krs.ta_id', 'krs.mahasiswa_id', 'krs.kurikulum_id', 'assignment.dosen_id', 'assignment.jenis_dosen', 'assignment.jenis_kelas')
            ->distinct()->get()
            ->unique(fn ($row) => $row->ta_id.'|'.$row->mahasiswa_id.'|'.$this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas))
            ->groupBy('ta_id');

        $years = TahunAkademik::orderByDesc('ta_id')->get()->map(function ($year) use ($required, $submitted) {
            $items = $required->get($year->ta_id, collect());
            $filled = $items->filter(fn ($row) => $submitted->has(
                $row->mahasiswa_id.'|'.$this->edomKey($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas)
            ))->count();

            return [
                'ta_id' => $year->ta_id,
                'label' => $year->nama.' - '.ucfirst($year->semester),
                'active' => (bool) $year->status_ta,
                'total' => $items->count(),
                'filled' => $filled,
                'unfilled' => $items->count() - $filled,
                'percent' => $items->count() ? round($filled / $items->count() * 100, 1) : null,
            ];
        });

        $total = $years->sum('total');
        $filled = $years->sum('filled');

        return [
            'years' => $years,
            'total' => $total,
            'filled' => $filled,
            'unfilled' => $total - $filled,
            'percent' => $total ? round($filled / $total * 100, 1) : null,
        ];
    }

    private function normalizeJenisKelas(?string $jenisKelas): ?string
    {
        return match (strtolower(trim((string) $jenisKelas))) {
            'pagi', 'reguler', 'reguler a' => 'reguler',
            'karyawan', 'reguler b' => 'karyawan',
            default => null,
        };
    }

    private function edomKey($dosenId, $kurikulumId, ?string $jenisDosen, ?string $jenisKelas): string
    {
        $normalizedKelas = $this->normalizeJenisKelas($jenisKelas);
        if (! $normalizedKelas && trim((string) $jenisKelas) === '') {
            $normalizedKelas = 'reguler';
        }

        return implode('|', [
            (int) $dosenId,
            (int) $kurikulumId,
            strtolower(trim((string) $jenisDosen)),
            $normalizedKelas ?? '',
        ]);
    }
}
