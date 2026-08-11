<?php

namespace App\Http\Middleware;

use App\Services\LoginAttemptService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProgressiveLoginThrottle
{
    public function __construct(private readonly LoginAttemptService $attempts) {}

    public function handle(Request $request, Closure $next, string $guard): Response
    {
        $remaining = $this->attempts->remainingSeconds($request, $guard);

        if ($remaining <= 0) {
            return $next($request);
        }

        return $this->attempts->blockedResponse($request, $remaining);
    }
}
