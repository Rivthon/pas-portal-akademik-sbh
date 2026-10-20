<?php

namespace App\Http\Middleware;

use App\Services\ProductionErrorReporter;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CaptureProductionErrors
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() >= 500) {
            app(ProductionErrorReporter::class)->captureResponse(
                $request,
                $response->getStatusCode(),
                $response->getContent()
            );
        }

        return $response;
    }
}
