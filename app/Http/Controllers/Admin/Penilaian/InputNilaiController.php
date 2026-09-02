<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Exports\NilaiMahasiswaExport;
use App\Http\Controllers\Controller;
use App\Models\BobotNilai;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use RealRashid\SweetAlert\Facades\Alert;

class InputNilaiController extends Controller
{
    public function index()
    {
        // Ambil semua program studi
        $programStudi = ProgramStudi::all();
        $tahunAjaran = TahunAkademik::all();

        return view('admin.penilaian.input-nilai.index', [
            'programStudi' => $programStudi,
            'tahunAjaran' => $tahunAjaran,
        ]);
    }

    public function getMataKuliah($programStudiId, $tahunAjaranId)
    {
        // Ambil data mata kuliah berdasarkan program studi dan tahun ajaran dari tabel KRS
        $mataKuliah = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
            ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
            ->where('krs.ta_id', $tahunAjaranId) // Filter berdasarkan tahun ajaran yang dipilih
            ->where('kurikulum.jurusan_id', $programStudiId) // Filter berdasarkan program studi
            ->select('matakuliah.matakuliah_id', 'matakuliah.nama', 'matakuliah.smt', 'matakuliah.semester')
            ->distinct() // Hindari duplikasi data
            ->orderBy('matakuliah.smt', 'asc') // Urutkan berdasarkan semester (smt) naik
            ->get();

        return response()->json($mataKuliah);
    }

