<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AttendanceController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $validated = $request->validate([
            'ta_id' => ['nullable', 'integer', 'exists:tahun_ajaran,ta_id'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],
        ]);

        $activeYear = TahunAkademik::query()->where('status_ta', 1)->first();
        $yearIds = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereNotNull('disetujui_pada')
            ->pluck('ta_id')
            ->when($activeYear, fn (Collection $ids) => $ids->push($activeYear->ta_id))
            ->filter()
            ->unique()
            ->values();
        $years = TahunAkademik::query()
            ->whereIn('ta_id', $yearIds)
            ->orderByDesc('ta_id')
            ->get(['ta_id', 'nama', 'semester']);

        if ($years->isEmpty()) {
            return response()->json($this->emptyPayload());
        }

        $selectedYearId = (int) ($validated['ta_id'] ?? $activeYear?->ta_id ?? $years->first()->ta_id);
        abort_unless($years->contains('ta_id', $selectedYearId), 403, 'Tahun akademik tidak tersedia untuk mahasiswa ini.');
        $selectedYear = $years->firstWhere('ta_id', $selectedYearId);

        $approvedKrs = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $selectedYearId)
            ->whereNotNull('disetujui_pada')
            ->whereHas('kurikulum.mataKuliah')
            ->with(['kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->get();
        $semesters = $approvedKrs
            ->map(fn (Krs $item) => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0))
            ->filter(fn (int $semester) => $semester >= 1 && $semester <= 14)
            ->unique()
            ->sort()
            ->values();
        if ($semesters->isEmpty()) {
            return response()->json([
                'tahun_akademik_tersedia' => $years->map(fn (TahunAkademik $year) => $this->academicYearPayload($year))->values(),
                'tahun_akademik' => $this->academicYearPayload($selectedYear),
                'semester_tersedia' => [],
                'semester' => null,
                'teori' => [],
                'praktik' => [],
            ]);
        }
        $studentSemester = max(1, min(14, (int) ($mahasiswa->semester ?: 1)));
        $selectedSemester = (int) ($validated['semester']
            ?? ($semesters->contains($studentSemester) ? $studentSemester : $semesters->first())
            ?? $studentSemester);
        abort_if(
            isset($validated['semester']) && ! $semesters->contains($selectedSemester),
            403,
            'Semester tidak tersedia pada KRS mahasiswa.'
        );

        $krsItems = $approvedKrs
            ->filter(fn (Krs $item) => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0) === $selectedSemester)
            ->keyBy('kurikulum_id');

        return response()->json([
            'tahun_akademik_tersedia' => $years->map(fn (TahunAkademik $year) => $this->academicYearPayload($year))->values(),
            'tahun_akademik' => $this->academicYearPayload($selectedYear),
            'semester_tersedia' => $semesters,
            'semester' => $selectedSemester,
            'teori' => $this->theoryCourses($mahasiswa, $selectedYearId, $krsItems),
            'praktik' => $this->practiceCourses($mahasiswa, $selectedYearId, $krsItems),
        ]);
    }

    private function theoryCourses(Mahasiswa $mahasiswa, int $yearId, Collection $krsItems): Collection
    {
        if ($krsItems->isEmpty()) {
            return collect();
        }

        $schedules = Jadwal::query()
            ->where('ta_id', $yearId)
            ->whereIn('kurikulum_id', $krsItems->keys())
            ->with([
                'pertemuan' => fn ($query) => $query
                    ->with([
                        'dosen',
                        'absensi' => fn ($attendance) => $attendance
                            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
                    ])
                    ->orderBy('tanggal_pertemuan')
                    ->orderBy('jam_mulai'),
            ])
            ->get()
            ->filter(function (Jadwal $schedule) use ($krsItems, $mahasiswa) {
                $krs = $krsItems->get($schedule->kurikulum_id);

                return $krs && KrsClassResolver::matches($krs, $schedule, $mahasiswa);
            })
            ->groupBy('kurikulum_id');

        return $this->coursePayloads($mahasiswa, $krsItems, $schedules, 'teori');
    }

    private function practiceCourses(Mahasiswa $mahasiswa, int $yearId, Collection $krsItems): Collection
    {
        if ($krsItems->isEmpty()) {
            return collect();
        }

        $schedules = JadwalPraktik::query()
            ->where('ta_id', $yearId)
            ->whereIn('kurikulum_id', $krsItems->keys())
            ->whereHas('kurikulum.dosenToMatakuliah', fn (Builder $query) => $query
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)'))
            ->with([
                'pertemuan' => fn ($query) => $query
                    ->with([
                        'dosen',
                        'absensi' => fn ($attendance) => $attendance
                            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id),
                    ])
                    ->orderBy('tanggal_pertemuan')
                    ->orderBy('jam_mulai'),
            ])
            ->get()
            ->filter(function (JadwalPraktik $schedule) use ($krsItems, $mahasiswa) {
                $krs = $krsItems->get($schedule->kurikulum_id);

                return $krs && KrsClassResolver::forKrs($krs, $mahasiswa)
                    === KrsClassResolver::normalize($schedule->jenis_kelas);
            })
            ->groupBy('kurikulum_id');

        return $this->coursePayloads($mahasiswa, $krsItems, $schedules, 'praktik');
    }

    private function coursePayloads(
        Mahasiswa $mahasiswa,
        Collection $krsItems,
        Collection $schedules,
        string $type
    ): Collection {
        return $schedules->map(function (Collection $courseSchedules, $curriculumId) use ($mahasiswa, $krsItems, $type) {
            /** @var Krs $krs */
            $krs = $krsItems->get($curriculumId);
            $meetings = $courseSchedules
                ->flatMap->pertemuan
                ->sortBy(fn ($meeting) => sprintf(
                    '%s-%s-%010d',
                    substr((string) $meeting->tanggal_pertemuan, 0, 10),
                    substr((string) $meeting->jam_mulai, 0, 5),
                    (int) $meeting->getKey()
                ))
                ->values()
                ->map(function ($meeting, int $index) {
                    $attendance = $meeting->absensi->first();
                    $status = $this->normalizeStatus($attendance?->status);

                    return [
                        'id' => (int) $meeting->getKey(),
                        'pertemuan_ke' => $index + 1,
                        'tanggal' => substr((string) $meeting->tanggal_pertemuan, 0, 10),
                        'jam_mulai' => $this->formatTime($meeting->jam_mulai),
                        'jam_selesai' => $this->formatTime($meeting->jam_selesai),
                        'topik' => $meeting->topik,
                        'sub_topik' => $meeting->sub_topik,
                        'metode_pbm' => $meeting->metode_pbm,
                        'dosen' => $meeting->dosen?->nama,
                        'status' => $status,
                        'status_label' => $this->statusLabel($status),
                        'keterangan' => $attendance?->keterangan,
                    ];
                });
            $summary = collect(['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'belum_diabsen' => 0]);
            foreach ($meetings as $meeting) {
                $summary[$meeting['status']] = (int) $summary[$meeting['status']] + 1;
            }
            $recorded = $summary['hadir'] + $summary['izin'] + $summary['sakit'] + $summary['alpha'];

            return [
                'kurikulum_id' => (int) $curriculumId,
                'jenis' => $type,
                'kode' => $krs->kurikulum?->mataKuliah?->matakuliah_id,
                'nama' => $krs->kurikulum?->mataKuliah?->nama,
                'sks' => (int) ($krs->kurikulum?->mataKuliah?->sks ?? 0),
                'semester' => (int) ($krs->kurikulum?->mataKuliah?->smt ?? 0),
                'program_studi' => $krs->kurikulum?->programStudi?->nama,
                'kelas' => $this->classLabel(KrsClassResolver::forKrs($krs, $mahasiswa)),
                'ringkasan' => $summary,
                'persentase' => $recorded > 0 ? round(($summary['hadir'] / $recorded) * 100) : 0,
                'pertemuan' => $meetings,
            ];
        })->sortBy('nama')->values();
    }

    private function normalizeStatus(?string $status): string
    {
        return match (strtolower(trim((string) $status))) {
            'hadir' => 'hadir',
            'izin' => 'izin',
            'sakit' => 'sakit',
            'alpha', 'alpa', 'tidak hadir' => 'alpha',
            default => 'belum_diabsen',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'hadir' => 'Hadir',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'alpha' => 'Alpha',
            default => 'Belum Diabsen',
        };
    }

    private function classLabel(string $class): string
    {
        return $class === 'karyawan' ? 'Reguler B' : 'Reguler A';
    }

    private function formatTime($value): ?string
    {
        return $value ? substr((string) $value, 0, 5) : null;
    }

    private function academicYearPayload(TahunAkademik $year): array
    {
        return ['id' => (int) $year->ta_id, 'nama' => $year->nama, 'periode' => $year->semester];
    }

    private function emptyPayload(): array
    {
        return [
            'tahun_akademik_tersedia' => [],
            'tahun_akademik' => null,
            'semester_tersedia' => [],
            'semester' => null,
            'teori' => [],
            'praktik' => [],
        ];
    }
}
