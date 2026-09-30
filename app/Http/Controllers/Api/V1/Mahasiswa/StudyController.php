<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Rps;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use App\Support\StoredUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudyController extends Controller
{
    public function schedules(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'teori' => [],
                'praktik' => [],
            ]);
        }

        $krsByCurriculum = $this->approvedKrs($mahasiswa, (int) $ta->ta_id)
            ->keyBy('kurikulum_id');
        if ($krsByCurriculum->isEmpty()) {
            return response()->json([
                'tahun_akademik' => $this->academicYearPayload($ta),
                'teori' => [],
                'praktik' => [],
            ]);
        }

        $theory = Jadwal::query()
            ->where('ta_id', $ta->ta_id)
            ->whereIn('kurikulum_id', $krsByCurriculum->keys())
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->get()
            ->filter(function (Jadwal $schedule) use ($krsByCurriculum, $mahasiswa) {
                $krs = $krsByCurriculum->get($schedule->kurikulum_id);

                return $krs && KrsClassResolver::matches($krs, $schedule, $mahasiswa);
            })
            ->map(fn (Jadwal $schedule) => $this->schedulePayload($schedule, 'teori'));

        $practice = JadwalPraktik::query()
            ->where('ta_id', $ta->ta_id)
            ->whereIn('kurikulum_id', $krsByCurriculum->keys())
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.dosenToMatakuliah.dosen',
                'ruangan',
            ])
            ->get()
            ->filter(function (JadwalPraktik $schedule) use ($krsByCurriculum, $mahasiswa) {
                $krs = $krsByCurriculum->get($schedule->kurikulum_id);

                return $krs
                    && KrsClassResolver::forKrs($krs, $mahasiswa)
                        === KrsClassResolver::normalize($schedule->jenis_kelas);
            })
            ->map(fn (JadwalPraktik $schedule) => $this->schedulePayload($schedule, 'praktik'));

        return response()->json([
            'tahun_akademik' => $this->academicYearPayload($ta),
            'teori' => $this->sortSchedules($theory),
            'praktik' => $this->sortSchedules($practice),
        ]);
    }

    public function rps(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'mata_kuliah' => [],
            ]);
        }

        $krsItems = $this->approvedKrs($mahasiswa, (int) $ta->ta_id)
            ->load(['kurikulum.mataKuliah', 'kurikulum.programStudi']);
        $rpsItems = Rps::query()
            ->whereIn('kurikulum_id', $krsItems->pluck('kurikulum_id'))
            ->with('dosen')
            ->get();

        return response()->json([
            'tahun_akademik' => $this->academicYearPayload($ta),
            'mata_kuliah' => $krsItems->map(function (Krs $krs) use ($mahasiswa, $rpsItems) {
                $class = KrsClassResolver::forKrs($krs, $mahasiswa);
                $rps = $rpsItems->first(fn (Rps $item) => (int) $item->kurikulum_id === (int) $krs->kurikulum_id
                    && KrsClassResolver::normalize($item->jenis_kelas) === $class);
                $available = $rps && StoredUpload::exists($rps->file);

                return [
                    'krs_id' => (int) $krs->krs_id,
                    'kurikulum_id' => (int) $krs->kurikulum_id,
                    'kode' => $krs->kurikulum?->mataKuliah?->matakuliah_id,
                    'nama' => $krs->kurikulum?->mataKuliah?->nama,
                    'sks' => (int) ($krs->kurikulum?->mataKuliah?->sks ?? 0),
                    'semester' => (int) ($krs->kurikulum?->mataKuliah?->smt ?? 0),
                    'program_studi' => $krs->kurikulum?->programStudi?->nama,
                    'kelas' => $this->classLabel($class),
                    'rps_id' => $rps ? (int) $rps->rps_id : null,
                    'tersedia' => (bool) $available,
                    'nama_file' => $available ? ($rps->nama_file ?: basename($rps->file)) : null,
                    'dosen_pengunggah' => $rps?->dosen?->nama,
                    'diunggah_pada' => $rps?->updated_at?->toIso8601String(),
                ];
            })->values(),
        ]);
    }

    public function rpsFile(Request $request, Rps $rps): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();
        $krs = $ta ? Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->where('kurikulum_id', $rps->kurikulum_id)
            ->whereNotNull('disetujui_pada')
            ->first() : null;

        abort_unless(
            $krs
                && KrsClassResolver::normalize($rps->jenis_kelas)
                    === KrsClassResolver::forKrs($krs, $mahasiswa),
            403,
            'Anda tidak terdaftar pada mata kuliah RPS ini.'
        );
        abort_unless(StoredUpload::exists($rps->file), 404, 'File RPS tidak ditemukan.');

        return response()->streamDownload(
            fn () => print file_get_contents(StoredUpload::absolutePath($rps->file)),
            $rps->nama_file ?: basename($rps->file),
            ['Content-Type' => 'application/pdf']
        );
    }

    private function schedulePayload(Jadwal|JadwalPraktik $schedule, string $type): array
    {
        $normalizedClass = KrsClassResolver::normalize($schedule->jenis_kelas);
        $lecturers = $schedule->kurikulum?->dosenToMatakuliah
            ?->filter(fn ($assignment) => strtolower((string) $assignment->jenis_dosen) === $type
                && KrsClassResolver::normalize($assignment->jenis_kelas) === $normalizedClass)
            ->pluck('dosen.nama')
            ->filter()
            ->unique()
            ->values() ?? collect();

        return [
            'id' => (int) $schedule->id,
            'jenis' => $type,
            'kode' => $schedule->kurikulum?->mataKuliah?->matakuliah_id,
            'nama' => $schedule->kurikulum?->mataKuliah?->nama,
            'sks' => (int) ($schedule->kurikulum?->mataKuliah?->sks ?? 0),
            'semester' => (int) ($schedule->kurikulum?->mataKuliah?->smt ?? 0),
            'kelas' => $this->classLabel($normalizedClass),
            'hari' => ucfirst(strtolower((string) $schedule->hari)),
            'jam_mulai' => $this->formatTime($schedule->jam_mulai),
            'jam_selesai' => $this->formatTime($schedule->jam_selesai),
            'ruangan' => $schedule->ruangan?->nama,
            'dosen' => $lecturers,
        ];
    }

    private function approvedKrs(Mahasiswa $mahasiswa, int $taId)
    {
        return Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId)
            ->whereNotNull('disetujui_pada')
            ->whereHas('kurikulum.mataKuliah')
            ->get();
    }

    private function sortSchedules($items)
    {
        $days = collect(['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);

        return $items->sortBy(fn (array $item) => sprintf(
            '%02d-%s',
            ($days->search(strtolower($item['hari'])) === false ? 99 : $days->search(strtolower($item['hari']))),
            $item['jam_mulai'] ?? '99:99'
        ))->values();
    }

    private function activeAcademicYear(): ?TahunAkademik
    {
        return TahunAkademik::query()
            ->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);
    }

    private function academicYearPayload(TahunAkademik $ta): array
    {
        return ['id' => (int) $ta->ta_id, 'nama' => $ta->nama, 'periode' => $ta->semester];
    }

    private function classLabel(string $class): string
    {
        return $class === 'karyawan' ? 'Reguler B' : 'Reguler A';
    }

    private function formatTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        return substr((string) $value, 0, 5);
    }
}
