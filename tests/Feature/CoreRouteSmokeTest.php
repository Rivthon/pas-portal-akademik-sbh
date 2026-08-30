<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CoreRouteSmokeTest extends TestCase
{
    public function test_core_pas_routes_are_registered(): void
    {
        $routes = [
            'admin.home',
            'admin.lms.index',
            'admin.rps.index',
            'admin.absensi.index',
            'dosen.dashboard',
            'dosen.lms.index',
            'dosen.rps.index',
            'dosen.absensi.index',
            'mahasiswa.dashboard',
            'mahasiswa.krs.index',
            'mahasiswa.lms.index',
            'mahasiswa.rps.index',
            'mahasiswa.rekap.absensi',
        ];

        foreach ($routes as $routeName) {
            $this->assertNotNull(
                Route::getRoutes()->getByName($routeName),
                "Route inti {$routeName} tidak ditemukan."
            );
        }
    }

    public function test_core_role_routes_require_authentication(): void
    {
        $expected = [
            'admin.home' => 'auth',
            'dosen.dashboard' => 'auth:dosen',
            'mahasiswa.dashboard' => 'auth:mahasiswa',
        ];

        foreach ($expected as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route);
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }
}
