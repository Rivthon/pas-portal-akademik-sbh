<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class EnforceWebsiteAvailability
{
    public const CACHE_KEY = 'website_down_enabled';

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->websiteIsDown()) {
            return $next($request);
        }

        // Jalur pemulihan harus tetap tersedia jika sesi admin berakhir.
        if ($request->is('admin/login')) {
            return $next($request);
        }

        $admin = Auth::guard('web')->user();
        if ($admin && $admin->can('website-maintenance-toggle')) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'PAS sedang dinonaktifkan sementara oleh administrator.',
                'maintenance' => true,
            ], 503, ['Retry-After' => '300']);
        }

        return response()
            ->view('errors.website-down', [], 503)
            ->header('Retry-After', '300');
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function websiteIsDown(): bool
    {
        try {
            return (bool) Cache::rememberForever(
                self::CACHE_KEY,
                fn () => Setting::query()->value('website_down') ?: false
            );
        } catch (Throwable) {
            // Saat instalasi/migration awal, jangan membuat aplikasi terkunci.
            return false;
        }
    }
}
