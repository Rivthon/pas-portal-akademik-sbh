<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\BobotNilai;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use App\Models\TahunAkademik;
use Illuminate\Support\Facades\DB;
use RealRashid\SweetAlert\Facades\Alert;

class InputNilaiController extends Controller
{

        public function index()
        {
            // Ambil semua program studi
            $programStudi = ProgramStudi::all();
            $tahunAjaran = TahunAkademik::all();
            return view('input-nilai.index', [
                'programStudi' => $programStudi,
                'tahunAjaran' => $tahunAjaran
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
        public function store(Request $request)
        {
            $validated = $request->validate([
                'program_studi_id' => 'required',
                'matakuliah_id' => 'required',
                'persen_tugas' => 'required|numeric',
                'persen_uts' => 'required|numeric',
                'persen_uas' => 'required|numeric',
                'persen_absen' => 'required|numeric',
                'persen_praktik' => 'nullable|numeric',
            ]);

            BobotNilai::updateOrCreate(
                [
                    'program_studi_id' => $request->program_studi_id,
                    'matakuliah_id' => $request->matakuliah_id
                ],
                $validated
            );

        return back()->with('success', 'Bobot nilai berhasil disimpan.');
        }
        public function getMahasiswa($matakuliahId, $tahunAjaranId)
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

            // Siapkan array bobot, gunakan default value jika di DB null
            $bobot = [
                'uts'     => $programStudi->persen_uts ?? 25,
                'uas'     => $programStudi->persen_uas ?? 35,
                'tugas'   => $programStudi->persen_tugas ?? 20,
                'absensi' => $programStudi->persen_absen ?? 10, // pastikan nama kolom 'persen_absen'
                'praktik' => $programStudi->persen_praktik ?? 10,
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
                    'bobot' => $bobot,
                    'mutu'  => $mutu,
                ]
            ]);
        }


        public function saveNilai(Request $request)
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
                    $nilaiUAP = $uap[$mahasiswaId] ?? 0;
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

}
