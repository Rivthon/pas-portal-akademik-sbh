<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Middleware untuk logging aktivitas secara otomatis pada route.
     *
     * Penggunaan di route:
     *   ->middleware('log.activity:akses_krs,Mengakses halaman KRS')
     *   ->middleware('log.activity:lihat_nilai')
     *
     * @param  string  $aktivitas  Nama aktivitas
     * @param  string|null  $deskripsi  Keterangan (opsional)
     */
    public function handle(Request $request, Closure $next, string $aktivitas, ?string $deskripsi = null): Response
    {
        // Log aktivitas setelah request berhasil diproses
        $response = $next($request);

        // Hanya log jika response sukses (2xx atau 3xx)
        if ($response->isSuccessful() || $response->isRedirection()) {
            activity_log($aktivitas, $deskripsi);
        }

        return $response;
    }
}
