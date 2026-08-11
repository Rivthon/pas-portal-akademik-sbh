<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminKrsArchiveTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_and_download_historical_krs_pdf(): void
    {
        [$admin, $krs, $semester] = $this->archiveContext();

        $this->actingAs($admin)
            ->get(route('admin.krs-archive.index', [
                'search' => $krs->mahasiswa->nim,
                'ta_id' => $krs->ta_id,
                'semester' => $semester,
            ]))
            ->assertOk()
            ->assertSee('Arsip KRS')
            ->assertSee($krs->mahasiswa->nim)
            ->assertSee('Semester '.$semester);

        $response = $this->get(route('admin.krs-archive.download', [
            $krs->mahasiswa,
            $krs->ta_id,
            $semester,
        ]))->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_admin_can_download_one_semester_as_zip_without_zip_extension(): void
    {
        [$admin, $krs, $semester] = $this->archiveContext();

        $response = $this->actingAs($admin)
            ->get(route('admin.krs-archive.download-semester', [
                'search' => $krs->mahasiswa->nim,
                'ta_id' => $krs->ta_id,
                'semester' => $semester,
            ]))
            ->assertOk()
            ->assertDownload();

        $file = $response->baseResponse->getFile()->getPathname();
        $contents = file_get_contents($file);

        $this->assertStringStartsWith("PK\x03\x04", $contents);
        $this->assertStringContainsString('.pdf', $contents);
        $this->assertSame("PK\x05\x06", substr($contents, -22, 4));

        @unlink($file);
    }

    private function archiveContext(): array
    {
        $permission = Permission::findOrCreate('krs-list', 'web');
        $admin = User::firstOrFail();
        $admin->givePermissionTo($permission);

        $krs = Krs::with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->whereHas('mahasiswa')
            ->whereHas('kurikulum.mataKuliah', fn ($query) => $query->whereNotNull('smt'))
            ->firstOrFail();
        $semester = (int) $krs->kurikulum->mataKuliah->smt;

        return [$admin, $krs, $semester];
    }
}
