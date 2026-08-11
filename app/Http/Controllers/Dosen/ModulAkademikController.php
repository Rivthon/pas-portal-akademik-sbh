<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\BobotNilai;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModulAkademikController extends Controller
{
    public function index()
    {
        $dosen = auth('dosen')->user();
        if (! $dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }

        // Ambil semua program studi & tahun ajaran (Tujuannya untuk Select Arsip masa lalu)
        $programStudi = ProgramStudi::all();
        $tahunAjaran = TahunAkademik::orderBy('ta_id', 'desc')->get();

        // Ambil TA Aktif Untuk Dashboard Card Default
        $activeTA = TahunAkademik::where('status_ta', 1)->first();
        $mataKuliahAktif = collect(); // Kosong by default jika tidak ada yg aktif

        if ($activeTA) {
            $mataKuliahAktif = Jadwal::with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
            ])
                ->where('ta_id', $activeTA->ta_id)
                ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                    $query->where('dosen_id', $dosen->dosen_id)
                        ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
                })
                ->orderBy('jurusan_id')
                ->orderBy('kurikulum_id')
                ->orderBy('jenis_kelas')
                ->get();
        }

        activity_log('akses_input_nilai', 'Dosen mengakses halaman input nilai');

        return view('dosen.nilai.input-nilai', [
            'programStudi' => $programStudi,
            'tahunAjaran' => $tahunAjaran,
            'activeTA' => $activeTA,
            'mataKuliahAktif' => $mataKuliahAktif,
        ]);
    }

    public function getMataKuliahDosen($programStudiId, $tahunAjaranId)
    {
        // Ambil ID dosen yang sedang login
        $dosen = auth('dosen')->user();
        if (! $dosen) {
            return response()->json(['message' => 'Dosen tidak terautentikasi.'], 401);
        }

        $jadwalList = Jadwal::with(['kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->where('ta_id', $tahunAjaranId)
            ->where('jurusan_id', $programStudiId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })
            ->orderBy('kurikulum_id')
            ->orderBy('jenis_kelas')
            ->get();

        if ($jadwalList->isEmpty()) {
            return response()->json(['message' => 'Tidak ada jadwal mengajar pada program studi dan rentang waktu (Tahun Ajaran) ini.'], 404);
        }

        return response()->json($jadwalList->map(function ($jadwal) {
            $mataKuliah = $jadwal->kurikulum?->mataKuliah;

            return [
                'jadwal_id' => $jadwal->id,
                'matakuliah_id' => $mataKuliah?->matakuliah_id,
                'nama' => $mataKuliah?->nama,
                'smt' => $mataKuliah?->smt ?? $mataKuliah?->semester,
                'jenis_kelas' => strtolower((string) $jadwal->jenis_kelas),
                'program_studi' => $jadwal->kurikulum?->programStudi?->nama,
            ];
        })->values());
    }

    public function getMahasiswaDosen(Jadwal $jadwal)
    {
        $dosen = auth('dosen')->user();
        abort_unless($dosen, 401);

        $jadwal->load(['kurikulum.mataKuliah', 'kurikulum.programStudi']);
        $isAssigned = $jadwal->kurikulum?->dosenToMatakuliah()
            ->where('dosen_id', $dosen->dosen_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->exists();
        abort_unless($isAssigned, 403, 'Jadwal ini bukan pengajaran Anda.');

        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);
        $mahasiswa = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
            ->where('krs.ta_id', $jadwal->ta_id)
            ->where('krs.kurikulum_id', $jadwal->kurikulum_id)
            ->where('mahasiswa.status_mhs', 'aktif')
            ->when(
                $kelasJadwal === 'karyawan',
                fn ($query) => $query->whereRaw('LOWER(mahasiswa.kelas) = ?', ['karyawan']),
                fn ($query) => $query->where(function ($kelas) {
                    $kelas->whereNull('mahasiswa.kelas')
                        ->orWhereRaw('LOWER(mahasiswa.kelas) != ?', ['karyawan']);
                })
            )
            ->select('mahasiswa.mahasiswa_id', 'mahasiswa.nama', 'mahasiswa.nim', 'krs.krs_id', 'krs.uts', 'krs.uas', 'krs.khs', 'krs.akhir', 'krs.tugas', 'krs.absen', 'krs.praktik') // 'absen' diganti dari krs.absensi di JS
            ->distinct()
            ->orderBy('mahasiswa.nim', 'asc')
            ->get();

        if ($mahasiswa->isEmpty()) {
            return response()->json(['message' => 'Tidak ada mahasiswa untuk mata kuliah ini'], 404);
        }

        // 2. =================================
        // AMBIL KONFIGURASI PENILAIAN (Bagian Baru)
        // ===================================

        $mataKuliah = $jadwal->kurikulum?->mataKuliah;
        if (! $mataKuliah) {
            return response()->json(['message' => 'Data mata kuliah tidak ditemukan.'], 404);
        }

        $programStudi = $jadwal->kurikulum?->programStudi;
        if (! $programStudi) {
            return response()->json(['message' => 'Konfigurasi program studi tidak ditemukan.'], 404);
        }
        $matakuliahId = $mataKuliah->matakuliah_id;

        $bobotCustom = null;
        try {
            if (Schema::hasTable('bobot_nilai')) {
                $bobotCustom = BobotNilai::where('program_studi_id', $programStudi->jurusan_id)
                    ->where('matakuliah_id', $matakuliahId)
                    ->first();
            }
        } catch (\Exception $e) {
        }

        $bobotSource = $bobotCustom ? 'custom' : 'default';

        $bobot = [
            'uts' => ($bobotCustom ? $bobotCustom->persen_uts : null) ?? $programStudi->persen_uts ?? 25,
            'uas' => ($bobotCustom ? $bobotCustom->persen_uas : null) ?? $programStudi->persen_uas ?? 35,
            'tugas' => ($bobotCustom ? $bobotCustom->persen_tugas : null) ?? $programStudi->persen_tugas ?? 20,
            'absensi' => ($bobotCustom ? $bobotCustom->persen_absen : null) ?? $programStudi->persen_absen ?? 10,
            'praktik' => ($bobotCustom ? $bobotCustom->persen_praktik : null) ?? $programStudi->persen_praktik ?? 10,
        ];

        // Siapkan array standar nilai mutu
        $mutu = [
            ['nilai' => 85.5, 'huruf' => 'A'],
            ['nilai' => 78.5, 'huruf' => 'AB'],
            ['nilai' => 74.5, 'huruf' => 'BA'],
            ['nilai' => 70.5, 'huruf' => 'B'],
            ['nilai' => 66.5, 'huruf' => 'BC'],
            ['nilai' => 59.5, 'huruf' => 'C'],
            ['nilai' => 45.5, 'huruf' => 'D'],
            ['nilai' => 0,    'huruf' => 'E'],
        ];

        // 3. =================================
        // GABUNGKAN SEMUA DATA DALAM SATU RESPONSE (Perbaikan Kunci)
        // ===================================
        return response()->json([
            'mahasiswa' => $mahasiswa,
            'konfigurasi' => [
                'bobot' => $bobot,
                'bobot_source' => $bobotSource,
                'program_studi_id' => $programStudi->jurusan_id,
                'matakuliah_id' => $matakuliahId,
                'jadwal_id' => $jadwal->id,
                'jenis_kelas' => $kelasJadwal,
                'mutu' => $mutu,
            ],
        ]);
    }

    public function saveBobotNilai(Request $request)
    {
        $request->validate([
            'program_studi_id' => 'required',
            'matakuliah_id' => 'required',
            'persen_tugas' => 'required|numeric|min:0|max:100',
            'persen_uts' => 'required|numeric|min:0|max:100',
            'persen_uas' => 'required|numeric|min:0|max:100',
            'persen_absen' => 'required|numeric|min:0|max:100',
            'persen_praktik' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            if (Schema::hasTable('bobot_nilai')) {
                BobotNilai::updateOrCreate(
                    [
                        'program_studi_id' => $request->program_studi_id,
                        'matakuliah_id' => $request->matakuliah_id,
                    ],
                    [
                        'persen_tugas' => $request->persen_tugas,
                        'persen_uts' => $request->persen_uts,
                        'persen_uas' => $request->persen_uas,
                        'persen_absen' => $request->persen_absen,
                        'persen_praktik' => $request->persen_praktik ?? 0,
                    ]
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Bobot nilai berhasil disimpan.',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel bobot_nilai belum ada di database.',
                ], 500);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function saveNilaiDosen(Request $request)
    {
        $request->validate([
            'jadwal_id' => ['required', 'exists:jadwal,id'],
        ]);

        $dosen = auth('dosen')->user();
        $jadwal = Jadwal::with(['kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->findOrFail($request->jadwal_id);
        $isAssigned = $jadwal->kurikulum?->dosenToMatakuliah()
            ->where('dosen_id', $dosen->dosen_id)
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->exists();
        abort_unless($isAssigned, 403, 'Jadwal ini bukan pengajaran Anda.');

        $programStudi = $jadwal->kurikulum?->programStudi;
        $mataKuliah = $jadwal->kurikulum?->mataKuliah;
        abort_unless($programStudi && $mataKuliah, 422, 'Data mata kuliah atau program studi tidak lengkap.');

        $bobotCustom = Schema::hasTable('bobot_nilai')
            ? BobotNilai::where('program_studi_id', $programStudi->jurusan_id)
                ->where('matakuliah_id', $mataKuliah->matakuliah_id)
                ->first()
            : null;
        $bobotUTS = $bobotCustom?->persen_uts ?? $programStudi->persen_uts ?? 0;
        $bobotUAS = $bobotCustom?->persen_uas ?? $programStudi->persen_uas ?? 0;
        $bobotTugas = $bobotCustom?->persen_tugas ?? $programStudi->persen_tugas ?? 0;
        $bobotAbsensi = $bobotCustom?->persen_absen ?? $programStudi->persen_absen ?? 0;
        $bobotPraktik = $bobotCustom?->persen_praktik ?? $programStudi->persen_praktik ?? 0;
        $kelasJadwal = strtolower((string) $jadwal->jenis_kelas);

        $uts = $request->input('uts', []);

        $uas = $request->input('uas', []);
        $tugas = $request->input('tugas', []);
        $absensi = $request->input('absensi', []);
        $praktik = $request->input('praktik', []);
        $krsIds = $request->input('krs_id', []);

        // Fungsi konversi angka ke mutu
        $getMutu = function ($angka) {
            if ($angka >= 85.5) {
                return 'A';
            }
            if ($angka >= 78.5) {
                return 'AB';
            }
            if ($angka >= 74.5) {
                return 'BA';
            }
            if ($angka >= 70.5) {
                return 'B';
            }
            if ($angka >= 66.5) {
                return 'BC';
            }
            if ($angka >= 59.5) {
                return 'C';
            }
            if ($angka >= 45.5) {
                return 'D';
            }

            return 'E';
        };

        try {
            foreach ($krsIds as $mahasiswaId => $krsId) {
                $krs = Krs::with(['kurikulum.mataKuliah', 'mahasiswa'])
                    ->whereKey($krsId)
                    ->where('ta_id', $jadwal->ta_id)
                    ->where('kurikulum_id', $jadwal->kurikulum_id)
                    ->whereHas('mahasiswa', function ($query) use ($kelasJadwal) {
                        if ($kelasJadwal === 'karyawan') {
                            $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                        } else {
                            $query->where(fn ($kelas) => $kelas->whereNull('kelas')
                                ->orWhereRaw('LOWER(kelas) != ?', ['karyawan']));
                        }
                    })
                    ->first();
                if (! $krs || (int) $krs->mahasiswa_id !== (int) $mahasiswaId) {
                    \Log::error("KRS atau relasi tidak ditemukan untuk ID $krsId (mahasiswa $mahasiswaId)");

                    continue;
                }

                // Ambil nilai input
                $nilaiUTS = $uts[$mahasiswaId] ?? 0;
                $nilaiUAS = $uas[$mahasiswaId] ?? 0;
                $nilaiTugas = $tugas[$mahasiswaId] ?? 0;
                $nilaiAbsensi = $absensi[$mahasiswaId] ?? 0;
                $nilaiPraktik = $praktik[$mahasiswaId] ?? 0;

                // Hitung nilai akhir otomatis
                $nilaiAkhir = round(
                    ($nilaiUTS * $bobotUTS / 100) +
                    ($nilaiUAS * $bobotUAS / 100) +
                    ($nilaiTugas * $bobotTugas / 100) +
                    ($nilaiPraktik * $bobotPraktik / 100) +
                    ($nilaiAbsensi * $bobotAbsensi / 100),
                    2
                );

                // Konversi ke huruf mutu
                $nilaiKhs = $getMutu($nilaiAkhir);

                // Update ke database
                $krs->update([
                    'uts' => $nilaiUTS,
                    'uas' => $nilaiUAS,
                    'akhir' => $nilaiAkhir,
                    'khs' => $nilaiKhs,
                    'tugas' => $nilaiTugas,
                    'absen' => $nilaiAbsensi,
                    'praktik' => $nilaiPraktik,
                    'updated_at' => now(),
                ]);
            }

            activity_log('input_nilai', 'Dosen menyimpan nilai mahasiswa ('.count($krsIds).' mahasiswa)');

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui!',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating nilai:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui data.',
            ], 500);
        }
    }

    public function lihatMahasiswaDosen(Request $request)
    {
        // Dapatkan ID dosen yang sedang login
        $dosenId = auth('dosen')->user();

        $search = trim((string) $request->input('search', ''));
        $sort = $request->input('sort', 'nama_asc');
        $statusKrs = $request->input('status_krs', 'semua');
        $semesterFilter = $request->filled('semester') ? (int) $request->input('semester') : null;

        if (! in_array($sort, ['nama_asc', 'nama_desc', 'semester_asc', 'semester_desc'], true)) {
            $sort = 'nama_asc';
        }
        if (! in_array($statusKrs, ['semua', 'belum', 'menunggu', 'disetujui'], true)) {
            $statusKrs = 'semua';
        }
        if ($semesterFilter !== null && ($semesterFilter < 1 || $semesterFilter > 14)) {
            $semesterFilter = null;
        }
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        // Query untuk mengambil daftar mahasiswa unik yang pernah diajar oleh dosen ini
        // melalui tabel junction dosen_mata_kuliah, kurikulum, dan krs.
        // KOREKSI: Ini mengambil mahasiswa yang Dosen ID nya adalah Dosen yang login (Mahasiswa Bimbingan PA).
        $query = Mahasiswa::with([
            'programStudi',
            'krs' => function ($query) use ($activeTA) {
                $query->when(
                    $activeTA,
                    fn ($krsQuery) => $krsQuery->where('ta_id', $activeTA->ta_id),
                    fn ($krsQuery) => $krsQuery->whereRaw('1 = 0')
                )->with(['kurikulum.mataKuliah', 'disetujuiOleh']);
            },
        ])
            ->withCount([
                'krs as krs_aktif_count' => function ($query) use ($activeTA) {
                    $query->when(
                        $activeTA,
                        fn ($krsQuery) => $krsQuery->where('ta_id', $activeTA->ta_id),
                        fn ($krsQuery) => $krsQuery->whereRaw('1 = 0')
                    );
                },
                'krs as krs_disetujui_count' => function ($query) use ($activeTA) {
                    $query->when(
                        $activeTA,
                        fn ($krsQuery) => $krsQuery->where('ta_id', $activeTA->ta_id)->whereNotNull('disetujui_pada'),
                        fn ($krsQuery) => $krsQuery->whereRaw('1 = 0')
                    );
                },
            ])
            ->where('dosen_id', $dosenId->dosen_id)
            ->where('status_mhs', 'aktif');

        if (! empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        if ($semesterFilter !== null) {
            $query->where('semester', $semesterFilter);
        }

        if ($statusKrs !== 'semua') {
            if (! $activeTA) {
                $query->when($statusKrs !== 'belum', fn ($q) => $q->whereRaw('1 = 0'));
            } elseif ($statusKrs === 'belum') {
                $query->whereDoesntHave('krs', fn ($q) => $q->where('ta_id', $activeTA->ta_id));
            } elseif ($statusKrs === 'menunggu') {
                $query->whereHas('krs', fn ($q) => $q
                    ->where('ta_id', $activeTA->ta_id)
                    ->whereNull('disetujui_pada'));
            } else {
                $query->whereHas('krs', fn ($q) => $q->where('ta_id', $activeTA->ta_id))
                    ->whereDoesntHave('krs', fn ($q) => $q
                        ->where('ta_id', $activeTA->ta_id)
                        ->whereNull('disetujui_pada'));
            }
        }

        match ($sort) {
            'nama_desc' => $query->orderBy('nama', 'desc'),
            'semester_asc' => $query->orderBy('semester')->orderBy('nama'),
            'semester_desc' => $query->orderByDesc('semester')->orderBy('nama'),
            default => $query->orderBy('nama'),
        };

        $mahasiswaList = $query->paginate(10)->withQueryString();

        $sksKurikulumCache = [];
        if ($activeTA) {
            $mahasiswaList->getCollection()->each(function (Mahasiswa $mahasiswa) use ($activeTA, &$sksKurikulumCache) {
                $kelas = $this->normalizeJenisKelasMahasiswa($mahasiswa->kelas);
                $cacheKey = implode('|', [
                    $activeTA->ta_id,
                    $mahasiswa->jurusan_id,
                    (int) $mahasiswa->semester,
                    $kelas ?? 'semua',
                ]);

                if (! array_key_exists($cacheKey, $sksKurikulumCache)) {
                    $sksKurikulumCache[$cacheKey] = $this->getSksKurikulumMahasiswa(
                        $mahasiswa,
                        $activeTA,
                        $kelas
                    );
                }

                $sksKurikulum = $sksKurikulumCache[$cacheKey];
                $totalSksKrs = $mahasiswa->krs->sum(
                    fn ($krs) => (int) ($krs->kurikulum?->mataKuliah?->sks ?? 0)
                );

                $mahasiswa->setAttribute('sks_kurikulum', $sksKurikulum);
                $mahasiswa->setAttribute(
                    'selisih_sks_kurikulum',
                    $sksKurikulum === null ? null : $sksKurikulum - $totalSksKrs
                );
            });
        }

        // Kirim data ke view
        return view('dosen.nilai.lihat-nilai', [
            'mahasiswaList' => $mahasiswaList,
            'search' => $search,
            'activeTA' => $activeTA,
            'sort' => $sort,
            'statusKrs' => $statusKrs,
            'semesterFilter' => $semesterFilter,
        ]);
    }

    public function lihatKrsMahasiswa(Mahasiswa $mahasiswa)
    {
        $dosen = auth('dosen')->user();
        $this->ensureMahasiswaBimbingan($mahasiswa, (int) $dosen->dosen_id);
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        $krs = collect();
        if ($activeTA) {
            $krs = Krs::with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'disetujuiOleh',
            ])
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $activeTA->ta_id)
                ->get()
                ->sortBy(fn ($item) => [
                    (int) ($item->kurikulum?->mataKuliah?->smt ?? 0),
                    (string) ($item->kurikulum?->mataKuliah?->nama ?? ''),
                ])
                ->values();
        }

        $mahasiswa->load('programStudi');
        $totalSks = $krs->sum(fn ($item) => (int) ($item->kurikulum?->mataKuliah?->sks ?? 0));
        $sudahDisetujui = $krs->isNotEmpty() && $krs->every(fn ($item) => $item->disetujui_pada !== null);
        $transkrip = $krs;
        $transkrip->each(function ($item) {
            $item->khs = $item->disetujui_pada ? 'Disetujui' : 'Menunggu ACC';
        });
        $ipk = '-';
        $isKrsView = true;

        return view('dosen.nilai.transkrip', compact(
            'mahasiswa',
            'activeTA',
            'transkrip',
            'totalSks',
            'sudahDisetujui',
            'ipk',
            'isKrsView'
        ));
    }

    public function approveKrs(Mahasiswa $mahasiswa)
    {
        $dosen = auth('dosen')->user();
        $this->ensureMahasiswaBimbingan($mahasiswa, (int) $dosen->dosen_id);
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $krsQuery = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id);
        $totalKrs = (clone $krsQuery)->count();

        if ($totalKrs === 0) {
            return back()->with('error', 'Mahasiswa belum mengambil KRS pada Tahun Akademik aktif.');
        }

        $updated = $krsQuery->whereNull('disetujui_pada')->update([
            'disetujui_oleh' => $dosen->dosen_id,
            'disetujui_pada' => now(),
            'updated_at' => now(),
        ]);

        activity_log('acc_krs', "Dosen menyetujui {$updated} mata kuliah KRS mahasiswa ID {$mahasiswa->mahasiswa_id}");

        return back()->with(
            $updated > 0 ? 'success' : 'info',
            $updated > 0 ? "KRS {$mahasiswa->nama} berhasil disetujui." : "KRS {$mahasiswa->nama} sudah disetujui sebelumnya."
        );
    }

    public function cancelKrsApproval(Mahasiswa $mahasiswa)
    {
        $dosen = auth('dosen')->user();
        $this->ensureMahasiswaBimbingan($mahasiswa, (int) $dosen->dosen_id);
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $krsQuery = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $activeTA->ta_id);

        if (! (clone $krsQuery)->exists()) {
            return back()->with('error', 'Mahasiswa belum mengambil KRS pada Tahun Akademik aktif.');
        }

        $updated = $krsQuery->whereNotNull('disetujui_pada')->update([
            'disetujui_oleh' => null,
            'disetujui_pada' => null,
            'updated_at' => now(),
        ]);

        activity_log(
            'batalkan_acc_krs',
            "Dosen membatalkan persetujuan {$updated} mata kuliah KRS mahasiswa ID {$mahasiswa->mahasiswa_id}"
        );

        return back()->with(
            $updated > 0 ? 'success' : 'info',
            $updated > 0
                ? "ACC KRS {$mahasiswa->nama} berhasil dibatalkan. Mahasiswa dapat memperbaiki KRS sebelum diajukan kembali."
                : "KRS {$mahasiswa->nama} sedang menunggu ACC."
        );
    }

    public function bulkApproveKrs(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_ids' => ['required', 'array', 'min:1', 'max:200'],
            'mahasiswa_ids.*' => ['required', 'integer', 'distinct', 'exists:mahasiswa,mahasiswa_id'],
        ], [
            'mahasiswa_ids.required' => 'Pilih minimal satu mahasiswa untuk Bulk ACC.',
            'mahasiswa_ids.min' => 'Pilih minimal satu mahasiswa untuk Bulk ACC.',
        ]);

        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first();

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $mahasiswaIds = Mahasiswa::where('dosen_id', $dosen->dosen_id)
            ->where('status_mhs', 'aktif')
            ->whereIn('mahasiswa_id', $validated['mahasiswa_ids'])
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $activeTA->ta_id))
            ->pluck('mahasiswa_id');

        if ($mahasiswaIds->isEmpty()) {
            return back()->with('error', 'Tidak ada KRS mahasiswa bimbingan yang dapat disetujui.');
        }

        $updated = Krs::whereIn('mahasiswa_id', $mahasiswaIds)
            ->where('ta_id', $activeTA->ta_id)
            ->whereNull('disetujui_pada')
            ->update([
                'disetujui_oleh' => $dosen->dosen_id,
                'disetujui_pada' => now(),
                'updated_at' => now(),
            ]);

        activity_log('bulk_acc_krs', "Dosen menyetujui {$updated} mata kuliah KRS untuk {$mahasiswaIds->count()} mahasiswa bimbingan");

        return back()->with(
            $updated > 0 ? 'success' : 'info',
            $updated > 0
                ? "Bulk ACC berhasil untuk {$mahasiswaIds->count()} mahasiswa ({$updated} mata kuliah)."
                : 'Semua KRS mahasiswa yang dipilih sudah disetujui sebelumnya.'
        );
    }

    private function ensureMahasiswaBimbingan(Mahasiswa $mahasiswa, int $dosenId): void
    {
        abort_unless(
            (int) $mahasiswa->dosen_id === $dosenId && strtolower((string) $mahasiswa->status_mhs) === 'aktif',
            403,
            'Mahasiswa bukan bimbingan akademik Anda.'
        );
    }

    private function getSksKurikulumMahasiswa(
        Mahasiswa $mahasiswa,
        TahunAkademik $tahunAkademik,
        ?string $jenisKelas = null
    ): ?int {
        $kurikulum = Kurikulum::with('mataKuliah')
            ->where('ta_id', $tahunAkademik->ta_id)
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->whereHas('mataKuliah', fn ($query) => $query
                ->where('smt', (int) $mahasiswa->semester))
            ->when($jenisKelas !== null, fn ($query) => $query
                ->whereHas('dosenToMatakuliah', fn ($penugasan) => $penugasan
                    ->whereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas])))
            ->get();

        if ($kurikulum->isEmpty()) {
            return null;
        }

        return $kurikulum->sum(
            fn (Kurikulum $item) => (int) ($item->mataKuliah?->sks ?? 0)
        );
    }

    private function normalizeJenisKelasMahasiswa(?string $kelas): ?string
    {
        return match (strtolower(trim((string) $kelas))) {
            'pagi', 'reguler' => 'reguler',
            'karyawan' => 'karyawan',
            default => null,
        };
    }

    /**
     * Menampilkan transkrip nilai detail dari seorang mahasiswa.
     * Laravel secara otomatis akan mengambil model Mahasiswa berdasarkan ID di URL (Route Model Binding).
     */
    public function transkripMahasiswa(Mahasiswa $mahasiswa)
    {
        if ((int) $mahasiswa->dosen_id !== (int) auth('dosen')->id()) {
            abort(403, 'Anda tidak berhak mengakses data mahasiswa ini.');
        }

        // --- Ambil Data Transkrip dengan Eloquent Relationship (Sesuai Permintaan) ---
        $transkrip = Krs::with(['kurikulum.mataKuliah', 'tahunAjaran'])
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereNotNull('khs')
            ->whereRaw("TRIM(krs.khs) != ''")
            ->get()
            ->sortBy('kurikulum.mataKuliah.smt'); // Urutkan berdasarkan semester

        // --- Kalkulasi IPK (Disesuaikan dengan struktur data baru) ---
        $totalSks = 0;
        $totalBobot = 0;
        $gradePoints = ['A' => 4, 'AB' => 3.5, 'BA' => 3.5, 'B' => 3, 'BC' => 2.5, 'C' => 2, 'D' => 1, 'E' => 0];

        foreach ($transkrip as $nilai) {
            // Akses SKS melalui relasi: $nilai->kurikulum->mataKuliah->sks
            if (isset($gradePoints[$nilai->khs]) && $nilai->kurikulum && $nilai->kurikulum->mataKuliah) {
                $sks = $nilai->kurikulum->mataKuliah->sks;
                $totalSks += $sks;
                $totalBobot += $sks * $gradePoints[$nilai->khs];
            }
        }

        $ipk = ($totalSks > 0) ? round($totalBobot / $totalSks, 2) : 0;

        // Kirim semua data ke view
        return view('dosen.nilai.transkrip', compact(
            'mahasiswa',
            'transkrip',
            'totalSks',
            'ipk'
        ));
    }

    public function indexRps()
    {
        return view('dosen.materi.index');
    }

    public function tambahRps()
    {
        return view('dosen.materi.create');
    }
}
