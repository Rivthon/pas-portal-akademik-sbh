<?php

namespace App\Http\Controllers\Api\V1\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BerkasProgramStudi;
use App\Models\Mahasiswa;
use App\Support\StoredUpload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramStudyFileController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();

        $files = BerkasProgramStudi::query()
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->where('aktif', true)
            ->whereIn('target', ['mahasiswa', 'semua'])
            ->latest()
            ->get()
            ->map(fn (BerkasProgramStudi $file) => [
                'id' => (int) $file->id,
                'judul' => $file->judul,
                'deskripsi' => $file->deskripsi,
                'nama_file' => $file->nama_file,
                'mime_type' => $file->mime_type,
                'ukuran' => (int) $file->ukuran,
                'tersedia' => StoredUpload::exists($file->path),
                'diperbarui_pada' => $file->updated_at?->toIso8601String(),
            ])
            ->values();

        return response()->json([
            'program_studi' => $mahasiswa->programStudi?->nama,
            'berkas' => $files,
        ]);
    }

    public function file(Request $request, BerkasProgramStudi $berkas): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user();
        abort_unless(
            (string) $mahasiswa->jurusan_id === (string) $berkas->jurusan_id
                && $berkas->aktif
                && in_array($berkas->target, ['mahasiswa', 'semua'], true),
            403,
            'Berkas ini bukan untuk program studi Anda.'
        );
        abort_unless(StoredUpload::exists($berkas->path), 404, 'File berkas tidak ditemukan.');

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', Str::ascii(basename($berkas->nama_file)))
            ?: 'berkas-program-studi';

        activity_log('download_berkas_prodi_mobile', 'Mahasiswa mengunduh berkas melalui aplikasi: '.$berkas->judul);

        return StoredUpload::disk($berkas->path)->download($berkas->path, $filename);
    }
}