    /**
     * Simpan/Update bobot nilai per matakuliah
     */
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
                // Note: activity_log dipanggil sebelum return
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Tabel bobot_nilai belum ada di database. Silakan jalankan migrasi.',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Gagal menyimpan bobot nilai: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get mahasiswa list + bobot konfigurasi for a MK
     */
    public function getMahasiswa($matakuliahId, $tahunAjaranId)
    {
        try {
            // Periksa kolom yang ada di tabel KRS untuk mencegah error
            $hasTugas = Schema::hasColumn('krs', 'tugas');
            $hasAbsen = Schema::hasColumn('krs', 'absen');
            $hasPraktik = Schema::hasColumn('krs', 'praktik');

            $selectCols = ['mahasiswa.mahasiswa_id', 'mahasiswa.nama', 'mahasiswa.nim', 'krs.krs_id', 'krs.uts', 'krs.uas', 'krs.khs', 'krs.akhir'];
            if ($hasTugas) {
                $selectCols[] = 'krs.tugas';
            }
            if ($hasAbsen) {
                $selectCols[] = 'krs.absen';
            }
            if ($hasPraktik) {
                $selectCols[] = 'krs.praktik';
            }

            // 1. Ambil data mahasiswa
            $mahasiswa = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
                ->where('krs.ta_id', $tahunAjaranId)
                ->where('kurikulum.matakuliah_id', $matakuliahId)
                ->where('mahasiswa.status_mhs', 'aktif')
                ->select($selectCols)
                ->distinct()
                ->orderBy('mahasiswa.nim', 'asc')
                ->get();

            if ($mahasiswa->isEmpty()) {
                return response()->json(['message' => 'Tidak ada mahasiswa untuk mata kuliah ini'], 404);
            }

            // 2. Ambil model mata kuliah
            $mataKuliah = Matakuliah::find($matakuliahId);
            if (! $mataKuliah) {
                return response()->json(['message' => 'Data mata kuliah tidak ditemukan.'], 404);
            }

            // 3. Ambil program studi
            $programStudi = ProgramStudi::where('jurusan_id', $mataKuliah->jurusan_id)->first();
            if (! $programStudi) {
                return response()->json(['message' => 'Konfigurasi program studi tidak ditemukan.'], 404);
            }

            // 4. Tiered bobot lookup: bobot_nilai (per MK) → program_studi (default prodi) → hardcoded default
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

            // 5. Standar nilai mutu
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

            return response()->json([
                'mahasiswa' => $mahasiswa,
                'konfigurasi' => [
                    'bobot' => $bobot,
                    'bobot_source' => $bobotSource,
                    'program_studi_id' => $programStudi->jurusan_id,
                    'matakuliah_id' => $matakuliahId,
                    'mutu' => $mutu,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('getMahasiswa error: '.$e->getMessage(), [
                'matakuliah_id' => $matakuliahId,
                'ta_id' => $tahunAjaranId,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Simpan nilai mahasiswa — auto-calculate nilai akhir & huruf mutu
     */
    public function export($matakuliahId, $tahunAjaranId, $format)
    {
        try {
            $hasTugas = Schema::hasColumn('krs', 'tugas');
            $hasAbsen = Schema::hasColumn('krs', 'absen');
            $hasPraktik = Schema::hasColumn('krs', 'praktik');

            $selectCols = ['mahasiswa.mahasiswa_id', 'mahasiswa.nama', 'mahasiswa.nim', 'krs.krs_id', 'krs.uts', 'krs.uas', 'krs.khs', 'krs.akhir'];
            if ($hasTugas) {
                $selectCols[] = 'krs.tugas';
            }
            if ($hasAbsen) {
                $selectCols[] = 'krs.absen';
            }
            if ($hasPraktik) {
                $selectCols[] = 'krs.praktik';
            }

            $mahasiswa = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
                ->where('krs.ta_id', $tahunAjaranId)
                ->where('kurikulum.matakuliah_id', $matakuliahId)
                ->where('mahasiswa.status_mhs', 'aktif')
                ->select($selectCols)
                ->distinct()
                ->orderBy('mahasiswa.nim', 'asc')
                ->get();

            if ($mahasiswa->isEmpty()) {
                return back()->with('error', 'Tidak ada mahasiswa untuk diexport.');
            }

            $mataKuliah = Matakuliah::find($matakuliahId);
            $tahunAjaranFull = TahunAkademik::find($tahunAjaranId);
            $programStudi = ProgramStudi::where('jurusan_id', $mataKuliah->jurusan_id)->first();
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

            $konfigurasi = [
                'bobot' => $bobot,
                'bobot_source' => $bobotSource,
            ];

            if ($format === 'excel') {
                return Excel::download(
                    new NilaiMahasiswaExport($mahasiswa, $konfigurasi, $mataKuliah, $tahunAjaranFull),
                    'Laporan_Nilai_'.str_replace(' ', '_', $mataKuliah->nama).'.xlsx'
                );
            } elseif ($format === 'pdf') {
                $pdf = Pdf::loadView('admin.penilaian.input-nilai.export', [
                    'mahasiswa' => $mahasiswa,
                    'konfigurasi' => $konfigurasi,
                    'mataKuliah' => $mataKuliah,
                    'tahunAjaran' => $tahunAjaranFull,
                ])->setPaper('a4', 'landscape');

                return $pdf->download('Laporan_Nilai_'.str_replace(' ', '_', $mataKuliah->nama).'.pdf');
            }

            return back()->with('error', 'Format tidak didukung');

        } catch (\Exception $e) {
            Alert::error('Error', 'Terjadi kesalahan saat mengeskport data: '.$e->getMessage());

            return back();
        }
    }

    /**
     * Simpan nilai mahasiswa — auto-calculate nilai akhir & huruf mutu
     */
    public function saveNilai(Request $request)
    {
        $request->validate([
            'krs_id' => ['required', 'array'],
            'krs_id.*' => ['required', 'integer', 'exists:krs,krs_id'],
            'uts' => ['nullable', 'array'],
            'uts.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'uas' => ['nullable', 'array'],
            'uas.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tugas' => ['nullable', 'array'],
            'tugas.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'absensi' => ['nullable', 'array'],
            'absensi.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'praktik' => ['nullable', 'array'],
            'praktik.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'nilai_akhir' => ['nullable', 'array'],
            'nilai_akhir.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $uts = $request->input('uts', []);
        $uas = $request->input('uas', []);
        $tugas = $request->input('tugas', []);
        $absensi = $request->input('absensi', []);
        $praktik = $request->input('praktik', []);
        $nilaiAkhirInput = $request->input('nilai_akhir', []);
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
            $hasTugas = Schema::hasColumn('krs', 'tugas');
            $hasAbsen = Schema::hasColumn('krs', 'absen');
            $hasPraktik = Schema::hasColumn('krs', 'praktik');

            foreach ($krsIds as $mahasiswaId => $krsId) {
                $krs = Krs::with('kurikulum.mataKuliah')->find($krsId);
                if (! $krs || ! $krs->kurikulum || ! $krs->kurikulum->mataKuliah) {
                    continue;
                }

                $mataKuliah = $krs->kurikulum->mataKuliah;
                $jurusanId = $mataKuliah->jurusan_id;

                // Lookup bobot: custom per MK → default prodi → hardcoded
                $programStudi = ProgramStudi::where('jurusan_id', $jurusanId)->first();
                $bobotCustom = null;
                try {
                    if (Schema::hasTable('bobot_nilai')) {
                        $bobotCustom = BobotNilai::where('program_studi_id', $jurusanId)
                            ->where('matakuliah_id', $mataKuliah->matakuliah_id)
                            ->first();
                    }
                } catch (\Exception $e) {
                }

                $bobotUTS = ($bobotCustom ? $bobotCustom->persen_uts : null) ?? $programStudi->persen_uts ?? 25;
                $bobotUAS = ($bobotCustom ? $bobotCustom->persen_uas : null) ?? $programStudi->persen_uas ?? 35;
                $bobotTugas = ($bobotCustom ? $bobotCustom->persen_tugas : null) ?? $programStudi->persen_tugas ?? 20;
                $bobotAbsensi = ($bobotCustom ? $bobotCustom->persen_absen : null) ?? $programStudi->persen_absen ?? 10;
                $bobotPraktik = ($bobotCustom ? $bobotCustom->persen_praktik : null) ?? $programStudi->persen_praktik ?? 10;

                // Ambil nilai input
                $nilaiUTS = floatval($uts[$mahasiswaId] ?? 0);
                $nilaiUAS = floatval($uas[$mahasiswaId] ?? 0);
                $nilaiTugas = floatval($tugas[$mahasiswaId] ?? 0);
                $nilaiAbsensi = floatval($absensi[$mahasiswaId] ?? 0);
                $nilaiPraktik = floatval($praktik[$mahasiswaId] ?? 0);

                // Nilai hasil bobot tetap dihitung sebagai nilai bawaan.
                $nilaiAkhirOtomatis = round(
                    ($nilaiUTS * $bobotUTS / 100) +
                    ($nilaiUAS * $bobotUAS / 100) +
                    ($nilaiTugas * $bobotTugas / 100) +
                    ($nilaiAbsensi * $bobotAbsensi / 100) +
                    ($nilaiPraktik * $bobotPraktik / 100),
                    2
                );

                // Jika Absolute diedit, simpan nilai manual tersebut.
                // Pemeriksaan eksplisit diperlukan agar angka 0 tetap dianggap input yang sah.
                $nilaiAkhirManual = $nilaiAkhirInput[$mahasiswaId] ?? null;
                $nilaiAkhir = $nilaiAkhirManual !== null && $nilaiAkhirManual !== ''
                    ? round((float) $nilaiAkhirManual, 2)
                    : $nilaiAkhirOtomatis;

                // Konversi ke huruf mutu
                $nilaiKhs = $getMutu($nilaiAkhir);

                // Update ke database
                $updateData = [
                    'uts' => $nilaiUTS,
                    'uas' => $nilaiUAS,
                    'akhir' => $nilaiAkhir,
                    'khs' => $nilaiKhs,
                    'updated_at' => now(),
                ];

                if ($hasTugas) {
                    $updateData['tugas'] = $nilaiTugas;
                }
                if ($hasAbsen) {
                    $updateData['absen'] = $nilaiAbsensi;
                }
                if ($hasPraktik) {
                    $updateData['praktik'] = $nilaiPraktik;
                }

                $krs->update($updateData);
            }

            activity_log('input_nilai', 'Admin menyimpan nilai mahasiswa ('.count($krsIds).' mahasiswa)');

            return response()->json([
                'success' => true,
                'message' => 'Nilai berhasil diperbarui!',
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating nilai:', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Legacy store — kept for backward compatibility
     */
    public function store(Request $request)
    {
        return $this->saveBobotNilai($request);
    }
}
