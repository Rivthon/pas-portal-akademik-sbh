<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Http\Controllers\Controller;

use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AktivasiController extends Controller
{
    public function index(Request $request)
    {
        // Ambil input pencarian & filter
        $search = $request->input('search');
        $jurusanId = $request->input('jurusan_id');
        $semester = $request->input('semester');
        $kelas = $request->input('kelas');

        // Query data mahasiswa dengan relasi program studi
        $query = Mahasiswa::with('programStudi')
            ->where('status_mhs', 'aktif')
            ->orderBy('semester', 'asc');

        // Filter pencarian
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('nim', 'like', '%' . $search . '%')
                    ->orWhereHas('programStudi', function ($pq) use ($search) {
                        $pq->where('nama', 'like', '%' . $search . '%');
                    });
            });
        }

        // Filter program studi
        if ($jurusanId) {
            $query->where('jurusan_id', $jurusanId);
        }

        // Filter semester
        if ($semester) {
            $query->where('semester', $semester);
        }

        // Filter kelas
        if ($kelas) {
            $query->where('kelas', $kelas);
        }

        // Paginate hasil query
        $mahasiswa = $query->paginate(15)->appends($request->query());

        // Data untuk filter dropdown
        $programStudiList = ProgramStudi::orderBy('nama')->get();

        // Statistik ringkasan (dari mahasiswa aktif secara keseluruhan)
        $totalAktif = Mahasiswa::where('status_mhs', 'aktif')->count();
        $stats = [
            'total'     => $totalAktif,
            'krs'       => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_krs', 1)->count() / $totalAktif * 100, 1) : 0,
            'uts'       => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_uts', 1)->count() / $totalAktif * 100, 1) : 0,
            'uas'       => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_uas', 1)->count() / $totalAktif * 100, 1) : 0,
            'krs_count' => Mahasiswa::where('status_mhs', 'aktif')->where('status_krs', 1)->count(),
            'uts_count' => Mahasiswa::where('status_mhs', 'aktif')->where('status_uts', 1)->count(),
            'uas_count' => Mahasiswa::where('status_mhs', 'aktif')->where('status_uas', 1)->count(),
        ];

        // Periksa apakah request melalui AJAX
        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.kemahasiswaan.aktivasi-mhs.partials_list', compact('mahasiswa'))->render(),
                'stats' => $stats,
            ]);
        }

        // Hitung nomor indeks untuk paginasi
        $pageIndex = ($mahasiswa->currentPage() - 1) * $mahasiswa->perPage();

        // Kirim data ke view utama
        return view('admin.kemahasiswaan.aktivasi-mhs.index', compact('mahasiswa', 'pageIndex', 'search', 'programStudiList', 'stats'));
    }


    public function updateStatus(Request $request)
    {
        try {
            \Log::info('Request Data:', $request->all());

            $mahasiswa = Mahasiswa::findOrFail($request->mahasiswa_id);
            $field = 'status_' . $request->type;

            if (in_array($field, ['status_krs', 'status_uts', 'status_uas', 'status_nilai_uts', 'status_nilai_uas', 'status_nilai_khs', 'status_uap'])) {
                $mahasiswa->$field = $request->status;
                $mahasiswa->save();

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($request->type) . " berhasil diperbarui.",
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Field tidak valid.']);
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.']);
        }
    }

    public function resetAllStatus()
    {
        try {
            Mahasiswa::query()->update([
                'status_krs' => 0,
                'status_uts' => 0,
                'status_uas' => 0,
                'status_nilai_uts' => 0,
                'status_nilai_uas' => 0,
                'status_uap' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Semua status berhasil direset.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mereset status.',
            ]);
        }
    }

    /**
     * Bulk update status untuk satu jenis (krs, uts, uas, dll)
     */
    public function bulkUpdateStatus(Request $request)
    {
        try {
            $type = $request->input('type');
            $status = $request->input('status', 1);
            $field = 'status_' . $type;

            $validFields = ['status_krs', 'status_uts', 'status_uas', 'status_nilai_uts', 'status_nilai_uas', 'status_uap'];

            if (!in_array($field, $validFields)) {
                return response()->json(['success' => false, 'message' => 'Field tidak valid.']);
            }

            $query = Mahasiswa::where('status_mhs', 'aktif');

            // Opsional: filter berdasarkan prodi/semester/kelas
            if ($request->filled('jurusan_id')) {
                $query->where('jurusan_id', $request->jurusan_id);
            }
            if ($request->filled('semester')) {
                $query->where('semester', $request->semester);
            }
            if ($request->filled('kelas')) {
                $query->where('kelas', $request->kelas);
            }

            $affected = $query->update([$field => $status]);

            return response()->json([
                'success' => true,
                'message' => ucfirst($type) . " berhasil diperbarui untuk {$affected} mahasiswa.",
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk update: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat bulk update.',
            ]);
        }
    }
}