<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaCutiAccessRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_cuti_tetap_bisa_membuka_dashboard_dan_riwayat_tetapi_fitur_aktif_dikunci(): void
    {
        $mahasiswa = Mahasiswa::whereNotNull('dosen_id')->firstOrFail();
        $mahasiswa->update(['status_mhs' => 'cuti']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Status Akademik: Cuti');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.khs.riwayat'))
            ->assertSessionMissing('cuti_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.krs.index'))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('cuti_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->getJson(route('mahasiswa.lms.index'))
            ->assertStatus(423)
            ->assertJsonPath('status', 'cuti');
    }

    public function test_mahasiswa_aktif_tidak_terkena_pembatasan_cuti(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'aktif']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertDontSee('Status Akademik: Cuti');
    }

    public function test_edom_lama_tetap_bisa_diakses_tetapi_edom_semester_aktif_dikunci(): void
    {
        $activeTaId = (int) TahunAkademik::where('status_ta', 1)->value('ta_id');
        $krsLama = Krs::with('mahasiswa')
            ->where('ta_id', '!=', $activeTaId)
            ->whereHas('mahasiswa')
            ->firstOrFail();
        $mahasiswa = $krsLama->mahasiswa;
        $mahasiswa->update(['status_mhs' => 'cuti']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.edom.index', ['ta_id' => $krsLama->ta_id]))
            ->assertSessionMissing('cuti_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.edom.index'))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('cuti_notice');
    }
}
