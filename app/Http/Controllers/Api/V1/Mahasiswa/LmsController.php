<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\LmsMateri;
use App\Models\LmsPengumpulanTugas;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use App\Support\StoredUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LmsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $tahunAkademik = TahunAkademik::query()
            ->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);

        if (! $tahunAkademik) {
            return response()->json([
                'tahun_akademik' => null,
                'kelas' => [],
            ]);
        }

        $jadwalIds = KrsClassResolver::jadwalIdsForMahasiswa(
            $mahasiswa,
            (int) $tahunAkademik->ta_id
        );

        $jadwal = Jadwal::query()
            ->whereIn('id', $jadwalIds)
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->withCount([
                'pertemuan',
                'materi' => fn ($query) => $query->where('status', 1),
                'tugas' => fn ($query) => $query->where('aktif', true)
                    ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id),
                'quiz' => fn ($query) => $query->where('aktif', true),
            ])
            ->orderByRaw("FIELD(LOWER(hari), 'senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu')")
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (Jadwal $item) => $this->schedulePayload($item));

        return response()->json([
            'tahun_akademik' => [
                'id' => (int) $tahunAkademik->ta_id,
                'nama' => $tahunAkademik->nama,
                'periode' => $tahunAkademik->semester,
            ],
            'kelas' => $jadwal,
        ]);
    }

    public function show(Request $request, Jadwal $jadwal): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->authorizeSchedule($jadwal, $mahasiswa);

        $jadwal->load([
            'kurikulum.mataKuliah',
            'kurikulum.programStudi',
            'kurikulum.dosenToMatakuliah.dosen',
            'ruangan',
        ])->loadCount([
            'pertemuan',
            'materi' => fn ($query) => $query->where('status', 1),
            'tugas' => fn ($query) => $query->where('aktif', true)
                ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id),
            'quiz' => fn ($query) => $query->where('aktif', true),
        ]);

        $pertemuan = Pertemuan::query()
            ->where('jadwal_id', $jadwal->id)
            ->with([
                'dosen',
                'materi' => fn ($query) => $query
                    ->where('status', 1)
                    ->orderBy('created_at'),
                'tugas' => fn ($query) => $query
                    ->where('aktif', true)
                    ->visibleForMahasiswa((int) $mahasiswa->mahasiswa_id)
                    ->with(['pengumpulan' => fn ($submission) => $submission
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                    ->orderBy('deadline'),
                'quiz' => fn ($query) => $query
                    ->where('aktif', true)
                    ->with(['attempts' => fn ($attempt) => $attempt
                        ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)])
                    ->orderBy('deadline'),
            ])
            ->orderBy('tanggal_pertemuan')
            ->orderBy('pertemuan_id')
            ->get()
            ->values()
            ->map(function (Pertemuan $item, int $index) {
                return [
                    'id' => (int) $item->pertemuan_id,
                    'nomor' => $index + 1,
                    'tanggal' => $item->tanggal_pertemuan,
                    'topik' => $item->topik,
                    'sub_topik' => $item->sub_topik,
                    'metode_pbm' => $item->metode_pbm,
                    'jam_mulai' => $this->formatTime($item->jam_mulai),
                    'jam_selesai' => $this->formatTime($item->jam_selesai),
                    'dosen' => $item->dosen?->nama,
                    'materi' => $item->materi->map(fn (LmsMateri $materi) => [
                        'id' => (int) $materi->materi_id,
                        'judul' => $materi->judul,
                        'deskripsi' => $materi->deskripsi,
                        'tipe' => $materi->tipe,
                        'youtube_url' => $materi->youtube_url,
                        'punya_file' => StoredUpload::exists($materi->file),
                        'nama_file' => $materi->file ? basename($materi->file) : null,
                        'diunggah_pada' => $materi->created_at?->toIso8601String(),
                    ])->values(),
                    'tugas' => $item->tugas->map(function ($tugas) {
                        /** @var LmsPengumpulanTugas|null $submission */
                        $submission = $tugas->pengumpulan->first();

                        return [
                            'id' => (int) $tugas->tugas_id,
                            'judul' => $tugas->judul,
                            'deskripsi' => $tugas->deskripsi,
                            'tipe' => $tugas->tipe,
                            'deadline' => $tugas->deadline?->toIso8601String(),
                            'nilai_maksimal' => (float) $tugas->nilai_maksimal,
                            'status_pengumpulan' => $submission
                                ? ($submission->nilai !== null ? 'dinilai' : 'dikumpulkan')
                                : 'belum_dikumpulkan',
                            'nilai' => $submission?->nilai !== null
                                ? (float) $submission->nilai
                                : null,
                        ];
                    })->values(),
                    'quiz' => $item->quiz->map(function ($quiz) {
                        $attempt = $quiz->attempts->first();

                        return [
                            'id' => (int) $quiz->quiz_id,
                            'judul' => $quiz->judul,
                            'deskripsi' => $quiz->deskripsi,
                            'mulai_at' => $quiz->mulai_at?->toIso8601String(),
                            'deadline' => $quiz->deadline?->toIso8601String(),
                            'durasi_menit' => (int) $quiz->durasi_menit,
                            'status_attempt' => $attempt?->status ?? 'belum_mulai',
                            'nilai' => $attempt?->nilai_total !== null
                                ? (float) $attempt->nilai_total
                                : null,
                        ];
                    })->values(),
                ];
            });

        return response()->json([
            'kelas' => $this->schedulePayload($jadwal),
            'pertemuan' => $pertemuan,
        ]);
    }

    public function materialFile(Request $request, LmsMateri $materi): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $materi->loadMissing('jadwal');

        abort_unless($materi->status && $materi->jadwal, 404);
        $this->authorizeSchedule($materi->jadwal, $mahasiswa);
        abort_unless(StoredUpload::exists($materi->file), 404, 'File materi tidak ditemukan.');

        return StoredUpload::disk($materi->file)->download(
            $materi->file,
            basename($materi->file)
        );
    }

    private function authorizeSchedule(Jadwal $jadwal, Mahasiswa $mahasiswa): void
    {
        $tahunAkademik = TahunAkademik::query()
            ->where('status_ta', 1)
            ->value('ta_id');

        abort_unless(
            $tahunAkademik
            && (int) $jadwal->ta_id === (int) $tahunAkademik
            && KrsClassResolver::jadwalIdsForMahasiswa($mahasiswa, (int) $tahunAkademik)
                ->contains((int) $jadwal->id),
            403,
            'Anda tidak terdaftar pada kelas LMS ini.'
        );
    }

    private function schedulePayload(Jadwal $jadwal): array
    {
        $assignments = $jadwal->kurikulum?->dosenToMatakuliah ?? collect();
        $lecturers = $assignments
            ->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === 'teori'
                && KrsClassResolver::normalize($assignment->jenis_kelas)
                    === KrsClassResolver::normalize($jadwal->jenis_kelas))
            ->pluck('dosen.nama')
            ->filter()
            ->unique()
            ->values();

        return [
            'id' => (int) $jadwal->id,
            'kode' => $jadwal->kurikulum?->mataKuliah?->matakuliah_id,
            'nama' => $jadwal->kurikulum?->mataKuliah?->nama ?? '-',
            'sks' => (int) ($jadwal->kurikulum?->mataKuliah?->sks ?? 0),
            'semester' => (int) ($jadwal->kurikulum?->mataKuliah?->smt ?? 0),
            'program_studi' => $jadwal->kurikulum?->programStudi?->nama,
            'kelas' => jenis_kelas_label($jadwal->jenis_kelas),
            'hari' => ucfirst((string) $jadwal->hari),
            'jam_mulai' => $this->formatTime($jadwal->jam_mulai),
            'jam_selesai' => $this->formatTime($jadwal->jam_selesai),
            'ruangan' => $jadwal->ruangan?->nama,
            'dosen' => $lecturers,
            'jumlah_pertemuan' => (int) ($jadwal->pertemuan_count ?? 0),
            'jumlah_materi' => (int) ($jadwal->materi_count ?? 0),
            'jumlah_tugas' => (int) ($jadwal->tugas_count ?? 0),
            'jumlah_quiz' => (int) ($jadwal->quiz_count ?? 0),
        ];
    }

    private function formatTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
