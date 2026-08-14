<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PedomanAkademik;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PedomanAkademikController extends Controller
{
    public function index()
    {
        $pedoman = PedomanAkademik::where('status', true)
            ->latest()
            ->get();

        return view('mahasiswa.pedoman-akademik.index', compact('pedoman'));
    }

    public function preview(PedomanAkademik $pedoman)
    {
        $this->pastikanAktifDanAda($pedoman);

        return response()->file(Storage::disk('private')->path($pedoman->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->safeFilename($pedoman->nama_file).'"',
        ]);
    }

    public function download(PedomanAkademik $pedoman)
    {
        $this->pastikanAktifDanAda($pedoman);

        activity_log('download_pedoman_akademik', 'Mahasiswa mengunduh Pedoman Akademik: '.$pedoman->judul);

        return Storage::disk('private')->download(
            $pedoman->path,
            $this->safeFilename($pedoman->nama_file)
        );
    }

    private function pastikanAktifDanAda(PedomanAkademik $pedoman): void
    {
        abort_unless(
            $pedoman->status && $pedoman->path && Storage::disk('private')->exists($pedoman->path),
            404
        );
    }

    private function safeFilename(?string $filename): string
    {
        $name = Str::ascii(basename((string) $filename));
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'pedoman-akademik.pdf';

        return Str::endsWith(strtolower($name), '.pdf') ? $name : $name.'.pdf';
    }
}
