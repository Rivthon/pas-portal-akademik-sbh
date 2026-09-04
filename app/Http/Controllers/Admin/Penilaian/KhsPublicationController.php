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
            'semesterReady'
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
        abort_if($target->contains(fn ($jadwal) => $jadwal->nilaiSubmission->status !== 'approved'), 422, 'Masih ada nilai yang belum disetujui Kaprodi.');

        $semester = $data['scope_type'] === 'semester' ? (int) $data['semester'] : null;
        $jadwalId = $data['scope_type'] === 'course' ? (int) $data['jadwal_id'] : null;
        $scopeKey = KhsPublication::scopeKey($data['scope_type'], $semester, $jadwalId);

        DB::transaction(function () use ($data, $target, $semester, $jadwalId, $scopeKey) {
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

        activity_log('terbitkan_khs', 'BAAK menerbitkan KHS '.$scopeKey.' prodi '.$data['program_studi_id'].' TA '.$data['ta_id']);

        return back()->with('success', 'KHS pada cakupan yang dipilih berhasil diterbitkan.');
    }

    public function revoke(KhsPublication $publication)
    {
        $publication->delete();

        return back()->with('success', 'Penerbitan KHS dibatalkan.');
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
