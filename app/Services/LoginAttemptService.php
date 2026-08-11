<?php

namespace App\Services;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LoginAttemptService
{
    private const STATE_TTL_DAYS = 30;

    public function recordFailure(Request $request, string $guard): int
    {
        $attemptsKey = $this->attemptsKey($request, $guard);
        Cache::add($attemptsKey, 0, now()->addDays(self::STATE_TTL_DAYS));
        $attempts = (int) Cache::increment($attemptsKey);
        $seconds = $this->lockSeconds($attempts);

        if ($seconds > 0) {
            Cache::put($this->blockedUntilKey($request, $guard), now()->addSeconds($seconds)->timestamp, $seconds);
        }

        return $seconds;
    }

    public function remainingSeconds(Request $request, string $guard): int
    {
        $blockedUntil = (int) Cache::get($this->blockedUntilKey($request, $guard), 0);

        return max(0, $blockedUntil - now()->timestamp);
    }

    public function clear(Request $request, string $guard): void
    {
        Cache::forget($this->attemptsKey($request, $guard));
        Cache::forget($this->blockedUntilKey($request, $guard));
    }

    public function failureResponse(Request $request, string $guard, string $message): RedirectResponse
    {
        $seconds = $this->recordFailure($request, $guard);

        if ($seconds > 0) {
            return $this->blockedResponse($request, $seconds);
        }

        return back()
            ->withErrors(['email' => $message])
            ->withInput($request->only('email'));
    }

    public function blockedResponse(Request $request, int $remaining): RedirectResponse
    {
        return back()
            ->withErrors([
                'email' => 'Percobaan login gagal berkali-kali. Silakan tunggu beberapa menit sebelum mencoba kembali.',
            ])
            ->withInput($request->only('email'))
            ->with('login_blocked_until', now()->addSeconds($remaining)->timestamp)
            ->withHeaders([
                'Retry-After' => (string) $remaining,
            ]);
    }

    public function attempts(Request $request, string $guard): int
    {
        return (int) Cache::get($this->attemptsKey($request, $guard), 0);
    }

    private function lockSeconds(int $attempts): int
    {
        return match (true) {
            $attempts < 5 => 0,
            $attempts === 5 => 60,
            $attempts === 6 => 120,
            $attempts === 7 => 300,
            $attempts === 8 => 600,
            default => 900,
        };
    }

    private function attemptsKey(Request $request, string $guard): string
    {
        return 'login-attempts:'.$this->identityHash($request, $guard);
    }

    private function blockedUntilKey(Request $request, string $guard): string
    {
        return 'login-blocked-until:'.$this->identityHash($request, $guard);
    }

    private function identityHash(Request $request, string $guard): string
    {
        $identity = mb_strtolower(trim((string) $request->input('email')));

        return hash('sha256', $guard.'|'.$identity.'|'.$request->ip());
    }
}
