<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminKhsAbsoluteValueTest extends TestCase
{
    use DatabaseTransactions;

    public function test_edited_absolute_value_is_saved_and_used_for_letter_grade(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('input-nilai', 'web'));
        $krs = Krs::query()
            ->whereHas('kurikulum.mataKuliah')
            ->firstOrFail();
        $mahasiswaId = $krs->mahasiswa_id;

        $response = $this->actingAs($admin)->postJson(route('admin.nilai.save'), [
            'krs_id' => [$mahasiswaId => $krs->krs_id],
            'uts' => [$mahasiswaId => 10],
            'uas' => [$mahasiswaId => 10],
            'tugas' => [$mahasiswaId => 10],
            'absensi' => [$mahasiswaId => 10],
            'praktik' => [$mahasiswaId => 10],
            'nilai_akhir' => [$mahasiswaId => 88.75],
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true);

        $krs->refresh();

        $this->assertSame(88.75, (float) $krs->akhir);
        $this->assertSame('A', $krs->khs);
    }

    public function test_manual_absolute_zero_is_not_replaced_by_automatic_value(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('input-nilai', 'web'));
        $krs = Krs::query()
            ->whereHas('kurikulum.mataKuliah')
            ->firstOrFail();
        $mahasiswaId = $krs->mahasiswa_id;

        $this->actingAs($admin)->postJson(route('admin.nilai.save'), [
            'krs_id' => [$mahasiswaId => $krs->krs_id],
            'uts' => [$mahasiswaId => 100],
            'uas' => [$mahasiswaId => 100],
            'tugas' => [$mahasiswaId => 100],
            'absensi' => [$mahasiswaId => 100],
            'praktik' => [$mahasiswaId => 100],
            'nilai_akhir' => [$mahasiswaId => 0],
        ])->assertOk();

        $krs->refresh();

        $this->assertSame(0.0, (float) $krs->akhir);
        $this->assertSame('E', $krs->khs);
    }
}
