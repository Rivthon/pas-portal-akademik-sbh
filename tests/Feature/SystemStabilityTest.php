<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemStabilityTest extends TestCase
{
    public function test_system_routes_are_protected_by_matching_permissions(): void
    {
        $expected = [
            'admin.system.health' => 'permission:system-health-list',
            'admin.system.backups.create' => 'permission:system-backup-create',
            'admin.system.backups.download' => 'permission:system-backup-download',
        ];

        foreach ($expected as $routeName => $middleware) {
            $route = Route::getRoutes()->getByName($routeName);
            $this->assertNotNull($route, "Route {$routeName} tidak ditemukan.");
            $this->assertContains('auth', $route->gatherMiddleware());
            $this->assertContains($middleware, $route->gatherMiddleware());
        }
    }

    public function test_operational_commands_are_registered(): void
    {
        $commands = Artisan::all();

        $this->assertArrayHasKey('system:backup', $commands);
        $this->assertArrayHasKey('system:mark-deployed', $commands);
    }

    public function test_backups_use_private_storage_by_default(): void
    {
        $this->assertSame('private', config('system.backup.disk'));
        $this->assertSame('system-backups', config('system.backup.directory'));
        $this->assertStringNotContainsString('public', config('system.backup.directory'));
    }
}
