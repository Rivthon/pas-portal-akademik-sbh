<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\JadwalPraktik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenAbsensiPraktikAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_existing_practice_assignment_is_synchronized_and_visible_for_dosen(): void
    {
        $dosen = Dosen::findOrFail(45);

        JadwalPraktik::query()
            ->where('ta_id', 18)
            ->where('kurikulum_id', 921)
            ->delete();

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi-praktik.index'));

        $response->assertOk()
            ->assertSee('Analisis Sediaan Farmasi')
            ->assertSee('Reguler')
            ->assertSee('Karyawan')
            ->assertSee('Semester Mata Kuliah')
            ->assertSee('Program Studi')
            ->assertSee('Jenis Kelas')
            ->assertSee('Ruangan')
            ->assertSee('Tahun Akademik');

        $this->assertDatabaseHas('jadwal_praktik', [
            'ta_id' => 18,
            'kurikulum_id' => 921,
            'jenis_kelas' => 'reguler',
        ]);
        $this->assertDatabaseHas('jadwal_praktik', [
            'ta_id' => 18,
            'kurikulum_id' => 921,
            'jenis_kelas' => 'karyawan',
        ]);
    }
}
