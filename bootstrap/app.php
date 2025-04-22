<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Menangani pelaporan exception tertentu
        $exceptions->report(function (\App\Exceptions\InvalidOrderException $e) {
            \Log::error('Invalid Order Exception: ' . $e->getMessage());
        });

        // Menangani rendering untuk NotFoundHttpException
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            return response()->view('errors.404', [], 404);
        });

        // Menangani rendering untuk InvalidOrderException
        $exceptions->render(function (\App\Exceptions\InvalidOrderException $e, $request) {
            return response()->view('errors.invalid_order', ['message' => $e->getMessage()], 500);
        });

        // Menangani error umum lainnya
        $exceptions->render(function (\Throwable $e, $request) {
            if (app()->environment('production')) {
                // Tampilkan halaman error umum di environment production
                return response()->view('errors.general', ['message' => 'Terjadi kesalahan pada server.'], 500);
            }

            // Untuk environment non-production, gunakan default Laravel
            return null;
        });
    })
    ->create();