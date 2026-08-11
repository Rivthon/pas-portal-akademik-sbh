<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    public function index()
    {
        // Ambil semua program studi
        $programStudi = ProgramStudi::all();

        return view('admin.penilaian.nilai.index', [
            'programStudi' => $programStudi,
        ]);
    }

    public function getMahasiswaByProdiSemester(Request $request)
    {
        $prodiId = $request->input('jurusan_id');
        $semester = $request->input('semester');

        // Debugging
        \Log::info("Jurusan ID: {$prodiId}, Semester: {$semester}");

        if (! $prodiId || ! $semester) {
            return response()->json(['message' => 'Program studi dan semester harus dipilih.'], 400);
        }

        // Query
        $mahasiswa = Mahasiswa::where('jurusan_id', $prodiId)
            ->where('semester', $semester)
            ->where('status_mhs', 'aktif')
            ->get(['mahasiswa_id', 'nama']);

        return response()->json($mahasiswa);
    }

    public function getKRSByMahasiswa($mahasiswaId)
    {
        try {
            // Validasi input mahasiswaId
            if (! $mahasiswaId || ! is_numeric($mahasiswaId)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'ID Mahasiswa tidak valid.',
                ], 400);
            }

            // Query untuk mendapatkan data KRS dengan join
            $krsData = Krs::join('kurikulum', 'krs.kurikulum_id', '=', 'kurikulum.kurikulum_id')
                ->join('matakuliah', 'kurikulum.matakuliah_id', '=', 'matakuliah.matakuliah_id')
                ->join('tahun_ajaran', 'krs.ta_id', '=', 'tahun_ajaran.ta_id')
                ->where('krs.mahasiswa_id', $mahasiswaId)
                ->whereNotNull('krs.khs')
                ->whereRaw("TRIM(krs.khs) != ''")
                ->select(
                    // 'matakuliah.semester',
                    'matakuliah.smt as semester',
                    'matakuliah.sks as sks',
                    'matakuliah.nama as nama_mata_kuliah',
                    'matakuliah.matakuliah_id as kode_mk',
                    'krs.khs',
                    'krs.akhir',
                    'krs.uas',
                    'tahun_ajaran.nama as tahun_ajaran'
                )
                ->orderBy('matakuliah.smt')
                ->get()
                ->groupBy('semester'); // <--- ini kuncinya

            // Cek apakah data ditemukan
            if ($krsData->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data KRS tidak ditemukan untuk mahasiswa ini.',
                ], 404);
            }

            // Kembalikan data dalam format JSON
            return response()->json([
                'status' => 'success',
                'message' => 'Data KRS berhasil ditemukan.',
                'data' => $krsData,
            ]);

        } catch (\Exception $e) {
            // Tangani error, log error jika diperlukan
            \Log::error('Error fetching KRS data: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data KRS.',
            ], 500);
        }
    }
}
