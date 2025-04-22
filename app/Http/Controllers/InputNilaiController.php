<?php

namespace App\Http\Controllers;

use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
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

        public function getMahasiswa($matakuliahId, $tahunAjaranId)
        {
            // Validasi parameter
            if (!$matakuliahId || !$tahunAjaranId) {
                return response()->json(['message' => 'Mata kuliah ID atau Tahun Ajaran ID tidak valid'], 400);
            }

            // Ambil daftar mahasiswa berdasarkan mata kuliah dan tahun ajaran
            $mahasiswa = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->join('mahasiswa', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
                ->where('krs.ta_id', $tahunAjaranId) // Filter berdasarkan tahun ajaran yang dipilih
                ->where('kurikulum.matakuliah_id', $matakuliahId)
                ->where('mahasiswa.status_mhs','aktif')
                ->select('mahasiswa.mahasiswa_id', 'mahasiswa.nama', 'krs.krs_id', 'krs.uts', 'krs.uas', 'krs.khs', 'krs.akhir')
                ->distinct() // Hindari duplikasi data
                ->get();

            // Jika tidak ada mahasiswa yang ditemukan
            if ($mahasiswa->isEmpty()) {
                return response()->json(['message' => 'Tidak ada mahasiswa untuk mata kuliah ini pada tahun ajaran yang dipilih'], 404);
            }

            return response()->json($mahasiswa, 200);
        }


   public function saveNilai(Request $request)
    {
        $mataKuliahId = $request->input('matakuliah_id');
        $uts = $request->input('uts', []);
        $uas = $request->input('uas', []);
        $akhir = $request->input('akhir', []);
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
                $krs = Krs::find($krsId);
                if (!$krs) {
                    \Log::error("KRS ID $krsId tidak ditemukan untuk mahasiswa $mahasiswaId");
                    continue;
                }

                // Ambil nilai akhir
                $nilaiAkhir = $akhir[$mahasiswaId] ?? null;

                // Konversi nilai akhir ke khs
                $nilaiKhs = isset($nilaiAkhir) ? $getMutu($nilaiAkhir) : null;

                $krs->update([
                    'uts' => $uts[$mahasiswaId] ?? null,
                    'uas' => $uas[$mahasiswaId] ?? null,
                    'akhir' => $nilaiAkhir,
                    'khs' => $nilaiKhs, // Mutu dihitung otomatis dari "akhir"
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