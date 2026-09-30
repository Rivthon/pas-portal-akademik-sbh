<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Services\EdomCompletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AcademicController extends Controller
{
    public function __construct(private readonly EdomCompletionService $edomCompletion) {}

    public function krs(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $mahasiswa->loadMissing('dosen');
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'status' => 'tidak_tersedia',
                'pesan' => 'Tahun akademik aktif belum ditentukan.',
                'dosen_pembimbing' => $mahasiswa->dosen?->nama,
                'total_sks' => 0,
                'mata_kuliah' => [],
            ]);
        }

        $items = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->whereHas('kurikulum.mataKuliah')
            ->with(['kurikulum.mataKuliah', 'disetujuiOleh'])
            ->get();

        $approvedCount = $items->whereNotNull('disetujui_pada')->count();
        $status = match (true) {
            $items->isEmpty() => 'belum_diambil',
            $approvedCount === $items->count() => 'disetujui',
            $approvedCount > 0 => 'sebagian_disetujui',
            default => 'menunggu_acc',
        };
        $approval = $items->sortByDesc('disetujui_pada')->firstWhere('disetujui_pada', '!=', null);

        return response()->json([
            'tahun_akademik' => $this->academicYearPayload($ta),
            'pengisian_diaktifkan' => (int) $mahasiswa->status_krs === 1,
            'dapat_diubah' => (int) $mahasiswa->status_krs === 1
                && $items->isNotEmpty()
                && $approvedCount === 0,
            'dapat_mengambil' => (int) $mahasiswa->status_krs === 1
                && $items->isEmpty(),
            'status' => $status,
            'pesan' => $this->krsMessage($status),
            'dosen_pembimbing' => $mahasiswa->dosen?->nama,
            'disetujui_oleh' => $approval?->disetujuiOleh?->nama,
            'disetujui_pada' => $approval?->disetujui_pada?->toIso8601String(),
            'total_sks' => (int) $items->sum(fn (Krs $item) => $item->kurikulum?->mataKuliah?->sks ?? 0),
            'mata_kuliah' => $items->map(fn (Krs $item) => [
                'id' => (int) $item->krs_id,
                'kode' => $item->kurikulum?->mataKuliah?->matakuliah_id,
                'nama' => $item->kurikulum?->mataKuliah?->nama,
                'sks' => (int) ($item->kurikulum?->mataKuliah?->sks ?? 0),
                'semester' => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0),
                'kategori' => $item->kurikulum?->mataKuliah?->kategori_mk,
                'disetujui' => $item->disetujui_pada !== null,
            ])->values(),
        ]);
    }

    public function krsOptions(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'pengisian_diaktifkan' => false,
                'dapat_diubah' => false,
                'mode' => 'ambil',
                'pesan' => 'Tahun akademik aktif belum ditentukan.',
                'mata_kuliah' => [],
            ]);
        }

        $existing = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->get();
        $enabled = (int) $mahasiswa->status_krs === 1;
        $editable = $enabled && ! $existing->contains(
            fn (Krs $item) => $item->disetujui_pada !== null
        );

        $options = collect();
        if ($editable) {
            $options = Kurikulum::query()
                ->where('ta_id', $ta->ta_id)
                ->where('jurusan_id', $mahasiswa->jurusan_id)
                ->whereHas('mataKuliah', fn ($query) => $query
                    ->where('smt', $mahasiswa->semester))
                ->with('mataKuliah')
                ->get()
                ->unique('matakuliah_id')
                ->values();
        }

        $selectedCourseIds = $existing->pluck('matakuliah_id')
            ->filter()
            ->map(fn ($id) => (string) $id);

        return response()->json([
            'tahun_akademik' => $this->academicYearPayload($ta),
            'pengisian_diaktifkan' => $enabled,
            'dapat_diubah' => $editable,
            'mode' => $existing->isEmpty() ? 'ambil' : 'edit',
            'pesan' => match (true) {
                ! $enabled => 'Pengisian KRS belum diaktifkan oleh Admin/BAAK.',
                ! $editable => 'KRS sudah disetujui Dosen Pembimbing dan tidak dapat diubah.',
                $options->isEmpty() => 'Tidak ada mata kuliah tersedia untuk semester ini.',
                default => null,
            },
            'mata_kuliah' => $options->map(function (Kurikulum $item) use ($selectedCourseIds) {
                $category = (int) ($item->mataKuliah?->kategori_mk ?? -1);

                return [
                    'kurikulum_id' => (int) $item->kurikulum_id,
                    'kode' => $item->mataKuliah?->matakuliah_id,
                    'nama' => $item->mataKuliah?->nama,
                    'sks' => (int) ($item->mataKuliah?->sks ?? 0),
                    'semester' => (int) ($item->mataKuliah?->smt ?? 0),
                    'kategori' => $category,
                    'kategori_label' => match ($category) {
                        0 => 'Wajib',
                        1 => 'Pilihan',
                        default => 'Belum Dikategorikan',
                    },
                    'terpilih' => $selectedCourseIds->contains((string) $item->matakuliah_id),
                ];
            })->values(),
        ]);
    }

    public function saveKrs(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json(['message' => 'Tahun akademik aktif belum ditentukan.'], 422);
        }
        if ((int) $mahasiswa->status_krs !== 1) {
            return response()->json([
                'message' => 'Pengisian KRS belum diaktifkan oleh Admin/BAAK.',
            ], 403);
        }

        $validated = $request->validate([
            'kurikulum_ids' => ['required', 'array', 'min:1'],
            'kurikulum_ids.*' => ['required', 'integer', 'distinct', 'exists:kurikulum,kurikulum_id'],
        ], [
            'kurikulum_ids.required' => 'Pilih minimal satu mata kuliah.',
            'kurikulum_ids.min' => 'Pilih minimal satu mata kuliah.',
        ]);

        $selectedIds = array_values(array_unique($validated['kurikulum_ids']));
        $curriculum = Kurikulum::query()
            ->whereIn('kurikulum_id', $selectedIds)
            ->where('ta_id', $ta->ta_id)
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->whereHas('mataKuliah', fn ($query) => $query
                ->where('smt', $mahasiswa->semester))
            ->with('mataKuliah')
            ->get();

        if ($curriculum->count() !== count($selectedIds)) {
            return response()->json([
                'message' => 'Terdapat mata kuliah yang tidak sesuai dengan prodi, semester, atau tahun akademik aktif.',
            ], 422);
        }
        if ($curriculum->pluck('matakuliah_id')->duplicates()->isNotEmpty()) {
            return response()->json([
                'message' => 'Mata kuliah yang sama tidak boleh dipilih lebih dari satu kali.',
            ], 422);
        }

        $result = DB::transaction(function () use ($mahasiswa, $ta, $curriculum) {
            $currentStudent = Mahasiswa::query()
                ->whereKey($mahasiswa->mahasiswa_id)
                ->lockForUpdate()
                ->firstOrFail();
            $existing = Krs::query()
                ->with('kurikulum.mataKuliah')
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->lockForUpdate()
                ->get();

            if ((int) $currentStudent->status_krs !== 1) {
                return 'disabled';
            }
            if ($existing->contains(fn (Krs $item) => $item->disetujui_pada !== null)) {
                return 'approved';
            }

            $selectedCourseIds = $curriculum->pluck('matakuliah_id')
                ->map(fn ($id) => (string) $id);
            $editableExisting = $existing->filter(
                fn (Krs $item) => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0) === (int) $mahasiswa->semester
                    && (string) ($item->kurikulum?->jurusan_id ?? '') === (string) $mahasiswa->jurusan_id
            );
            $deleteIds = $editableExisting
                ->reject(fn (Krs $item) => $selectedCourseIds->contains((string) $item->matakuliah_id))
                ->pluck('krs_id');

            if ($deleteIds->isNotEmpty()) {
                Krs::whereIn('krs_id', $deleteIds)->delete();
            }

            $currentCourseIds = $existing->pluck('matakuliah_id')
                ->filter()
                ->map(fn ($id) => (string) $id);
            $added = 0;
            foreach ($curriculum as $item) {
                if ($currentCourseIds->contains((string) $item->matakuliah_id)) {
                    continue;
                }

                Krs::create([
                    'kurikulum_id' => $item->kurikulum_id,
                    'matakuliah_id' => $item->matakuliah_id,
                    'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                    'ta_id' => $ta->ta_id,
                ]);
                $added++;
            }

            return ['ditambahkan' => $added, 'dihapus' => $deleteIds->count()];
        });

        if ($result === 'disabled') {
            return response()->json([
                'message' => 'Pengisian KRS baru saja dinonaktifkan oleh Admin/BAAK.',
            ], 403);
        }
        if ($result === 'approved') {
            return response()->json([
                'message' => 'KRS sudah disetujui Dosen Pembimbing sehingga perubahan dibatalkan.',
            ], 422);
        }

        activity_log(
            'ubah_krs_mobile',
            'Mahasiswa menyimpan KRS dari aplikasi: '.$result['ditambahkan'].' ditambahkan dan '.$result['dihapus'].' dihapus'
        );

        return response()->json([
            'message' => 'KRS berhasil disimpan dan menunggu ACC Dosen Pembimbing.',
            'ringkasan' => $result,
        ]);
    }

    public function khs(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json($this->lockedKhsPayload(null, 'Tahun akademik aktif belum ditentukan.'));
        }

        if ((int) $mahasiswa->status_akhir !== 1) {
            return response()->json($this->lockedKhsPayload(
                $ta,
                'KHS semester aktif belum diaktifkan oleh BAAK.'
            ));
        }

        $edom = $this->edomCompletion->status($mahasiswa, (int) $ta->ta_id);
        if (! $edom['complete']) {
            return response()->json($this->lockedKhsPayload(
                $ta,
                'Selesaikan seluruh EDOM tahun akademik aktif sebelum melihat KHS.',
                $edom
            ));
        }

        return response()->json($this->khsPayload($mahasiswa, $ta, false, $edom));
    }

    public function khsHistory(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $activeTaId = $this->activeAcademicYear()?->ta_id;
        $published = $this->publishedKhs($mahasiswa);
        $taIds = $published->pluck('ta_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => ! $activeTaId || $id < (int) $activeTaId)
            ->unique();

        $periods = TahunAkademik::query()
            ->whereIn('ta_id', $taIds)
            ->orderByDesc('ta_id')
            ->get(['ta_id', 'nama', 'semester']);

        return response()->json([
            'riwayat' => $periods->map(function (TahunAkademik $ta) use ($mahasiswa) {
                $edom = $this->edomCompletion->status($mahasiswa, (int) $ta->ta_id);

                if (! $edom['complete']) {
                    return $this->lockedKhsPayload(
                        $ta,
                        'EDOM pada tahun akademik ini belum selesai.',
                        $edom
                    );
                }

                return $this->khsPayload($mahasiswa, $ta, true, $edom);
            })->values(),
        ]);
    }

    private function khsPayload(
        Mahasiswa $mahasiswa,
        TahunAkademik $ta,
        bool $historical,
        array $edom
    ): array {
        $allPublished = $this->publishedKhs($mahasiswa);
        $items = $allPublished->where('ta_id', (int) $ta->ta_id)->values();

        if ($items->isEmpty()) {
            return $this->lockedKhsPayload($ta, 'KHS belum diterbitkan oleh BAAK.', $edom);
        }

        [$semesterSks, $semesterWeight] = $this->totals($items);
        $cumulative = $allPublished->where('ta_id', '<=', (int) $ta->ta_id)->values();
        [$cumulativeSks, $cumulativeWeight] = $this->totals($cumulative);

        return [
            'tahun_akademik' => $this->academicYearPayload($ta),
            'riwayat' => $historical,
            'terkunci' => false,
            'pesan' => null,
            'edom' => $edom,
            'total_sks' => $semesterSks,
            'ips' => round($semesterSks > 0 ? $semesterWeight / $semesterSks : 0, 2),
            'ipk' => round($cumulativeSks > 0 ? $cumulativeWeight / $cumulativeSks : 0, 2),
            'mata_kuliah' => $items->map(fn (Krs $item) => [
                'id' => (int) $item->krs_id,
                'kode' => $item->kurikulum?->mataKuliah?->matakuliah_id,
                'nama' => $item->kurikulum?->mataKuliah?->nama,
                'sks' => (int) ($item->kurikulum?->mataKuliah?->sks ?? 0),
                'semester' => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0),
                'nilai_huruf' => $item->khs,
                'bobot' => $this->gradeWeight($item->khs),
            ])->values(),
        ];
    }

    private function lockedKhsPayload(?TahunAkademik $ta, string $message, ?array $edom = null): array
    {
        return [
            'tahun_akademik' => $ta ? $this->academicYearPayload($ta) : null,
            'riwayat' => false,
            'terkunci' => true,
            'pesan' => $message,
            'edom' => $edom,
            'total_sks' => 0,
            'ips' => null,
            'ipk' => null,
            'mata_kuliah' => [],
        ];
    }

    private function publishedKhs(Mahasiswa $mahasiswa): Collection
    {
        $items = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereNotNull('khs')
            ->whereHas('kurikulum.mataKuliah')
            ->with(['kurikulum.mataKuliah', 'kurikulum.tahunAjaran'])
            ->get();

        return KhsPublication::filterPublishedKrs($items, $mahasiswa);
    }

    private function totals(Collection $items): array
    {
        $sks = (int) $items->sum(fn (Krs $item) => $item->kurikulum?->mataKuliah?->sks ?? 0);
        $weight = (float) $items->sum(function (Krs $item) {
            $credits = (int) ($item->kurikulum?->mataKuliah?->sks ?? 0);

            return $credits * $this->gradeWeight($item->khs);
        });

        return [$sks, $weight];
    }

    private function gradeWeight(?string $grade): float
    {
        return match (strtoupper(trim((string) $grade))) {
            'A' => 4.00,
            'AB' => 3.75,
            'BA' => 3.50,
            'B' => 3.00,
            'BC' => 2.75,
            'C' => 2.00,
            'D' => 1.00,
            default => 0.00,
        };
    }

    private function activeAcademicYear(): ?TahunAkademik
    {
        return TahunAkademik::query()
            ->where('status_ta', 1)
            ->first(['ta_id', 'nama', 'semester']);
    }

    private function academicYearPayload(TahunAkademik $ta): array
    {
        return [
            'id' => (int) $ta->ta_id,
            'nama' => $ta->nama,
            'periode' => $ta->semester,
        ];
    }

    private function krsMessage(string $status): string
    {
        return match ($status) {
            'disetujui' => 'KRS telah disetujui Dosen Pembimbing.',
            'sebagian_disetujui' => 'Sebagian mata kuliah masih menunggu ACC Dosen Pembimbing.',
            'menunggu_acc' => 'KRS sedang menunggu ACC Dosen Pembimbing.',
            'belum_diambil' => 'KRS belum diambil.',
            default => 'Data KRS belum tersedia.',
        };
    }
}
