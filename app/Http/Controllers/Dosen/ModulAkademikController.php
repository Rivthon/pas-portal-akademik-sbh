<?php

namespace App\Http\Controllers\Dosen;

use App\Models\Krs;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;



class ModulAkademikController extends Controller
{
    public function index()
    {
        $dosen = auth('dosen')->user();
        if (!$dosen) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai dosen!');
        }

        // Ambil semua program studi & tahun ajaran (Tujuannya untuk Select Arsip masa lalu)
        $programStudi = ProgramStudi::all();
        $tahunAjaran = TahunAkademik::orderBy('ta_id', 'desc')->get();

        // Ambil TA Aktif Untuk Dashboard Card Default
        $activeTA = TahunAkademik::where('status_ta', 1)->first();
        $mataKuliahAktif = collect(); // Kosong by default jika tidak ada yg aktif

        if ($activeTA) {
            // Ambil ID Kurikulum berdasarkan jadwal terisi dan absah Dosen ybs di TA saat ini
            $kurikulumTeori = DB::table('jadwal')
                ->join('kurikulum', 'jadwal.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('dosen_mata_kuliah', 'kurikulum.kurikulum_id', '=', 'dosen_mata_kuliah.kurikulum_id')
                ->where('jadwal.ta_id', $activeTA->ta_id)
                ->where('dosen_mata_kuliah.dosen_id', $dosen->dosen_id)
                ->pluck('kurikulum.kurikulum_id')->toArray();

            $kurikulumPraktik = DB::table('jadwal_praktik')
                ->join('kurikulum', 'jadwal_praktik.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('dosen_mata_kuliah', 'kurikulum.kurikulum_id', '=', 'dosen_mata_kuliah.kurikulum_id')
                ->where('jadwal_praktik.ta_id', $activeTA->ta_id)
                ->where('dosen_mata_kuliah.dosen_id', $dosen->dosen_id)
                ->pluck('kurikulum.kurikulum_id')->toArray();

            $allKurikulum = array_unique(array_merge($kurikulumTeori, $kurikulumPraktik));

            if (!empty($allKurikulum)) {
                $mataKuliahAktif = DB::table('kurikulum')
                    ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                    ->whereIn('kurikulum.kurikulum_id', $allKurikulum)
                    ->select('matakuliah.matakuliah_id', 'matakuliah.nama', 'matakuliah.smt', 'matakuliah.semester')
                    ->distinct()
                    ->orderBy('matakuliah.smt', 'asc')
                    ->get();
            }
        }

        return view('dosen.nilai.input-nilai', [
            'programStudi' => $programStudi,
            'tahunAjaran' => $tahunAjaran,
            'activeTA' => $activeTA,
            'mataKuliahAktif' => $mataKuliahAktif
        ]);
    }

        public function getMataKuliahDosen($programStudiId, $tahunAjaranId)
        {
            // Ambil ID dosen yang sedang login
            $dosen = auth('dosen')->user();
            if (!$dosen) {
                return response()->json(['message' => 'Dosen tidak terautentikasi.'], 401);
            }

            // Ambil Id kurikulum dari Jadwal Teori sesuai TA, Prodi dan dosen
            $kurikulumTeori = DB::table('jadwal')
                ->join('kurikulum', 'jadwal.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('dosen_mata_kuliah', 'kurikulum.kurikulum_id', '=', 'dosen_mata_kuliah.kurikulum_id')
                ->where('jadwal.ta_id', $tahunAjaranId)
                ->where('kurikulum.jurusan_id', $programStudiId)
                ->where('dosen_mata_kuliah.dosen_id', $dosen->dosen_id)
                ->pluck('kurikulum.kurikulum_id')->toArray();

            // Ambil Id kurikulum dari Jadwal Praktik sesuai TA, Prodi dan dosen
            $kurikulumPraktik = DB::table('jadwal_praktik')
                ->join('kurikulum', 'jadwal_praktik.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('dosen_mata_kuliah', 'kurikulum.kurikulum_id', '=', 'dosen_mata_kuliah.kurikulum_id')
                ->where('jadwal_praktik.ta_id', $tahunAjaranId)
                ->where('kurikulum.jurusan_id', $programStudiId)
                ->where('dosen_mata_kuliah.dosen_id', $dosen->dosen_id)
                ->pluck('kurikulum.kurikulum_id')->toArray();

            // Gabungkan menjadi satu unique array
            $allKurikulum = array_unique(array_merge($kurikulumTeori, $kurikulumPraktik));

            if (empty($allKurikulum)) {
                return response()->json(['message' => 'Tidak ada jadwal mengajar pada program studi dan rentang waktu (Tahun Ajaran) ini.'], 404);
            }

            // Ambil data mata kuliah yang sudah pasti masuk jadwal & diajar dosen tsb
            $mataKuliah = DB::table('kurikulum')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->whereIn('kurikulum.kurikulum_id', $allKurikulum)
                ->select('matakuliah.matakuliah_id', 'matakuliah.nama', 'matakuliah.smt', 'matakuliah.semester')
                ->distinct()
                ->orderBy('matakuliah.smt', 'asc')
                ->get();

            return response()->json($mataKuliah);
        }

        public function getMahasiswaDosen($matakuliahId, $tahunAjaranId)
            {
                // 1. =================================
                // AMBIL DATA MAHASISWA (Kode Anda sudah bagus, dipertahankan)
                // ===================================
                $mahasiswa = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                    ->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
                    ->where('krs.ta_id', $tahunAjaranId)
                    ->where('kurikulum.matakuliah_id', $matakuliahId)
                    ->where('mahasiswa.status_mhs', 'aktif')
                    ->select('mahasiswa.mahasiswa_id', 'mahasiswa.nama', 'mahasiswa.nim', 'krs.krs_id', 'krs.uts', 'krs.uas', 'krs.khs', 'krs.akhir','krs.tugas', 'krs.absen', 'krs.praktik') // 'absen' diganti dari krs.absensi di JS
                    ->distinct()
                    ->orderBy('mahasiswa.nim', 'asc')
                    ->get();

                if ($mahasiswa->isEmpty()) {
                    return response()->json(['message' => 'Tidak ada mahasiswa untuk mata kuliah ini'], 404);
                }

                // 2. =================================
                // AMBIL KONFIGURASI PENILAIAN (Bagian Baru)
                // ===================================

                // Ambil model mata kuliah untuk mendapatkan jurusan_id
                $mataKuliah = Matakuliah::find($matakuliahId);
                if (!$mataKuliah) {
                    return response()->json(['message' => 'Data mata kuliah tidak ditemukan.'], 404);
                }

                // Ambil model program studi berdasarkan jurusan_id
                $programStudi = ProgramStudi::where('jurusan_id', $mataKuliah->jurusan_id)->first();
                if (!$programStudi) {
                    return response()->json(['message' => 'Konfigurasi program studi tidak ditemukan.'], 404);
                }

                $bobotCustom = null;
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('bobot_nilai')) {
                        $bobotCustom = \App\Models\BobotNilai::where('program_studi_id', $programStudi->jurusan_id)
                            ->where('matakuliah_id', $matakuliahId)
                            ->first();
                    }
                } catch (\Exception $e) { }

                $bobotSource = $bobotCustom ? 'custom' : 'default';

                $bobot = [
                    'uts'     => ($bobotCustom ? $bobotCustom->persen_uts : null) ?? $programStudi->persen_uts ?? 25,
                    'uas'     => ($bobotCustom ? $bobotCustom->persen_uas : null) ?? $programStudi->persen_uas ?? 35,
                    'tugas'   => ($bobotCustom ? $bobotCustom->persen_tugas : null) ?? $programStudi->persen_tugas ?? 20,
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
                    'mahasiswa'   => $mahasiswa,
                    'konfigurasi' => [
                        'bobot'       => $bobot,
                        'bobot_source' => $bobotSource,
                        'program_studi_id' => $programStudi->jurusan_id,
                        'matakuliah_id' => $matakuliahId,
                        'mutu'        => $mutu,
                    ]
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
                    if (\Illuminate\Support\Facades\Schema::hasTable('bobot_nilai')) {
                        \App\Models\BobotNilai::updateOrCreate(
                            [
                                'program_studi_id' => $request->program_studi_id,
                                'matakuliah_id' => $request->matakuliah_id
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
                        'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                    ], 500);
                }
            }
            public function saveNilaiDosen(Request $request)
            {
                $uts = $request->input('uts', []);

                $uas = $request->input('uas', []);
                $tugas = $request->input('tugas', []);
                $absensi = $request->input('absensi', []);
                $praktik = $request->input('praktik', []);
                $krsIds = $request->input('krs_id', []);

                // Fungsi konversi angka ke mutu
                $getMutu = function ($angka) {
                    if ($angka >= 85.5) return 'A';
                    if ($angka >= 78.5) return 'AB';
                    if ($angka >= 74.5) return 'BA';
                    if ($angka >= 70.5) return 'B';
                    if ($angka >= 66.5) return 'BC';
                    if ($angka >= 59.5) return 'C';
                    if ($angka >= 45.5) return 'D';
                    return 'E';
                };

                try {
                    foreach ($krsIds as $mahasiswaId => $krsId) {
                        $krs = \App\Models\Krs::with('kurikulum.mataKuliah')->find($krsId);
                        if (!$krs || !$krs->kurikulum || !$krs->kurikulum->mataKuliah) {
                            \Log::error("KRS atau relasi tidak ditemukan untuk ID $krsId (mahasiswa $mahasiswaId)");
                            continue;
                        }

                        $mataKuliah = $krs->kurikulum->mataKuliah;
                        $jurusanId = $mataKuliah->jurusan_id;

                        // Ambil data bobot dari program_studi
                        $programStudi = \App\Models\ProgramStudi::where('jurusan_id', $jurusanId)->first();

                        if (!$programStudi) {
                            \Log::error("Program studi tidak ditemukan untuk jurusan_id $jurusanId");
                            continue;
                        }

                        // Gunakan bobot dari DB
                        $bobotUTS = $programStudi->persen_uts ?? 0;
                        $bobotUAS = $programStudi->persen_uas ?? 0;
                        $bobotTugas = $programStudi->persen_tugas ?? 0;
                        $bobotAbsensi = $programStudi->persen_absen ?? 0;
                        $bobotPraktik = $programStudi->persen_praktik ?? 0;

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
                
                $search = $request->input('search');

                // Query untuk mengambil daftar mahasiswa unik yang pernah diajar oleh dosen ini
                // melalui tabel junction dosen_mata_kuliah, kurikulum, dan krs.
                // KOREKSI: Ini mengambil mahasiswa yang Dosen ID nya adalah Dosen yang login (Mahasiswa Bimbingan PA).
                $query = Mahasiswa::with(['programStudi'])
                    ->where('dosen_id', $dosenId->dosen_id)
                    ->where('status_mhs', 'aktif');
                    
                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('nama', 'like', "%{$search}%")
                          ->orWhere('nim', 'like', "%{$search}%");
                    });
                }

                $mahasiswaList = $query->orderBy('nama', 'asc')->paginate(10)->withQueryString();

                // Kirim data ke view
                return view('dosen.nilai.lihat-nilai', [
                    'mahasiswaList' => $mahasiswaList,
                    'search' => $search
                ]);
            }

            /**
             * Menampilkan transkrip nilai detail dari seorang mahasiswa.
             * Laravel secara otomatis akan mengambil model Mahasiswa berdasarkan ID di URL (Route Model Binding).
             */
            public function transkripMahasiswa(Mahasiswa $mahasiswa)
            {
                // --- PENTING: Perbaikan Keamanan & Bug Auth ---
                // Gunakan auth()->id() untuk mendapatkan ID, bukan seluruh objek user.
                // Jika Anda menggunakan guard 'dosen', gunakan auth('dosen')->id().
                $dosenId = auth('dosen')->id().

                $isMyStudent = DB::table('krs')
                    ->join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                    ->where('krs.mahasiswa_id', $mahasiswa->mahasiswa_id)
                    ->exists();

                if (!$isMyStudent) {
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
