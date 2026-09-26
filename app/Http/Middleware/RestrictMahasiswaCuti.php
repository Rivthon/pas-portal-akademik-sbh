<?php

namespace App\Http\Middleware;

use App\Models\Krs;
use App\Models\TahunAkademik;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RestrictMahasiswaCuti
{
    private const ALLOWED_ROUTES = [
        'mahasiswa.dashboard',
        'mahasiswa.profile.*',
        'mahasiswa.cuti.*',
        'mahasiswa.calendar-akademik.file',
        'mahasiswa.pedoman-akademik.*',
        'mahasiswa.administrasi.index',
        'mahasiswa.getBerita',
        'mahasiswa.index.berita',
        'mahasiswa.getBeritaKampus',
        'mahasiswa.berita.detail',
        'mahasiswa.permintaan.*',
        'mahasiswa.pengajuan.*',
        'mahasiswa.cetak-transkrip',
        'mahasiswa.khs.riwayat',
        'mahasiswa.nilai-ujian.riwayat',
        'mahasiswa.status.krs.index',
        'mahasiswa.krs.cetak-krs-mahasiswa',
        'mahasiswa.rekap.absensi',
        'mahasiswa.rekap.absensi.detail',
        'mahasiswa.absensi-praktik.*',
        'mahasiswa.skpi.index',
        'mahasiswa.skpi.cetak',
        'mahasiswa.skpi.download',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $mahasiswa = $request->user('mahasiswa');

        if (! $mahasiswa || strtolower(trim((string) $mahasiswa->status_mhs)) !== 'cuti') {
            return $next($request);
        }

        $routeName = (string) $request->route()?->getName();

        if (Str::is(self::ALLOWED_ROUTES, $routeName)
            || $this->isHistoricalKhsDownload($request, $routeName)
            || $this->isHistoricalEdom($request, $routeName, (int) $mahasiswa->mahasiswa_id)) {
            return $next($request);
        }

        $message = 'Akun Anda sedang berstatus Cuti Akademik. Fitur kegiatan akademik semester berjalan tidak dapat digunakan.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => 'cuti',
            ], 423);
        }

        return redirect()->route('mahasiswa.dashboard')->with('cuti_notice', $message);
    }

    private function isHistoricalKhsDownload(Request $request, string $routeName): bool
    {
        if ($routeName !== 'mahasiswa.khs.cetak') {
            return false;
        }

        $selectedTaId = $request->integer('ta_id');
        $activeTaId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');

        return $selectedTaId > 0 && $activeTaId > 0 && $selectedTaId < $activeTaId;
    }

    private function isHistoricalEdom(Request $request, string $routeName, int $mahasiswaId): bool
    {
        if (! Str::is('mahasiswa.edom.*', $routeName)) {
            return false;
        }

        $activeTaId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');
        if ($activeTaId <= 0) {
            return false;
        }

        $selectedTaId = $request->integer('ta_id');
        if ($selectedTaId <= 0 && $request->route('krs_id')) {
            $selectedTaId = (int) Krs::where('mahasiswa_id', $mahasiswaId)
                ->whereKey($request->route('krs_id'))
                ->value('ta_id');
        }

        return $selectedTaId > 0 && $selectedTaId < $activeTaId;
    }
}
