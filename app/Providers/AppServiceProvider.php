<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Carbon::setLocale('id'); // Set locale to Indonesian

        RateLimiter::for('login-ip', function (Request $request) {
            $tooManyAttemptsResponse = function (Request $request, array $headers) {
                $retryAfter = max(1, (int) ($headers['Retry-After'] ?? 60));

                return back()
                    ->withErrors([
                        'email' => 'Percobaan login gagal berkali-kali. Silakan tunggu beberapa menit sebelum mencoba kembali.',
                    ])
                    ->withInput($request->only('email'))
                    ->with('login_blocked_until', now()->addSeconds($retryAfter)->timestamp)
                    ->withHeaders($headers);
            };

            return Limit::perMinute(30)
                ->by('ip:'.$request->ip())
                ->response($tooManyAttemptsResponse);
        });

        // Query bindings can contain personal data. Keep this diagnostic local-only.
        if (app()->isLocal() && config('app.debug')) {
            DB::listen(function ($query) {
                Log::debug('[SQL]', [
                    'sql' => $query->sql,
                    'time' => $query->time.' ms',
                ]);
            });
        }
    }
}
