<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SystemHealthDashboardTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_admin_can_open_system_health_dashboard(): void
    {
        Storage::fake('local');
        Storage::fake('private');
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findByName('system-health-list'));

        $this->actingAs($user)
            ->get(route('admin.system.health'))
            ->assertOk()
            ->assertSee('Kesehatan Sistem PAS')
            ->assertSee('Database')
            ->assertSee('Versi Deployment');
    }

    public function test_admin_without_permission_cannot_open_system_health_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.system.health'))
            ->assertForbidden();
    }

    public function test_authorized_admin_can_download_private_backup(): void
    {
        Storage::fake('private');
        $user = User::factory()->create();
        $user->givePermissionTo(Permission::findByName('system-backup-download'));
        Storage::disk('private')->put('system-backups/pas_backup_test.zip', 'zip-content');

        $this->actingAs($user)
            ->get(route('admin.system.backups.download', 'pas_backup_test.zip'))
            ->assertOk()
            ->assertDownload('pas_backup_test.zip');
    }
}
