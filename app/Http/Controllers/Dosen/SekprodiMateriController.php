<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\LmsMateri;
use App\Models\TahunAkademik;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SekprodiMateriController extends Controller
{
    public function index(Request $request): View
    {
        $dosen = $request->user('dosen');
        $managedPrograms = $dosen->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        abort_if($managedPrograms->isEmpty(), 403, 'Akun Anda belum ditetapkan sebagai Sekprodi.');

        $managedIds = $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id);
        $programStudiId = (string) ($request->input('program_studi_id') ?: $managedPrograms->first()->jurusan_id);
        abort_unless($managedIds->contains($programStudiId), 403, 'Anda bukan Sekprodi program studi ini.');

        $activeTa = TahunAkademik::query()->where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);
        $search = trim((string) $request->input('search'));

        $materi = LmsMateri::query()
            ->with([
                'dosen:dosen_id,nama,nidn',
                'jadwal:id,ta_id,jurusan_id,kurikulum_id,jenis_kelas',
                'jadwal.kurikulum:kurikulum_id,matakuliah_id,jurusan_id,ta_id',
                'jadwal.kurikulum.mataKuliah',
                'jadwal.tahunAjaran',
                'pertemuan:pertemuan_id,tanggal_pertemuan,topik',
            ])
            ->whereHas('jadwal', function ($jadwal) use ($programStudiId, $taId, $request) {
                $jadwal->where('jurusan_id', $programStudiId)
                    ->when($taId, fn ($query) => $query->where('ta_id', $taId))
                    ->when($request->filled('jenis_kelas'), fn ($query) => $query
                        ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $request->jenis_kelas)]));
            })
            ->when($request->filled('dosen_id'), fn ($query) => $query->where('dosen_id', $request->dosen_id))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($filter) use ($search) {
                    $filter->where('judul', 'like', '%'.$search.'%')
                        ->orWhere('deskripsi', 'like', '%'.$search.'%')
                        ->orWhereHas('dosen', fn ($dosen) => $dosen->where('nama', 'like', '%'.$search.'%'))
                        ->orWhereHas('jadwal.kurikulum.mataKuliah', fn ($mataKuliah) => $mataKuliah
                            ->where('nama', 'like', '%'.$search.'%')
                            ->orWhere('matakuliah_id', 'like', '%'.$search.'%'));
                });
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $dosenPengunggah = LmsMateri::query()
            ->select('dosen_id')
            ->with('dosen:dosen_id,nama')
            ->whereHas('jadwal', fn ($jadwal) => $jadwal
                ->where('jurusan_id', $programStudiId)
                ->when($taId, fn ($query) => $query->where('ta_id', $taId)))
            ->distinct()
            ->get()
            ->pluck('dosen')
            ->filter()
            ->sortBy('nama')
            ->values();

        return view('dosen.sekprodi.materi', [
            'materi' => $materi,
            'managedPrograms' => $managedPrograms,
            'selectedProgramId' => $programStudiId,
            'tahunAkademik' => TahunAkademik::query()->orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'dosenPengunggah' => $dosenPengunggah,
        ]);
    }

    public function download(Request $request, LmsMateri $materi): StreamedResponse
    {
        $materi->loadMissing('jadwal');
        $programStudiId = $materi->jadwal?->jurusan_id;

        abort_unless(
            $programStudiId && $request->user('dosen')->programStudiSebagaiSekprodi()
                ->where('program_studi.jurusan_id', $programStudiId)
                ->exists(),
            403,
            'Materi ini bukan milik program studi yang Anda kelola.'
        );
        abort_unless($materi->file && StoredUpload::exists($materi->file), 404, 'File materi tidak ditemukan.');

        activity_log('download_materi_sekprodi', 'Sekprodi mengunduh materi LMS ID '.$materi->materi_id);

        return StoredUpload::disk($materi->file)->download(
            $materi->file,
            $this->safeFilename($materi),
            ['Cache-Control' => 'private, no-store']
        );
    }

    private function safeFilename(LmsMateri $materi): string
    {
        $extension = pathinfo((string) $materi->file, PATHINFO_EXTENSION);
        $filename = Str::slug(Str::ascii($materi->judul ?: 'materi'), '_');

        return ($filename ?: 'materi').($extension ? '.'.$extension : '');
    }
}
