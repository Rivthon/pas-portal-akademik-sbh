<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\DosenMatakuliah;
use App\Models\Rps;
use App\Models\TahunAkademik;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SekprodiRpsController extends Controller
{
    public function index(Request $request): View
    {
        $dosen = $request->user('dosen');
        $managedPrograms = $dosen->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        abort_if($managedPrograms->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Sekprodi.');

        $managedIds = $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id);
        $programStudiId = (string) ($request->input('program_studi_id') ?: $managedPrograms->first()->jurusan_id);
        abort_unless($managedIds->contains($programStudiId), 403, 'Anda bukan Sekprodi program studi ini.');

        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);
        $search = trim((string) $request->input('search'));

        $assignments = DosenMatakuliah::query()
            ->with(['dosen', 'kurikulum.mataKuliah', 'kurikulum.programStudi'])
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereHas('kurikulum', fn ($query) => $query
                ->where('jurusan_id', $programStudiId)
                ->when($taId, fn ($taQuery) => $taQuery->where('ta_id', $taId)))
            ->when($request->filled('jenis_kelas'), fn ($query) => $query
                ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $request->jenis_kelas)]))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->whereHas('kurikulum.mataKuliah', fn ($mataKuliah) => $mataKuliah
                        ->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('matakuliah_id', 'like', '%'.$search.'%'))
                        ->orWhereHas('dosen', fn ($dosen) => $dosen
                            ->where('nama', 'like', '%'.$search.'%'));
                });
            })
            ->orderBy('kurikulum_id')
            ->get();

        $rpsByClass = Rps::query()
            ->with('dosen')
            ->whereIn('kurikulum_id', $assignments->pluck('kurikulum_id')->unique())
            ->get()
            ->keyBy(fn ($rps) => $rps->kurikulum_id.'|'.strtolower((string) $rps->jenis_kelas));

        $mataKuliah = $assignments
            ->groupBy(fn ($item) => $item->kurikulum_id.'|'.strtolower((string) $item->jenis_kelas))
            ->map(function ($group, $key) use ($rpsByClass) {
                $item = $group->first();
                $item->setRelation('rpsSekprodi', $rpsByClass->get($key));
                $item->dosen_pengampu = $group->pluck('dosen.nama')->filter()->unique()->values();

                return $item;
            })
            ->values();

        return view('dosen.sekprodi.rps', [
            'mataKuliah' => $mataKuliah,
            'managedPrograms' => $managedPrograms,
            'selectedProgramId' => $programStudiId,
            'tahunAkademik' => TahunAkademik::orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
        ]);
    }

    public function download(Request $request, Rps $rps): StreamedResponse
    {
        $rps->loadMissing('kurikulum.programStudi');
        $programStudiId = $rps->kurikulum?->jurusan_id;

        abort_unless(
            $programStudiId && $request->user('dosen')->programStudiSebagaiSekprodi()
                ->where('program_studi.jurusan_id', $programStudiId)
                ->exists(),
            403,
            'RPS ini bukan milik program studi yang Anda kelola.'
        );
        abort_unless(StoredUpload::exists($rps->file), 404, 'File RPS tidak ditemukan.');

        activity_log('download_rps_sekprodi', 'Sekprodi mengunduh RPS ID '.$rps->rps_id);

        return StoredUpload::disk($rps->file)->download(
            $rps->file,
            $this->safeFilename($rps),
            ['Cache-Control' => 'private, no-store']
        );
    }

    private function safeFilename(Rps $rps): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '_', Str::ascii(basename(
            $rps->nama_file ?: $rps->file
        ))) ?: 'rps.pdf';
    }
}
