<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PedomanAkademik;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PedomanAkademikController extends Controller
{
    public function index(): JsonResponse
    {
        $pedoman = PedomanAkademik::query()
            ->where('status', true)
            ->latest()
            ->get()
            ->map(fn (PedomanAkademik $item) => [
                'id' => (int) $item->id,
                'judul' => $item->judul,
                'tahun_berlaku' => $item->tahun_berlaku,
                'nama_file' => $this->safeFilename($item->nama_file),
                'tersedia' => filled($item->path) && Storage::disk('private')->exists($item->path),
                'diperbarui_pada' => optional($item->updated_at)->toIso8601String(),
            ]);

        return response()->json(['pedoman' => $pedoman]);
    }

    public function file(PedomanAkademik $pedoman): BinaryFileResponse
    {
        abort_unless(
            $pedoman->status && $pedoman->path && Storage::disk('private')->exists($pedoman->path),
            404,
            'File Pedoman Akademik tidak ditemukan.'
        );

        activity_log(
            'akses_pedoman_akademik_mobile',
            'Mahasiswa membuka atau mengunduh Pedoman Akademik melalui aplikasi Android: '.$pedoman->judul
        );

        return response()->file(Storage::disk('private')->path($pedoman->path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$this->safeFilename($pedoman->nama_file).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function safeFilename(?string $filename): string
    {
        $name = Str::ascii(basename((string) $filename));
        $name = preg_replace('/[^A-Za-z0-9._-]/', '_', $name) ?: 'pedoman-akademik.pdf';

        return Str::endsWith(strtolower($name), '.pdf') ? $name : $name.'.pdf';
    }
}
