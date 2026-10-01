<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Evaluasi;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Setting;
use App\Models\TahunAkademik;
use App\Services\EdomCompletionService;
use App\Support\KrsClassResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EdomController extends Controller
{
    public function __construct(private readonly EdomCompletionService $completion) {}

    public function index(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $activeTa = TahunAkademik::query()->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);
        $requestedTaId = $request->integer('ta_id');
        $ta = $requestedTaId
            ? TahunAkademik::query()->find($requestedTaId, ['ta_id', 'nama', 'semester'])
            : $activeTa;

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'diaktifkan' => false,
                'dikonfirmasi' => false,
                'pesan' => 'Tahun akademik aktif belum ditentukan.',
                'progres' => $this->emptyProgress(),
                'mata_kuliah' => [],
            ]);
        }

        $isHistorical = $activeTa && (int) $ta->ta_id !== (int) $activeTa->ta_id;
        $krs = $this->approvedKrs($mahasiswa, (int) $ta->ta_id);
        if ($requestedTaId && $krs->isEmpty()) {
            abort(404, 'Data EDOM untuk tahun akademik tersebut tidak ditemukan.');
        }
        if ($isHistorical) {
            $krs = KhsPublication::filterPublishedKrs($krs, $mahasiswa)->values();
        }
        $enabled = $isHistorical ? $krs->isNotEmpty() : $this->enabled();
        $existingRatings = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('krs_id', $krs->pluck('krs_id'))
            ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()
            ->get()
            ->mapWithKeys(fn ($row) => [
                $this->key($row->dosen_id, $row->kurikulum_id, $row->jenis_dosen, $row->jenis_kelas) => true,
            ]);

        $courses = $krs->map(function (Krs $item) use ($existingRatings, $mahasiswa) {
            $studentClass = KrsClassResolver::forKrs($item, $mahasiswa);
            $lecturers = collect($item->kurikulum?->dosenToMatakuliah)
                ->filter(fn ($assignment) => $this->assignmentMatches(
                    $assignment->jenis_dosen,
                    $assignment->jenis_kelas,
                    $studentClass
                ))
                ->map(function ($assignment) use ($existingRatings, $item, $studentClass) {
                    $lecturerId = (int) ($assignment->dosen?->dosen_id ?? 0);
                    $type = strtolower(trim((string) $assignment->jenis_dosen));
                    $class = $this->normalizeClass($assignment->jenis_kelas ?: $studentClass);

                    return [
                        'id' => $lecturerId,
                        'nama' => $assignment->dosen?->nama ?? '-',
                        'jenis_dosen' => $type,
                        'jenis_dosen_label' => $type === 'praktik' ? 'Praktik' : 'Teori',
                        'jenis_kelas' => $class,
                        'sudah_diisi' => $existingRatings->has(
                            $this->key($lecturerId, $item->kurikulum_id, $type, $class)
                        ),
                    ];
                })
                ->filter(fn (array $lecturer) => $lecturer['id'] > 0)
                ->unique(fn (array $lecturer) => $lecturer['id'].'|'.$lecturer['jenis_dosen'].'|'.$lecturer['jenis_kelas'])
                ->values();

            return [
                'krs_id' => (int) $item->krs_id,
                'kurikulum_id' => (int) $item->kurikulum_id,
                'kode' => $item->kurikulum?->mataKuliah?->matakuliah_id,
                'nama' => $item->kurikulum?->mataKuliah?->nama ?? '-',
                'dosen' => $lecturers,
            ];
        })->filter(fn (array $course) => collect($course['dosen'])->isNotEmpty())->values();

        $progress = $this->completion->status($mahasiswa, (int) $ta->ta_id);
        $confirmed = $progress['complete']
            && ($isHistorical || (int) $mahasiswa->status_edom === 1);

        return response()->json([
            'tahun_akademik' => $this->academicYear($ta),
            'riwayat' => $isHistorical,
            'diaktifkan' => $enabled,
            'dikonfirmasi' => $confirmed,
            'dapat_konfirmasi' => ! $isHistorical && $enabled && $progress['complete'] && ! $confirmed,
            'pesan' => match (true) {
                ! $enabled && $isHistorical => 'EDOM tahun akademik ini belum tersedia melalui penerbitan KHS BAAK.',
                ! $enabled => 'Pengisian EDOM tahun akademik aktif belum dibuka oleh admin.',
                $krs->isEmpty() => 'Belum ada KRS yang disetujui untuk pengisian EDOM.',
                $confirmed => 'EDOM telah selesai dan dikonfirmasi.',
                default => null,
            },
            'progres' => $progress,
            'mata_kuliah' => $courses,
        ]);
    }

    public function form(Request $request, Krs $krs, int $dosenId): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->ensurePeriodAccess($mahasiswa, $krs);
        [$assignment, $type, $class] = $this->validatedAssignment($request, $krs, $dosenId, $mahasiswa);

        if ($this->alreadySubmitted($mahasiswa, $krs, $dosenId, $type, $class)) {
            return response()->json(['message' => 'EDOM untuk dosen ini sudah pernah diisi dan tidak dapat diubah.'], 422);
        }

        return response()->json([
            'krs_id' => (int) $krs->krs_id,
            'dosen_id' => $dosenId,
            'mata_kuliah' => $krs->kurikulum?->mataKuliah?->nama ?? '-',
            'dosen' => $assignment->dosen_nama,
            'jenis_dosen' => $type,
            'jenis_dosen_label' => $type === 'praktik' ? 'Praktik' : 'Teori',
            'jenis_kelas' => $class,
            'pertanyaan' => Evaluasi::query()->orderBy('eval_id')->get(['eval_id', 'nama'])
                ->map(fn (Evaluasi $question) => [
                    'id' => (int) $question->eval_id,
                    'pertanyaan' => $question->nama,
                ])->values(),
        ]);
    }

    public function submit(Request $request, Krs $krs, int $dosenId): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->ensurePeriodAccess($mahasiswa, $krs);
        [, $type, $class] = $this->validatedAssignment($request, $krs, $dosenId, $mahasiswa);

        $validated = $request->validate([
            'responses' => ['required', 'array', 'min:1'],
            'responses.*' => ['required', 'integer', 'between:1,5'],
            'suggestion' => ['required', 'string', 'max:255'],
        ], [
            'responses.required' => 'Seluruh pertanyaan EDOM wajib dijawab.',
            'responses.*.between' => 'Nilai EDOM harus berada pada skala 1 sampai 5.',
            'suggestion.required' => 'Komentar atau saran wajib diisi.',
            'suggestion.max' => 'Komentar maksimal 255 karakter.',
        ]);

        $questionIds = Evaluasi::query()->orderBy('eval_id')->pluck('eval_id')
            ->map(fn ($id) => (int) $id)->values();
        $responseIds = collect(array_keys($validated['responses']))
            ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
            ->map(fn ($id) => (int) $id)->unique()->sort()->values();

        if ($questionIds->isEmpty() || $responseIds->all() !== $questionIds->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'responses' => 'Seluruh pertanyaan EDOM yang valid wajib dijawab.',
            ]);
        }

        DB::transaction(function () use ($validated, $mahasiswa, $krs, $dosenId, $type, $class) {
            Krs::query()->whereKey($krs->krs_id)->lockForUpdate()->firstOrFail();

            if ($this->alreadySubmitted($mahasiswa, $krs, $dosenId, $type, $class)) {
                throw ValidationException::withMessages([
                    'responses' => 'EDOM untuk dosen ini sudah pernah diisi dan tidak dapat diubah.',
                ]);
            }

            $now = now();
            DB::table('penilaian')->insert(collect($validated['responses'])
                ->map(fn ($score, $questionId) => [
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                    'dosen_id' => $dosenId,
                    'krs_id' => $krs->krs_id,
                    'kurikulum_id' => $krs->kurikulum_id,
                    'evaluasi_id' => (int) $questionId,
                    'nilai' => (int) $score,
                    'jenis_dosen' => $type,
                    'jenis_kelas' => $class,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->values()->all());

            DB::table('saran')->insert([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'dosen_id' => $dosenId,
                'krs_id' => $krs->krs_id,
                'kurikulum_id' => $krs->kurikulum_id,
                'saran' => trim($validated['suggestion']),
                'jenis_dosen' => $type,
                'jenis_kelas' => $class,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        activity_log('submit_edom_mobile', 'Mahasiswa mengisi EDOM melalui aplikasi Android');

        return response()->json([
            'message' => 'EDOM berhasil disimpan. Jawaban tidak dapat diubah setelah dikirim.',
            'progres' => $this->completion->status($mahasiswa, (int) $krs->ta_id),
        ]);
    }

    public function confirm(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $this->ensureEnabled();
        $ta = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $progress = $this->completion->status($mahasiswa, (int) $ta->ta_id);

        if (! $progress['complete']) {
            return response()->json([
                'message' => $progress['required'] === 0
                    ? 'Tidak ada data dosen untuk KRS tahun akademik aktif.'
                    : 'EDOM belum lengkap. Masih ada '.$progress['remaining'].' dosen yang belum dinilai.',
            ], 422);
        }

        $mahasiswa->update(['status_edom' => 1]);
        activity_log('konfirmasi_edom_mobile', 'Mahasiswa mengonfirmasi EDOM melalui aplikasi Android');

        return response()->json(['message' => 'EDOM berhasil dikonfirmasi. KHS dapat dibuka jika telah diterbitkan BAAK.']);
    }

    private function approvedKrs(Mahasiswa $mahasiswa, int $taId)
    {
        return Krs::query()->with([
            'kurikulum.mataKuliah',
            'kurikulum.dosenToMatakuliah.dosen',
        ])->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId)
            ->whereNotNull('disetujui_pada')
            ->get();
    }

    private function validatedAssignment(Request $request, Krs $krs, int $dosenId, Mahasiswa $mahasiswa): array
    {
        abort_unless(
            (int) $krs->mahasiswa_id === (int) $mahasiswa->mahasiswa_id
            && $krs->disetujui_pada !== null,
            403,
            'KRS tidak valid untuk pengisian EDOM.'
        );

        $type = strtolower(trim((string) $request->input('jenis_dosen')));
        abort_unless(in_array($type, ['teori', 'praktik'], true), 422, 'Jenis dosen tidak valid.');
        $studentClass = KrsClassResolver::forKrs($krs, $mahasiswa);
        $assignment = DB::table('dosen_mata_kuliah')
            ->join('dosen', 'dosen.dosen_id', '=', 'dosen_mata_kuliah.dosen_id')
            ->where('dosen_mata_kuliah.kurikulum_id', $krs->kurikulum_id)
            ->where('dosen_mata_kuliah.dosen_id', $dosenId)
            ->whereRaw('LOWER(dosen_mata_kuliah.jenis_dosen) = ?', [$type])
            ->where(function ($query) use ($studentClass, $type) {
                $query->whereRaw('LOWER(COALESCE(dosen_mata_kuliah.jenis_kelas, "")) = ?', [$studentClass]);
                if ($studentClass === 'reguler' && $type === 'praktik') {
                    $query->orWhereNull('dosen_mata_kuliah.jenis_kelas')
                        ->orWhere('dosen_mata_kuliah.jenis_kelas', '');
                }
            })
            ->first([
                'dosen_mata_kuliah.jenis_dosen',
                'dosen_mata_kuliah.jenis_kelas',
                'dosen.nama as dosen_nama',
            ]);
        abort_unless($assignment, 403, 'Dosen tidak sesuai dengan mata kuliah atau kelas mahasiswa.');

        return [$assignment, $type, $this->normalizeClass($assignment->jenis_kelas ?: $studentClass)];
    }

    private function alreadySubmitted(Mahasiswa $mahasiswa, Krs $krs, int $dosenId, string $type, string $class): bool
    {
        return DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('krs_id', $krs->krs_id)
            ->where('dosen_id', $dosenId)
            ->where('kurikulum_id', $krs->kurikulum_id)
            ->where('jenis_dosen', $type)
            ->where('jenis_kelas', $class)
            ->exists();
    }

    private function assignmentMatches(?string $type, ?string $class, string $studentClass): bool
    {
        $type = strtolower(trim((string) $type));
        $class = strtolower(trim((string) $class));

        return $class === $studentClass
            || ($type === 'praktik' && $studentClass === 'reguler' && $class === '');
    }

    private function key($dosenId, $curriculumId, ?string $type, ?string $class): string
    {
        return implode('|', [
            (int) $dosenId,
            (int) $curriculumId,
            strtolower(trim((string) $type)),
            $this->normalizeClass($class),
        ]);
    }

    private function normalizeClass(?string $class): string
    {
        return match (strtolower(trim((string) $class))) {
            'karyawan', 'reguler b' => 'karyawan',
            default => 'reguler',
        };
    }

    private function enabled(): bool
    {
        return (bool) (Setting::query()->value('edom_enabled') ?? true);
    }

    private function ensureEnabled(): void
    {
        abort_unless($this->enabled(), 403, 'Pengisian EDOM tahun akademik aktif belum dibuka oleh admin.');
    }

    private function ensurePeriodAccess(Mahasiswa $mahasiswa, Krs $krs): void
    {
        $activeTaId = (int) (TahunAkademik::query()->where('status_ta', 1)->value('ta_id') ?? 0);

        if ((int) $krs->ta_id === $activeTaId) {
            $this->ensureEnabled();

            return;
        }

        $krs->loadMissing('kurikulum.mataKuliah');
        abort_unless(
            (int) $krs->mahasiswa_id === (int) $mahasiswa->mahasiswa_id
            && KhsPublication::filterPublishedKrs(collect([$krs]), $mahasiswa)->isNotEmpty(),
            403,
            'EDOM tahun akademik tersebut belum tersedia melalui penerbitan KHS BAAK.'
        );
    }

    private function academicYear(TahunAkademik $ta): array
    {
        return ['id' => (int) $ta->ta_id, 'nama' => $ta->nama, 'periode' => $ta->semester];
    }

    private function emptyProgress(): array
    {
        return ['required' => 0, 'filled' => 0, 'remaining' => 0, 'complete' => false];
    }
}
