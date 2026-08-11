<?php

namespace App\Http\Controllers;

use App\Models\CalendarAkademik;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CalendarAkademikFileController extends Controller
{
    public function show(CalendarAkademik $calendar): BinaryFileResponse
    {
        $this->authorizeViewer($calendar);

        $path = $calendar->storagePath();
        abort_if(! $path || ! Storage::disk('public')->exists($path), 404, 'File Kalender Akademik tidak ditemukan.');

        $filename = $this->safeFilename($calendar->nama_file ?: basename($path));

        return response()->file(Storage::disk('public')->path($path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function authorizeViewer(CalendarAkademik $calendar): void
    {
        if (Auth::guard('web')->check()) {
            return;
        }

        $viewer = Auth::guard('dosen')->user() ?: Auth::guard('mahasiswa')->user();

        abort_unless(
            $viewer
                && (string) $viewer->jurusan_id === (string) $calendar->jurusan_id
                && (bool) $calendar->status,
            403,
            'Anda tidak memiliki akses ke Kalender Akademik ini.'
        );
    }

    private function safeFilename(string $filename): string
    {
        $filename = str_replace(["\r", "\n", '"'], '', basename($filename));

        return $filename !== '' ? $filename : 'kalender-akademik.pdf';
    }
}
