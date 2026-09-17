<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEdomIsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $enabled = (bool) (Setting::query()->value('edom_enabled') ?? true);

        if (! $enabled) {
            return redirect()
                ->route('mahasiswa.dashboard')
                ->with('warning', 'Pengisian EDOM belum dibuka oleh admin. Silakan coba kembali sesuai jadwal akademik.');
        }

        return $next($request);
    }
}
