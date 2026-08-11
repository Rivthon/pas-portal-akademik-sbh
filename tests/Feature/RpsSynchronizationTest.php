<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Rps;
use Tests\TestCase;

class RpsSynchronizationTest extends TestCase
{
    public function test_komunikasi_kesehatan_gizi_is_visible_and_openable_by_dosen(): void
    {
        $rps = Rps::where('kurikulum_id', 950)->firstOrFail();
        $dosen = Dosen::findOrFail(22);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.rps.index'))
            ->assertOk()
            ->assertSee(route('dosen.rps.show', $rps), false);

        $this->get(route('dosen.rps.show', $rps))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_enrolled_mahasiswa_sees_the_same_rps_and_can_open_it(): void
    {
        $rps = Rps::where('kurikulum_id', 950)->firstOrFail();
        $mahasiswaId = Krs::where('kurikulum_id', 950)
            ->where('ta_id', 18)
            ->value('mahasiswa_id');
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.rps.index'))
            ->assertOk()
            ->assertSee(route('mahasiswa.rps.show', $rps), false);

        $this->get(route('mahasiswa.rps.show', $rps))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
