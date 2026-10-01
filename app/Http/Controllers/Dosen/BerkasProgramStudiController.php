<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\BerkasProgramStudi;
use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Support\StoredUpload;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasProgramStudiController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Dosen $dosen */
        $dosen = $request->user('dosen');
        $managedPrograms = $dosen->programStudiSebagaiSekprodi()->orderBy('nama')->get();
        $managedIds = $managedPrograms->pluck('jurusan_id')->map(fn ($id) => (string) $id);

        $berkas = BerkasProgramStudi::query()
            ->with(['programStudi', 'pengunggah'])
            ->where(function ($query) use ($dosen, $managedIds) {
                // Default-deny: dosen tanpa prodi dan tanpa penugasan Sekprodi tidak boleh melihat seluruh berkas.
                $query->whereRaw('1 = 0');

                if ($dosen->jurusan_id) {
                    $query->orWhere(function ($ownProgram) use ($dosen) {
                        $ownProgram->where('jurusan_id', $dosen->jurusan_id)
                            ->where('aktif', true)
                            ->whereIn('target', ['dosen', 'semua']);
                    });
                }

                if ($managedIds->isNotEmpty()) {
                    $query->orWhereIn('jurusan_id', $managedIds);
                }
            })
            ->latest()
            ->paginate(12);

        return view('dosen.berkas-program-studi.index', compact('berkas', 'managedPrograms'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var Dosen $dosen */
        $dosen = $request->user('dosen');
        $managedIds = $dosen->programStudiSebagaiSekprodi()->pluck('jurusan_id')->map(fn ($id) => (string) $id);
        abort_if($managedIds->isEmpty(), 403, 'Hanya Sekprodi yang dapat mengunggah berkas.');

        $data = $request->validate([
            'jurusan_id' => ['required', Rule::in($managedIds->all())],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'target' => ['required', Rule::in(['mahasiswa', 'dosen', 'semua'])],
            'berkas' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png', 'max:20480'],
        ], [
            'jurusan_id.in' => 'Anda bukan Sekprodi pada program studi tersebut.',
            'berkas.mimes' => 'Format berkas harus PDF, Word, Excel, PowerPoint, JPG, atau PNG.',
            'berkas.max' => 'Ukuran berkas maksimal 20 MB.',
        ]);

        $file = $request->file('berkas');
        $path = $file->store('berkas-program-studi/'.$data['jurusan_id'], 'private');

        try {
            DB::transaction(function () use ($data, $dosen, $file, $path) {
                BerkasProgramStudi::create([
                    'jurusan_id' => $data['jurusan_id'],
                    'uploaded_by_dosen_id' => $dosen->dosen_id,
                    'judul' => $data['judul'],
                    'deskripsi' => $data['deskripsi'] ?? null,
                    'target' => $data['target'],
                    'nama_file' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $file->getMimeType(),
                    'ukuran' => $file->getSize(),
                    'aktif' => true,
                ]);
            });
        } catch (\Throwable $exception) {
            StoredUpload::delete($path);
            throw $exception;
        }

        activity_log('unggah_berkas_prodi', 'Sekprodi mengunggah berkas: '.$data['judul']);

        return back()->with('success', 'Berkas Program Studi berhasil diunggah.');
    }

    public function toggle(Request $request, BerkasProgramStudi $berkas): RedirectResponse
    {
        $this->ensureManager($request, $berkas);
        $berkas->update(['aktif' => ! $berkas->aktif]);

        activity_log('ubah_status_berkas_prodi', 'Sekprodi mengubah status berkas: '.$berkas->judul);

        return back()->with('success', 'Status berkas berhasil diperbarui.');
    }

    public function destroy(Request $request, BerkasProgramStudi $berkas): RedirectResponse
    {
        $this->ensureManager($request, $berkas);
        $path = $berkas->path;
        $judul = $berkas->judul;
        $berkas->delete();
        StoredUpload::delete($path);

        activity_log('hapus_berkas_prodi', 'Sekprodi menghapus berkas: '.$judul);

        return back()->with('success', 'Berkas Program Studi berhasil dihapus.');
    }

    public function download(Request $request, BerkasProgramStudi $berkas): StreamedResponse
    {
        /** @var Dosen $dosen */
        $dosen = $request->user('dosen');
        $isManager = ProgramStudi::query()
            ->where('jurusan_id', $berkas->jurusan_id)
            ->where('sekprodi_dosen_id', $dosen->dosen_id)
            ->exists();
        $isAudience = (string) $dosen->jurusan_id === (string) $berkas->jurusan_id
            && $berkas->aktif
            && in_array($berkas->target, ['dosen', 'semua'], true);

        abort_unless($isManager || $isAudience, 403, 'Berkas ini bukan untuk program studi Anda.');
        abort_unless(StoredUpload::exists($berkas->path), 404, 'File berkas tidak ditemukan.');

        activity_log('download_berkas_prodi_dosen', 'Dosen mengunduh berkas: '.$berkas->judul);

        return StoredUpload::disk($berkas->path)->download($berkas->path, $this->safeFilename($berkas));
    }

    private function ensureManager(Request $request, BerkasProgramStudi $berkas): void
    {
        abort_unless(
            ProgramStudi::query()
                ->where('jurusan_id', $berkas->jurusan_id)
                ->where('sekprodi_dosen_id', $request->user('dosen')->dosen_id)
                ->exists(),
            403,
            'Hanya Sekprodi program studi terkait yang dapat mengelola berkas ini.'
        );
    }

    private function safeFilename(BerkasProgramStudi $berkas): string
    {
        return preg_replace('/[^A-Za-z0-9._-]/', '_', Str::ascii(basename($berkas->nama_file)))
            ?: 'berkas-program-studi';
    }
}
