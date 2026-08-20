<?php

use App\Exceptions\InvalidOrderException;
use App\Http\Middleware\CheckMahasiswaStatus;
use App\Http\Middleware\LogActivity;
use App\Http\Middleware\ProgressiveLoginThrottle;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'mhs.status' => CheckMahasiswaStatus::class,
            'log.activity' => LogActivity::class,
            'login.progressive' => ProgressiveLoginThrottle::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }

            $loginRoute = match (true) {
                $request->is('dosen', 'dosen/*') => 'dosen.login',
                $request->is('mahasiswa', 'mahasiswa/*') => 'mahasiswa.login',
                default => 'admin.login',
            };

            return redirect()
                ->guest(route($loginRoute))
                ->with(
                    'auth_notice',
                    'Sesi Anda telah berakhir atau Anda telah logout. Silakan login kembali.'
                );
        });

        // Menangani pelaporan exception tertentu
        $exceptions->report(function (InvalidOrderException $e) {
            Log::error('Invalid Order Exception: '.$e->getMessage());
        });

        // Menangani rendering untuk NotFoundHttpException
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            return response()->view('errors.404', [], 404);
        });

        // Menangani rendering untuk InvalidOrderException
        $exceptions->render(function (InvalidOrderException $e, $request) {
            return response()->view('errors.invalid_order', ['message' => $e->getMessage()], 500);
        });

    })
    ->create();
