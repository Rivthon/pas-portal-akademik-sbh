<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\LmsMateri;
use App\Models\LmsQuiz;
use App\Models\LmsTugas;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $tahunAkademik = TahunAkademik::query()
            ->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);

        $jadwalIds = $tahunAkademik
            ? KrsClassResolver::jadwalIdsForMahasiswa($mahasiswa, (int) $tahunAkademik->ta_id)
            : collect();

        $jumlahKrs = $tahunAkademik
            ? Krs::query()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $tahunAkademik->ta_id)
                ->count()
            : 0;

        return response()->json([
            'tahun_akademik' => $tahunAkademik ? [
                'id' => (int) $tahunAkademik->ta_id,
                'nama' => $tahunAkademik->nama,
                'periode' => $tahunAkademik->semester,
            ] : null,
            'ringkasan' => [
                'semester_mahasiswa' => (int) $mahasiswa->semester,
                'jumlah_krs' => $jumlahKrs,
                'jumlah_kelas_lms' => $jadwalIds->count(),
                'status_krs' => (bool) $mahasiswa->status_krs,
                'status_mahasiswa' => $mahasiswa->status_mhs,
            ],
            'pengumuman_lms' => $this->announcements($jadwalIds),
        ]);
    }

    private function announcements(Collection $jadwalIds): Collection
    {
        if ($jadwalIds->isEmpty()) {
            return collect();
        }

        $materi = LmsMateri::query()
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('status', 1)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (LmsMateri $item) => [
                'id' => (int) $item->materi_id,
                'jadwal_id' => (int) $item->jadwal_id,
                'pertemuan_id' => $item->pertemuan_id ? (int) $item->pertemuan_id : null,
                'jenis' => 'materi',
                'judul' => $item->judul,
                'mata_kuliah' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'waktu' => $item->created_at?->toIso8601String(),
                'deadline' => null,
            ]);

        $tugas = LmsTugas::query()
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (LmsTugas $item) => [
                'id' => (int) $item->tugas_id,
                'jadwal_id' => (int) $item->jadwal_id,
                'pertemuan_id' => $item->pertemuan_id ? (int) $item->pertemuan_id : null,
                'jenis' => 'tugas',
                'judul' => $item->judul,
                'mata_kuliah' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'waktu' => $item->created_at?->toIso8601String(),
                'deadline' => $item->deadline?->toIso8601String(),
            ]);

        $quiz = LmsQuiz::query()
            ->whereIn('jadwal_id', $jadwalIds)
            ->where('aktif', true)
            ->with('jadwal.kurikulum.mataKuliah')
            ->latest()
            ->limit(6)
            ->get()
            ->map(fn (LmsQuiz $item) => [
                'id' => (int) $item->quiz_id,
                'jadwal_id' => (int) $item->jadwal_id,
                'pertemuan_id' => $item->pertemuan_id ? (int) $item->pertemuan_id : null,
                'jenis' => 'quiz',
                'judul' => $item->judul,
                'mata_kuliah' => $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-',
                'waktu' => $item->created_at?->toIso8601String(),
                'deadline' => $item->deadline?->toIso8601String(),
            ]);

        return $materi
            ->concat($tugas)
            ->concat($quiz)
            ->sortByDesc('waktu')
            ->take(10)
            ->values();
    }
}
