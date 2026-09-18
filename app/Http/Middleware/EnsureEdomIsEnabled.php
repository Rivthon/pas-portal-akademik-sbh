<?php

namespace App\Http\Middleware;

use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEdomIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $mahasiswa = $request->user('mahasiswa');
        $activeTaId = (int) (TahunAkademik::query()->where('status_ta', 1)->value('ta_id') ?? 0);
        $targetTaId = $request->integer('ta_id');
        $routeKrsId = (int) ($request->route('krs_id') ?? 0);

        // Tautan form lama dapat tidak membawa ta_id. Ambil periodenya dari
        // KRS milik mahasiswa agar aturan tahun akademik tidak dapat dilewati.
        if (! $targetTaId && $routeKrsId && $mahasiswa) {
            $targetTaId = (int) (Krs::query()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('krs_id', $routeKrsId)
                ->value('ta_id') ?? 0);
        }

        $isHistorical = $targetTaId > 0
            && $activeTaId > 0
            && $targetTaId !== $activeTaId;

        if ($isHistorical) {
            $historicalKrs = Krs::with('kurikulum.mataKuliah')
                ->where('mahasiswa_id', $mahasiswa?->mahasiswa_id)
                ->where('ta_id', $targetTaId)
                ->when($routeKrsId, fn ($query) => $query->where('krs_id', $routeKrsId))
                ->get();

            $hasBaakPublication = $mahasiswa
                && KhsPublication::filterPublishedKrs($historicalKrs, $mahasiswa)->isNotEmpty();

            if (! $hasBaakPublication) {
                return redirect()
                    ->route('mahasiswa.dashboard')
                    ->with('warning', 'EDOM tahun akademik tersebut belum diaktifkan melalui penerbitan KHS oleh BAAK.');
            }

            return $next($request);
        }

        $enabled = (bool) (Setting::query()->value('edom_enabled') ?? true);

        if (! $enabled) {
            return redirect()
                ->route('mahasiswa.dashboard')
                ->with('warning', 'Pengisian EDOM tahun akademik aktif belum dibuka oleh admin. Silakan coba kembali sesuai jadwal akademik.');
        }

        return $next($request);
    }
}
