<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProgramStudiKaprodiSelectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edit_page_shows_dosen_dropdown_without_changing_legacy_kaprodi(): void
    {
        $admin = $this->authorizedAdmin();
        $programStudi = ProgramStudi::query()->whereNotNull('kaprod')->firstOrFail();
        $legacyName = $programStudi->kaprod;

        $this->actingAs($admin)
            ->get(route('admin.program-studi.edit', $programStudi))
            ->assertOk()
            ->assertSee('kaprodi_dosen_id')
            ->assertSee($legacyName);

        $this->assertSame($legacyName, $programStudi->fresh()->kaprod);
        $this->assertNull($programStudi->fresh()->kaprodi_dosen_id);
    }

    public function test_admin_can_link_kaprodi_to_dosen_account(): void
    {
        $admin = $this->authorizedAdmin();
        $programStudi = ProgramStudi::query()->firstOrFail();
        $kaprodi = Dosen::query()->whereNotNull('kd_dosen')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.program-studi.update', $programStudi), [
                'jurusan_id' => $programStudi->jurusan_id,
                'nama' => $programStudi->nama,
                'jenjang' => $programStudi->jenjang,
                'kaprodi_dosen_id' => $kaprodi->dosen_id,
            ])
            ->assertRedirect(route('admin.program-studi.index'));

        $programStudi->refresh();

        $this->assertSame((int) $kaprodi->dosen_id, (int) $programStudi->kaprodi_dosen_id);
        $this->assertSame($kaprodi->nama, $programStudi->kaprod);
        $this->assertTrue($programStudi->kaprodi->is($kaprodi));
    }

    private function authorizedAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('program-studi-edit', 'web'));

        return $admin;
    }
}
