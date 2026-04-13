<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMahasiswaStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $type (krs|uts|uas|uap|akhir)
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $type)
    {
        $mahasiswa = Auth::guard('mahasiswa')->user();
        
        if (!$mahasiswa) {
            return redirect()->route('mahasiswa.login');
        }

        $errorMsg = null;

        switch ($type) {
            case 'krs':
                if ($mahasiswa->status_krs == 0) {
                    $errorMsg = 'Anda belum diizinkan mencetak Kartu KRS.';
                }
                break;

            case 'uts':
                if ($mahasiswa->status_uts == 0) {
                    $errorMsg = 'Anda belum diizinkan mencetak Kartu UTS.';
                }
                break;

            case 'uas':
                if ($mahasiswa->status_uas == 0) {
                    $errorMsg = 'Anda belum diizinkan mencetak Kartu UAS.';
                }
                break;

            case 'uap':
                if ($mahasiswa->status_uap == 0) {
                    $errorMsg = 'Anda belum diizinkan mencetak Kartu UAP.';
                }
                break;

            case 'akhir':
                if ($mahasiswa->status_akhir != 1) {
                    $errorMsg = 'Akses KHS ditolak. Silakan selesaikan kewajiban administrasi Anda terlebih dahulu.';
                }
                break;
        }

        if ($errorMsg) {
            return redirect()->back()->with('error', $errorMsg);
        }

        return $next($request);
    }
}
