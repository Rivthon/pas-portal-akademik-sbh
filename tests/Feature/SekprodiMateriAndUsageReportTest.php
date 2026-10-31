<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SekprodiMateriAndUsageReportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_authorized_admin_can_open_usage_report(): void
    {
        $user = User::query()->firstOrFail();
        $permission = Permission::query()->where('name', 'usage-report-list')->firstOrFail();
        $user->givePermissionTo($permission);

        $this->actingAs($user)
            ->get(route('admin.usage-report.index'))
            ->assertOk()
            ->assertSeeText('Aktivitas Dosen & Mahasiswa')
            ->assertSee('Penggunaan Dosen')
            ->assertSee('Penggunaan Mahasiswa');

        $this->get(route('admin.usage-report.export'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_sekprodi_can_only_open_materials_for_managed_program(): void
    {
        $program = ProgramStudi::query()->whereNotNull('sekprodi_dosen_id')->first();
        if (! $program || ! Dosen::query()->whereKey($program->sekprodi_dosen_id)->exists()) {
            $this->markTestSkipped('Data uji belum memiliki Sekprodi yang valid.');
        }

        $sekprodi = Dosen::query()->findOrFail($program->sekprodi_dosen_id);

        $this->actingAs($sekprodi, 'dosen')
            ->get(route('dosen.sekprodi.materi.index', ['program_studi_id' => $program->jurusan_id]))
            ->assertOk()
            ->assertSee('Materi Program Studi')
            ->assertSee($program->nama);

        $otherProgram = ProgramStudi::query()->whereKeyNot($program->jurusan_id)->first();
        if ($otherProgram) {
            $this->get(route('dosen.sekprodi.materi.index', ['program_studi_id' => $otherProgram->jurusan_id]))
                ->assertForbidden();
        }
    }
}
