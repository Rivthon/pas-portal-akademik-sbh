<?php

namespace App\Http\Controllers\Admin\Kemahasiswaan;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AktivasiController extends Controller
{
    private const KEBIDANAN_JURUSAN_ID = '15401';

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
                $q->where('nama', 'like', '%'.$search.'%')
                    ->orWhere('nim', 'like', '%'.$search.'%')
                    ->orWhereHas('programStudi', function ($pq) use ($search) {
                        $pq->where('nama', 'like', '%'.$search.'%');
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
            'total' => $totalAktif,
            'krs' => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_krs', 1)->count() / $totalAktif * 100, 1) : 0,
            'uts' => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_uts', 1)->count() / $totalAktif * 100, 1) : 0,
            'uas' => $totalAktif > 0 ? round(Mahasiswa::where('status_mhs', 'aktif')->where('status_uas', 1)->count() / $totalAktif * 100, 1) : 0,
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
            $field = 'status_'.$request->type;

            if (in_array($field, ['status_krs', 'status_uts', 'status_uas', 'status_nilai_uts', 'status_nilai_uas', 'status_nilai_khs', 'status_akhir'])) {
                $mahasiswa->$field = $request->status;
                $mahasiswa->save();

                activity_log('update_aktivasi', 'Admin mengubah '.$request->type.' mahasiswa: '.$mahasiswa->nama.' menjadi '.$request->status);

                return response()->json([
                    'success' => true,
                    'message' => ucfirst($request->type).' berhasil diperbarui.',
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Field tidak valid.']);
        } catch (\Exception $e) {
            \Log::error('Error: '.$e->getMessage());

            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan.']);
        }
    }

    public function uapIndex(Request $request)
    {
        $query = $this->uapMahasiswaQuery($request);
        $mahasiswa = $query->paginate(15)->appends($request->query());

        $total = Mahasiswa::query()
            ->where('status_mhs', 'aktif')
            ->where('jurusan_id', self::KEBIDANAN_JURUSAN_ID)
            ->where('semester', 6)
            ->count();
        $totalAktif = Mahasiswa::query()
            ->where('status_mhs', 'aktif')
            ->where('jurusan_id', self::KEBIDANAN_JURUSAN_ID)
            ->where('semester', 6)
            ->where('status_uap', 1)
            ->count();

        return view('admin.kemahasiswaan.aktivasi-uap.index', [
            'mahasiswa' => $mahasiswa,
            'total' => $total,
            'totalAktif' => $totalAktif,
            'totalNonaktif' => $total - $totalAktif,
        ]);
    }

    public function updateUapStatus(Request $request)
    {
        $validated = $request->validate([
            'mahasiswa_id' => 'required|integer',
            'status' => 'required|boolean',
        ]);

        $mahasiswa = Mahasiswa::query()
            ->where('status_mhs', 'aktif')
            ->where('jurusan_id', self::KEBIDANAN_JURUSAN_ID)
            ->where('semester', 6)
            ->findOrFail($validated['mahasiswa_id']);

        $mahasiswa->status_uap = (int) $validated['status'];
        $mahasiswa->save();

        activity_log(
            'update_aktivasi_uap',
            'Admin mengubah aktivasi UAP mahasiswa Kebidanan: '.$mahasiswa->nama.' menjadi '.$validated['status']
        );

        return response()->json([
            'success' => true,
            'message' => 'Status UAP '.$mahasiswa->nama.' berhasil diperbarui.',
        ]);
    }

    public function bulkUpdateUapStatus(Request $request)
    {
        $validated = $request->validate([
            'status' => 'required|boolean',
            'kelas' => 'nullable|in:pagi,karyawan',
            'search' => 'nullable|string|max:100',
        ]);

        $query = $this->uapMahasiswaQuery($request);
        $affected = $query->update([
            'status_uap' => (int) $validated['status'],
            'updated_at' => now(),
        ]);

        activity_log(
            'bulk_update_aktivasi_uap',
            'Admin mengubah aktivasi UAP untuk '.$affected.' mahasiswa Kebidanan menjadi '.$validated['status']
        );

        return response()->json([
            'success' => true,
            'message' => "Status UAP berhasil diperbarui untuk {$affected} mahasiswa Kebidanan.",
        ]);
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
                'status_akhir' => 0,
            ]);

            activity_log('reset_semua_status', 'Admin mereset semua status aktivasi mahasiswa');

            return response()->json([
                'success' => true,
                'message' => 'Semua status berhasil direset.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Error: '.$e->getMessage());

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
            $field = 'status_'.$type;

            $validFields = ['status_krs', 'status_uts', 'status_uas', 'status_nilai_uts', 'status_nilai_uas', 'status_akhir'];

            if (! in_array($field, $validFields)) {
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

            activity_log('bulk_update_aktivasi', 'Admin bulk update '.$type.' untuk '.$affected.' mahasiswa');

            return response()->json([
                'success' => true,
                'message' => ucfirst($type)." berhasil diperbarui untuk {$affected} mahasiswa.",
            ]);
        } catch (\Exception $e) {
            \Log::error('Error bulk update: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat bulk update.',
            ]);
        }
    }

    private function uapMahasiswaQuery(Request $request): Builder
    {
        return Mahasiswa::query()
            ->with('programStudi')
            ->where('status_mhs', 'aktif')
            ->where('jurusan_id', self::KEBIDANAN_JURUSAN_ID)
            ->where('semester', 6)
            ->when($request->filled('kelas'), fn (Builder $query) => $query->where('kelas', $request->input('kelas')))
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function (Builder $studentQuery) use ($search) {
                    $studentQuery->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nim', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw('CAST(semester AS UNSIGNED) ASC')
            ->orderBy('nama');
    }
}
