<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminTeachingJournalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_download_teaching_journal_per_course_schedule(): void
    {
        $permission = Permission::findOrCreate('absensi-export', 'web');
        $admin = User::firstOrFail();
        $admin->givePermissionTo($permission);
        $jadwal = Jadwal::whereHas('pertemuan', fn ($query) => $query
            ->whereDate('tanggal_pertemuan', '<=', now()->toDateString())
            ->whereHas('absensi'))
            ->firstOrFail();

        $response = $this->actingAs($admin)
            ->post(route('admin.absensi.jurnalMengajar'), ['jadwal_id' => $jadwal->id])
            ->assertOk()
            ->assertDownload();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_teaching_journal_template_omits_pbm_method_column(): void
    {
        $template = file_get_contents(resource_path('views/admin/akademik/absensi/jurnal-mengajar-pdf.blade.php'));

        $this->assertStringContainsString('JURNAL MENGAJAR / LAPORAN BAP', $template);
        $this->assertStringContainsString('REKAP ABSENSI MAHASISWA', $template);
        $this->assertStringContainsString('Dosen Pengajar', $template);
        $this->assertStringContainsString('page-break-after: always', $template);
        $this->assertStringContainsString('<section class="page-break">', $template);
        $this->assertStringNotContainsString('Metode PBM', $template);
    }
}
