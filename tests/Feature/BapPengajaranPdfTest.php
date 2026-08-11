<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BapPengajaranPdfTest extends TestCase
{
    use DatabaseTransactions;

    public function test_bauk_can_download_bap_pdf_for_each_lecturer(): void
    {
        $permission = Permission::findOrCreate('bap-pengajaran-list', 'web');
        $user = User::firstOrFail();
        $user->givePermissionTo($permission);
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $dosen = Dosen::whereHas('dosenMatakuliah.kurikulum', fn ($query) => $query
            ->where('ta_id', $ta->ta_id))->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('admin.bap-pengajaran.pdf', [
                'dosen' => $dosen,
                'ta_id' => $ta->ta_id,
            ]))
            ->assertOk()
            ->assertDownload();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }
}
