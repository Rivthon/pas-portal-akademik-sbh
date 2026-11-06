<?php

namespace Tests\Feature;

use App\Http\Middleware\EnforceWebsiteAvailability;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WebsiteDownControlTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        EnforceWebsiteAvailability::clearCache();
    }

    protected function tearDown(): void
    {
        EnforceWebsiteAvailability::clearCache();
        parent::tearDown();
    }

    public function test_website_down_blocks_public_web_and_api_but_keeps_admin_login_available(): void
    {
        Setting::query()->firstOrFail()->update(['website_down' => true]);
        EnforceWebsiteAvailability::clearCache();

        $this->get('/mahasiswa/login')
            ->assertStatus(503)
            ->assertSee('PAS Sedang Tidak Tersedia');

        $this->getJson('/api/v1/health')
            ->assertStatus(503)
            ->assertJsonPath('maintenance', true);

        $this->get(route('admin.login'))->assertOk();
    }

    public function test_authorized_admin_can_access_panel_and_turn_website_back_on(): void
    {
        $admin = User::query()->firstOrFail();
        $permission = Permission::query()->where('name', 'website-maintenance-toggle')->firstOrFail();
        $admin->givePermissionTo($permission);
        $admin->givePermissionTo('website-maintenance-bypass');
        Setting::query()->firstOrFail()->update(['website_down' => true]);
        EnforceWebsiteAvailability::clearCache();

        $this->actingAs($admin)
            ->get(route('admin.home'))
            ->assertOk();

        $this->patch(route('admin.settings.website-availability'), ['website_down' => false])
            ->assertRedirect(route('admin.settings.edit'));

        $this->assertDatabaseHas('settings', ['website_down' => false]);
        $this->get('/mahasiswa/login')->assertOk();
    }

    public function test_admin_without_special_permission_cannot_toggle_website(): void
    {
        Setting::query()->firstOrFail()->update(['website_down' => false]);
        EnforceWebsiteAvailability::clearCache();
        $admin = User::query()->get()->first(
            fn (User $user) => ! $user->can('website-maintenance-toggle')
        );
        if (! $admin) {
            $this->markTestSkipped('Tidak ada akun admin nonprivileged pada data uji.');
        }

        $this->actingAs($admin)
            ->patch(route('admin.settings.website-availability'), ['website_down' => true])
            ->assertForbidden();
    }

    public function test_baak_can_access_but_bauk_cannot_during_website_down(): void
    {
        Setting::query()->firstOrFail()->update(['website_down' => true]);
        EnforceWebsiteAvailability::clearCache();

        $baak = User::query()->role('baak')->first();
        if ($baak) {
            $baak->givePermissionTo('website-maintenance-bypass');
            $this->actingAs($baak)->get(route('admin.home'))->assertOk();
            $this->patch(route('admin.settings.website-availability'), ['website_down' => false])
                ->assertForbidden();
            auth()->logout();
        }

        $bauk = User::query()->role('bauk')->first();
        if ($bauk) {
            // Tetap harus ditolak walaupun permission tersisip secara keliru.
            $bauk->givePermissionTo('website-maintenance-bypass');
            $this->actingAs($bauk)
                ->get(route('admin.home'))
                ->assertStatus(503)
                ->assertSee('PAS Sedang Tidak Tersedia');
        }
    }
}
