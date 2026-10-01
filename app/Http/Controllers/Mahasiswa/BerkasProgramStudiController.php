<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BerkasProgramStudi;
use App\Models\Mahasiswa;
use App\Support\StoredUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BerkasProgramStudiController extends Controller
{
    public function index(Request $request): View
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user('mahasiswa');
        $berkas = BerkasProgramStudi::query()
            ->with(['programStudi', 'pengunggah'])
            ->where('jurusan_id', $mahasiswa->jurusan_id)
            ->where('aktif', true)
            ->whereIn('target', ['mahasiswa', 'semua'])
            ->latest()
            ->paginate(12);

        return view('mahasiswa.berkas-program-studi.index', compact('berkas'));
    }

    public function download(Request $request, BerkasProgramStudi $berkas): StreamedResponse
    {
        /** @var Mahasiswa $mahasiswa */
        $mahasiswa = $request->user('mahasiswa');
        abort_unless(
            (string) $mahasiswa->jurusan_id === (string) $berkas->jurusan_id
                && $berkas->aktif
                && in_array($berkas->target, ['mahasiswa', 'semua'], true),
            403,
            'Berkas ini bukan untuk program studi Anda.'
        );
        abort_unless(StoredUpload::exists($berkas->path), 404, 'File berkas tidak ditemukan.');

        activity_log('download_berkas_prodi_mahasiswa', 'Mahasiswa mengunduh berkas: '.$berkas->judul);

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', Str::ascii(basename($berkas->nama_file)))
            ?: 'berkas-program-studi';

        return StoredUpload::disk($berkas->path)->download($berkas->path, $filename);
    }
}
