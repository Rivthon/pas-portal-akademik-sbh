<?php

namespace App\Http\Controllers\Admin\Akademik;

use App\Http\Controllers\Controller;
use App\Models\PedomanAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PedomanAkademikController extends Controller
{
    public function index()
    {
        $pedoman = PedomanAkademik::with('pengunggah')
            ->latest()
            ->paginate(10);

        return view('admin.akademik.pedoman-akademik.index', compact('pedoman'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tahun_berlaku' => ['nullable', 'string', 'max:30'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'],
            'status' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $path = $file->store('pedoman-akademik', 'private');

        try {
            DB::transaction(function () use ($data, $file, $path, $request) {
                $aktif = $request->boolean('status');
                if ($aktif) {
                    PedomanAkademik::where('status', true)->update(['status' => false]);
                }

                PedomanAkademik::create([
                    'judul' => $data['judul'],
                    'tahun_berlaku' => $data['tahun_berlaku'] ?? null,
                    'nama_file' => $file->getClientOriginalName(),
                    'path' => $path,
                    'status' => $aktif,
                    'uploaded_by' => auth()->id(),
                ]);
            });
        } catch (\Throwable $e) {
            Storage::disk('private')->delete($path);
            throw $e;
        }

        activity_log('upload_pedoman_akademik', 'BAAK/Admin mengunggah Pedoman Akademik: '.$data['judul']);

        return back()->with('success', 'Pedoman Akademik berhasil diunggah.');
    }

    public function edit(PedomanAkademik $pedoman)
    {
        return view('admin.akademik.pedoman-akademik.edit', compact('pedoman'));
    }

    public function update(Request $request, PedomanAkademik $pedoman)
    {
        $data = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'tahun_berlaku' => ['nullable', 'string', 'max:30'],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:20480'],
            'status' => ['nullable', 'boolean'],
        ]);

        $file = $request->file('file');
        $pathBaru = $file?->store('pedoman-akademik', 'private');
        $pathLama = $pedoman->path;

        try {
            DB::transaction(function () use ($data, $file, $pathBaru, $request, $pedoman) {
                $aktif = $request->boolean('status');
                if ($aktif) {
                    PedomanAkademik::where('id', '!=', $pedoman->getKey())
                        ->where('status', true)
                        ->update(['status' => false]);
                }

                $pedoman->update([
                    'judul' => $data['judul'],
                    'tahun_berlaku' => $data['tahun_berlaku'] ?? null,
                    'nama_file' => $file?->getClientOriginalName() ?? $pedoman->nama_file,
                    'path' => $pathBaru ?? $pedoman->path,
                    'status' => $aktif,
                    'uploaded_by' => auth()->id(),
                ]);
            });
        } catch (\Throwable $e) {
            if ($pathBaru) {
                Storage::disk('private')->delete($pathBaru);
            }
            throw $e;
        }

        if ($pathBaru && $pathLama && $pathLama !== $pathBaru) {
            Storage::disk('private')->delete($pathLama);
        }

        activity_log('update_pedoman_akademik', 'BAAK/Admin memperbarui Pedoman Akademik: '.$pedoman->judul);

        return redirect()->route('admin.pedoman-akademik.index')
            ->with('success', 'Pedoman Akademik berhasil diperbarui.');
    }

    public function destroy(PedomanAkademik $pedoman)
    {
        $path = $pedoman->path;
        $judul = $pedoman->judul;
        $pedoman->delete();

        if ($path) {
            Storage::disk('private')->delete($path);
        }

        activity_log('hapus_pedoman_akademik', 'BAAK/Admin menghapus Pedoman Akademik: '.$judul);

        return back()->with('success', 'Pedoman Akademik berhasil dihapus.');
    }

    public function preview(PedomanAkademik $pedoman)
    {
        return $this->fileResponse($pedoman, false);
    }

    public function download(PedomanAkademik $pedoman)
    {
        return $this->fileResponse($pedoman, true);
    }

    private function fileResponse(PedomanAkademik $pedoman, bool $download)
    {
        abort_unless($pedoman->path && Storage::disk('private')->exists($pedoman->path), 404);

        $namaFile = $this->safeFilename($pedoman->nama_file);
        if ($download) {
            return Storage::disk('private')->download($pedoman->path, $namaFile);
        }

        return response()->file(Storage::disk('private')->path($pedoman->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$namaFile.'"',
        ]);
    }

    private function safeFilename(?string $filename): string
    {
        $name = Str::ascii(basename((string) $filename));
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'pedoman-akademik.pdf';

        return Str::endsWith(strtolower($name), '.pdf') ? $name : $name.'.pdf';
    }
}
