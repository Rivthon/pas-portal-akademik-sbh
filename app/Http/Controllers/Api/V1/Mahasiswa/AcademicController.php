<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\KrsGuidanceMessage;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\PengajuanTranskrip;
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

    public function krsDiscussion(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $mahasiswa->loadMissing('dosen');
        $ta = $this->activeAcademicYear();

        if (! $ta) {
            return response()->json([
                'tahun_akademik' => null,
                'dosen_pembimbing' => $mahasiswa->dosen?->nama,
                'tersedia' => false,
                'terkunci' => true,
                'pesan_status' => 'Tahun akademik aktif belum ditentukan.',
                'pesan' => [],
            ]);
        }

        $locked = $this->isKrsDiscussionLocked($mahasiswa, (int) $ta->ta_id);
        $messages = KrsGuidanceMessage::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->oldest()
            ->get();

        return response()->json([
            'tahun_akademik' => $this->academicYearPayload($ta),
            'dosen_pembimbing' => $mahasiswa->dosen?->nama,
            'tersedia' => (bool) $mahasiswa->dosen_id,
            'terkunci' => $locked,
            'pesan_status' => match (true) {
                ! $mahasiswa->dosen_id => 'Dosen Pembimbing Akademik belum ditentukan.',
                $locked => 'Diskusi KRS ditutup karena KRS telah disetujui. Forum akan aktif kembali jika ACC dibatalkan.',
                default => null,
            },
            'pesan' => $messages->map(fn (KrsGuidanceMessage $message) => [
                'id' => (int) $message->id,
                'pengirim' => $message->sender_type,
                'milik_saya' => $message->sender_type === 'mahasiswa',
                'label_pengirim' => $message->sender_type === 'mahasiswa' ? 'Anda' : 'Dosen Pembimbing',
                'isi' => $message->message,
                'dikirim_pada' => $message->created_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function sendKrsDiscussion(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        if (! $mahasiswa->dosen_id) {
            return response()->json([
                'message' => 'Dosen Pembimbing Akademik belum ditentukan.',
            ], 422);
        }
        if (! $ta) {
            return response()->json(['message' => 'Tahun akademik aktif belum ditentukan.'], 422);
        }
        if ($this->isKrsDiscussionLocked($mahasiswa, (int) $ta->ta_id)) {
            return response()->json([
                'message' => 'Diskusi KRS sudah ditutup karena KRS telah disetujui.',
            ], 422);
        }

        $validated = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
        ], [
            'message.required' => 'Umpan balik tidak boleh kosong.',
            'message.max' => 'Umpan balik maksimal 2.000 karakter.',
        ]);

        KrsGuidanceMessage::create([
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'dosen_id' => $mahasiswa->dosen_id,
            'ta_id' => $ta->ta_id,
            'sender_type' => 'mahasiswa',
            'message' => trim($validated['message']),
        ]);

        activity_log(
            'balas_bimbingan_krs_mobile',
            'Mahasiswa mengirim umpan balik KRS melalui aplikasi Android'
        );

        return response()->json(['message' => 'Umpan balik berhasil dikirim kepada Dosen Pembimbing.']);
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

    public function grades(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $ta = $this->activeAcademicYear();

        $currentItems = collect();
        if ($ta) {
            $currentItems = Krs::query()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereHas('kurikulum.mataKuliah')
                ->with('kurikulum.mataKuliah')
                ->get()
                ->unique('kurikulum_id')
                ->sortBy(fn (Krs $item) => $item->kurikulum?->mataKuliah?->nama)
                ->values();
        }

        $historyItems = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->when($ta, fn ($query) => $query->where('ta_id', '!=', $ta->ta_id))
            ->whereHas('kurikulum.mataKuliah')
            ->where(function ($query) {
                $query->whereNotNull('uts')->orWhereNotNull('uas');
            })
            ->with(['kurikulum.mataKuliah', 'tahunAjaran'])
            ->get()
            ->filter(fn (Krs $item) => filled($item->uts) || filled($item->uas))
            ->unique(fn (Krs $item) => $item->ta_id.'-'.$item->kurikulum_id)
            ->groupBy('ta_id')
            ->sortKeysDesc();

        $edomCompletionByAcademicYear = [];
        $published = $this->publishedKhs($mahasiswa)
            ->filter(function (Krs $item) use ($mahasiswa, $ta, &$edomCompletionByAcademicYear) {
                if ($ta && (int) $item->ta_id === (int) $ta->ta_id
                    && (int) $mahasiswa->status_akhir !== 1) {
                    return false;
                }

                $taId = (int) $item->ta_id;
                $edomCompletionByAcademicYear[$taId] ??= $this->edomCompletion
                    ->status($mahasiswa, $taId)['complete'];

                return $edomCompletionByAcademicYear[$taId];
            })
            ->sortBy(fn (Krs $item) => sprintf(
                '%010d|%s',
                (int) $item->ta_id,
                strtolower((string) $item->kurikulum?->mataKuliah?->nama)
            ))
            ->values();
        [$transcriptCredits, $transcriptWeight] = $this->totals($published);
        $ipk = round($transcriptCredits > 0 ? $transcriptWeight / $transcriptCredits : 0, 2);
        $latestTranscriptRequest = PengajuanTranskrip::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->latest('id')
            ->first();

        activity_log('lihat_nilai_mobile', 'Mahasiswa melihat manajemen nilai melalui aplikasi Android');

        return response()->json([
            'tahun_akademik' => $ta ? $this->academicYearPayload($ta) : null,
            'uts' => $this->currentExamPayload(
                $currentItems,
                (int) $mahasiswa->status_nilai_uts === 1,
                'uts',
                $ta
            ),
            'uas' => $this->currentExamPayload(
                $currentItems,
                (int) $mahasiswa->status_nilai_uas === 1,
                'uas',
                $ta
            ),
            'riwayat' => $historyItems->map(function (Collection $items) {
                $period = $items->first()?->tahunAjaran;

                return [
                    'tahun_akademik' => $period ? $this->academicYearPayload($period) : null,
                    'mata_kuliah' => $items
                        ->sortBy(fn (Krs $item) => $item->kurikulum?->mataKuliah?->nama)
                        ->map(fn (Krs $item) => $this->examCoursePayload($item))
                        ->values(),
                ];
            })->values(),
            'transkrip' => [
                'total_sks' => $transcriptCredits,
                'ipk' => $ipk,
                'predikat' => $this->transcriptPredicate($ipk),
                'mata_kuliah' => $published->map(fn (Krs $item) => [
                    ...$this->courseIdentityPayload($item),
                    'tahun_akademik' => $item->kurikulum?->tahunAjaran
                        ? $this->academicYearPayload($item->kurikulum->tahunAjaran)
                        : null,
                    'nilai_huruf' => $item->khs,
                    'bobot' => $this->gradeWeight($item->khs),
                ])->values(),
            ],
            'pengajuan_transkrip' => $this->transcriptRequestPayload($latestTranscriptRequest),
        ]);
    }

    public function submitTranscriptRequest(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        $validated = $request->validate([
            'jenis' => ['required', 'in:sementara'],
            'keperluan' => ['required', 'string', 'max:255'],
            'bukti' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'extensions:jpg,jpeg,png', 'max:2048'],
        ], [
            'jenis.required' => 'Jenis transkrip wajib dipilih.',
            'jenis.in' => 'Jenis transkrip tidak valid.',
            'keperluan.required' => 'Keperluan pengajuan wajib diisi.',
            'keperluan.max' => 'Keperluan maksimal 255 karakter.',
            'bukti.image' => 'Bukti harus berupa gambar JPG atau PNG.',
            'bukti.mimes' => 'Bukti harus berupa gambar JPG atau PNG.',
            'bukti.max' => 'Ukuran bukti maksimal 2 MB.',
        ]);

        $result = DB::transaction(function () use ($mahasiswa, $request, $validated) {
            Mahasiswa::query()->whereKey($mahasiswa->mahasiswa_id)->lockForUpdate()->firstOrFail();
            $latest = PengajuanTranskrip::query()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->latest('id')
                ->first();

            if ($latest && $latest->status !== 'ditolak') {
                return null;
            }

            return PengajuanTranskrip::create([
                'mahasiswa_id' => $mahasiswa->mahasiswa_id,
                'jenis' => $validated['jenis'],
                'keperluan' => trim($validated['keperluan']),
                'bukti' => $request->hasFile('bukti')
                    ? $request->file('bukti')->store('bukti_pengajuan', 'private')
                    : null,
                'status' => 'pending',
            ]);
        });

        if (! $result) {
            return response()->json([
                'message' => 'Masih ada pengajuan transkrip yang sedang berjalan atau sudah disetujui.',
            ], 422);
        }

        activity_log(
            'pengajuan_transkrip_mobile',
            'Mahasiswa mengajukan transkrip sementara melalui aplikasi Android'
        );

        return response()->json([
            'message' => 'Pengajuan transkrip berhasil dikirim.',
            'pengajuan' => $this->transcriptRequestPayload($result)['pengajuan_terakhir'],
        ], 201);
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

    private function currentExamPayload(
        Collection $items,
        bool $enabled,
        string $component,
        ?TahunAkademik $ta
    ): array {
        $label = strtoupper($component);

        return [
            'aktif' => $enabled && $ta !== null,
            'pesan' => match (true) {
                ! $ta => 'Tahun akademik aktif belum ditentukan.',
                ! $enabled => 'Nilai '.$label.' belum diaktifkan oleh BAUK.',
                default => null,
            },
            'mata_kuliah' => $enabled && $ta
                ? $items->map(fn (Krs $item) => [
                    ...$this->courseIdentityPayload($item),
                    'nilai' => $this->numericScore($item->{$component}),
                ])->values()
                : [],
        ];
    }

    private function examCoursePayload(Krs $item): array
    {
        return [
            ...$this->courseIdentityPayload($item),
            'uts' => $this->numericScore($item->uts),
            'uas' => $this->numericScore($item->uas),
        ];
    }

    private function courseIdentityPayload(Krs $item): array
    {
        return [
            'id' => (int) $item->krs_id,
            'kode' => $item->kurikulum?->mataKuliah?->matakuliah_id,
            'nama' => $item->kurikulum?->mataKuliah?->nama,
            'sks' => (int) ($item->kurikulum?->mataKuliah?->sks ?? 0),
            'semester' => (int) ($item->kurikulum?->mataKuliah?->smt ?? 0),
        ];
    }

    private function numericScore(mixed $score): ?float
    {
        $normalized = str_replace(',', '.', trim((string) $score));

        return $normalized !== '' && is_numeric($normalized) ? (float) $normalized : null;
    }

    private function transcriptPredicate(float $ipk): string
    {
        return match (true) {
            $ipk >= 3.51 => 'Dengan Pujian',
            $ipk >= 3.00 => 'Sangat Baik',
            $ipk >= 2.50 => 'Baik',
            $ipk >= 2.00 => 'Cukup',
            default => 'Kurang',
        };
    }

    private function transcriptRequestPayload(?PengajuanTranskrip $request): array
    {
        return [
            'dapat_mengajukan' => ! $request || $request->status === 'ditolak',
            'pengajuan_terakhir' => $request ? [
                'id' => (int) $request->id,
                'jenis' => $request->jenis,
                'keperluan' => $request->keperluan,
                'status' => $request->status,
                'catatan' => $request->catatan,
                'diajukan_pada' => $request->created_at?->toIso8601String(),
            ] : null,
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

    private function isKrsDiscussionLocked(Mahasiswa $mahasiswa, int $taId): bool
    {
        $krs = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId);

        return (clone $krs)->exists()
            && ! (clone $krs)->whereNull('disetujui_pada')->exists();
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
