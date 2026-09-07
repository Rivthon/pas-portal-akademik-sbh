<?php

namespace App\Http\Controllers\Admin\Penilaian;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\KhsPublication;
use App\Models\NilaiSubmission;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KhsPublicationController extends Controller
{
    public function index(Request $request)
    {
        $taId = $request->integer('ta_id') ?: TahunAkademik::where('status_ta', 1)->value('ta_id');
        $programStudi = ProgramStudi::orderBy('nama')->get()->map(function ($prodi) use ($taId) {
            $base = NilaiSubmission::where('program_studi_id', $prodi->jurusan_id)
                ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $taId));
            $prodi->submitted_count = (clone $base)->where('status', 'submitted')->count();
            $prodi->approved_count = (clone $base)->where('status', 'approved')->count();
            $prodi->temporary_count = (clone $base)->where('status', 'approved')
                ->whereNull('reviewed_by_dosen_id')
                ->where('review_note', 'like', '[PENERBITAN SEMENTARA BAAK]%')
                ->count();
            $prodi->revision_count = (clone $base)->where('status', 'revision')->count();
            $prodi->missing_count = $this->jadwalBerpeserta($taId, $prodi->jurusan_id)
                ->whereDoesntHave('nilaiSubmission')->count();
            $prodi->publication_count = KhsPublication::where('ta_id', $taId)
                ->where('program_studi_id', $prodi->jurusan_id)->count();

            return $prodi;
        });
        $tahunAjaran = TahunAkademik::orderByDesc('ta_id')->get();
        $selectedProdiId = (string) ($request->input('program_studi_id') ?: $programStudi->first()?->jurusan_id);
        $selectedProdi = $programStudi->firstWhere('jurusan_id', $selectedProdiId);
        $semester = $request->filled('semester') ? $request->integer('semester') : null;

        $allJadwal = collect();
        $jadwalList = collect();
        $semesterOptions = collect();
        $publications = collect();
        $globalPublication = null;
        $semesterPublication = null;
        $allReady = false;
        $semesterReady = false;
        $allPublishable = false;
        $semesterPublishable = false;

        if ($selectedProdi && $taId) {
            $allJadwal = $this->jadwalBerpeserta($taId, $selectedProdiId)
                ->with(['kurikulum.mataKuliah', 'nilaiSubmission'])
                ->orderBy('kurikulum_id')
                ->orderBy('jenis_kelas')
                ->get();
            $semesterOptions = $allJadwal->pluck('kurikulum.mataKuliah.smt')->filter()->map(fn ($value) => (int) $value)->unique()->sort()->values();
            if ($semester !== null && ! $semesterOptions->contains($semester)) {
                $semester = null;
            }
            $jadwalList = $semester === null
                ? $allJadwal
                : $allJadwal->filter(fn ($jadwal) => (int) $jadwal->kurikulum?->mataKuliah?->smt === $semester)->values();

            $publications = KhsPublication::where('ta_id', $taId)
                ->where('program_studi_id', $selectedProdiId)
                ->get();
            $globalPublication = $publications->firstWhere('scope_type', 'all');
            $semesterPublication = $semester === null ? null : $publications
                ->first(fn ($item) => $item->scope_type === 'semester' && (int) $item->semester === $semester);
            $allReady = $this->isReady($allJadwal);
            $semesterReady = $semester !== null && $this->isReady($jadwalList);
            $allPublishable = $this->isPublishable($allJadwal);
            $semesterPublishable = $semester !== null && $this->isPublishable($jadwalList);

            $jadwalList->each(function ($jadwal) use ($publications) {
                $semesterJadwal = (int) ($jadwal->kurikulum?->mataKuliah?->smt ?? 0);
                $jadwal->exact_publication = $publications
                    ->first(fn ($item) => $item->scope_type === 'course' && (int) $item->jadwal_id === (int) $jadwal->id);
                $jadwal->effective_publication = $publications->first(function ($item) use ($jadwal, $semesterJadwal) {
                    return $item->scope_type === 'all'
                        || ($item->scope_type === 'semester' && (int) $item->semester === $semesterJadwal)
                        || ($item->scope_type === 'course' && (int) $item->jadwal_id === (int) $jadwal->id);
                });
            });
        }

        return view('admin.nilai-publish.index', compact(
            'programStudi',
            'tahunAjaran',
            'taId',
            'selectedProdiId',
            'selectedProdi',
            'semester',
            'semesterOptions',
            'jadwalList',
            'globalPublication',
            'semesterPublication',
            'allReady',
            'semesterReady',
            'allPublishable',
            'semesterPublishable'
        ));
    }

    public function publish(Request $request)
    {
        $data = $request->validate([
            'ta_id' => ['required', 'exists:tahun_ajaran,ta_id'],
            'program_studi_id' => ['required', 'exists:program_studi,jurusan_id'],
            'scope_type' => ['required', Rule::in(['all', 'semester', 'course'])],
            'semester' => ['nullable', 'integer', 'min:1', 'max:14', 'required_if:scope_type,semester'],
            'jadwal_id' => ['nullable', 'integer', 'required_if:scope_type,course', 'exists:jadwal,id'],
        ]);

        $target = $this->targetJadwal($data)->with(['kurikulum.mataKuliah', 'nilaiSubmission'])->get();
        abort_if($target->isEmpty(), 422, 'Tidak ada kelas berpeserta pada cakupan yang dipilih.');
        abort_if($target->contains(fn ($jadwal) => ! $jadwal->nilaiSubmission), 422, 'Masih ada kelas yang nilainya belum diajukan dosen.');

        $temporaryApprovals = $target
            ->filter(fn ($jadwal) => $jadwal->nilaiSubmission->status !== 'approved')
            ->pluck('nilaiSubmission');

        $semester = $data['scope_type'] === 'semester' ? (int) $data['semester'] : null;
        $jadwalId = $data['scope_type'] === 'course' ? (int) $data['jadwal_id'] : null;
        $scopeKey = KhsPublication::scopeKey($data['scope_type'], $semester, $jadwalId);

        DB::transaction(function () use ($data, $target, $temporaryApprovals, $semester, $jadwalId, $scopeKey) {
            $temporaryApprovals->each(function (NilaiSubmission $submission) {
                $previousNote = trim((string) $submission->review_note);
                $submission->update([
                    'status' => 'approved',
                    'reviewed_by_dosen_id' => null,
                    'reviewed_at' => now(),
                    'review_note' => NilaiSubmission::TEMPORARY_BAAK_NOTE
                        .($previousNote !== '' ? ' Catatan sebelumnya: '.$previousNote : ''),
                ]);
            });

            $base = KhsPublication::where('ta_id', $data['ta_id'])
                ->where('program_studi_id', $data['program_studi_id']);

            if ($data['scope_type'] === 'all') {
                (clone $base)->delete();
            } elseif ($data['scope_type'] === 'semester') {
                (clone $base)->where(function ($query) use ($target, $scopeKey) {
                    $query->where('scope_key', $scopeKey)
                        ->orWhere(fn ($course) => $course->where('scope_type', 'course')->whereIn('jadwal_id', $target->pluck('id')));
                })->delete();
            }

            KhsPublication::updateOrCreate(
                [
                    'ta_id' => $data['ta_id'],
                    'program_studi_id' => $data['program_studi_id'],
                    'scope_key' => $scopeKey,
                ],
                [
                    'scope_type' => $data['scope_type'],
                    'semester' => $semester,
                    'jadwal_id' => $jadwalId,
                    'published_by_user_id' => auth()->id(),
                    'published_at' => now(),
                ]
            );
        });

        activity_log(
            'terbitkan_khs',
            'BAAK menerbitkan KHS '.$scopeKey.' prodi '.$data['program_studi_id'].' TA '.$data['ta_id']
                .($temporaryApprovals->isNotEmpty() ? ' dengan '.$temporaryApprovals->count().' persetujuan sementara' : '')
        );

        $message = $temporaryApprovals->isNotEmpty()
            ? 'KHS berhasil diterbitkan sementara. '.$temporaryApprovals->count().' pengajuan disahkan oleh BAAK tanpa menunggu verifikasi Kaprodi.'
            : 'KHS pada cakupan yang dipilih berhasil diterbitkan.';

        return back()->with('success', $message);
    }

    public function revoke(KhsPublication $publication)
    {
        $target = $this->targetJadwal([
            'ta_id' => $publication->ta_id,
            'program_studi_id' => $publication->program_studi_id,
            'scope_type' => $publication->scope_type,
            'semester' => $publication->semester,
            'jadwal_id' => $publication->jadwal_id,
        ])->with(['kurikulum.mataKuliah', 'nilaiSubmission'])->get();

        $restoredCount = DB::transaction(function () use ($publication, $target) {
            $publication->delete();
            $restored = 0;

            $target->each(function (Jadwal $jadwal) use (&$restored) {
                $submission = $jadwal->nilaiSubmission;
                if (! $submission?->isTemporaryBaakApproval() || KhsPublication::coversJadwal($jadwal)) {
                    return;
                }

                $submission->update([
                    'status' => 'submitted',
                    'reviewed_by_dosen_id' => null,
                    'reviewed_at' => null,
                    'review_note' => null,
                ]);
                $restored++;
            });

            return $restored;
        });

        $message = 'Penerbitan KHS dibatalkan.';
        if ($restoredCount > 0) {
            $message .= ' '.$restoredCount.' status persetujuan sementara dikembalikan menjadi menunggu verifikasi.';
        }

        return back()->with('success', $message);
    }

    private function targetJadwal(array $data): Builder
    {
        return $this->jadwalBerpeserta($data['ta_id'], $data['program_studi_id'])
            ->when($data['scope_type'] === 'semester', fn ($query) => $query->whereHas(
                'kurikulum.mataKuliah',
                fn ($mataKuliah) => $mataKuliah->where('smt', $data['semester'])
            ))
            ->when($data['scope_type'] === 'course', fn ($query) => $query->whereKey($data['jadwal_id']));
    }

    private function isReady(Collection $jadwal): bool
    {
        return $jadwal->isNotEmpty()
            && $jadwal->every(fn ($item) => $item->nilaiSubmission?->status === 'approved');
    }

    private function isPublishable(Collection $jadwal): bool
    {
        return $jadwal->isNotEmpty()
            && $jadwal->every(fn ($item) => $item->nilaiSubmission !== null);
    }

    private function jadwalBerpeserta($taId, $prodiId): Builder
    {
        return Jadwal::where('ta_id', $taId)->where('jurusan_id', $prodiId)->whereExists(function ($query) {
            $query->selectRaw('1')
                ->from('krs')
                ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
                ->whereColumn('krs.kurikulum_id', 'jadwal.kurikulum_id')
                ->whereColumn('krs.ta_id', 'jadwal.ta_id')
                ->where(function ($kelas) {
                    $kelas->where(function ($karyawan) {
                        $karyawan->whereRaw("LOWER(jadwal.jenis_kelas) = 'karyawan'")
                            ->whereRaw("LOWER(mahasiswa.kelas) = 'karyawan'");
                    })->orWhere(function ($reguler) {
                        $reguler->whereRaw("LOWER(jadwal.jenis_kelas) != 'karyawan'")
                            ->where(function ($mahasiswa) {
                                $mahasiswa->whereNull('mahasiswa.kelas')
                                    ->orWhereRaw("LOWER(mahasiswa.kelas) != 'karyawan'");
                            });
                    });
                });
        });
    }
}
