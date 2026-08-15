<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Tests\TestCase;

class ProductionExceptionRenderingTest extends TestCase
{
    public function test_missing_permission_remains_403_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        Route::get('/_test-production-permission-denied', function () {
            throw UnauthorizedException::forPermissions(['fitur-terbatas']);
        });

        $this->get('/_test-production-permission-denied')
            ->assertForbidden()
            ->assertSee('Akses Ditolak');
    }
}
