<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use Tests\TestCase;

class DosenClassAssignmentScopeTest extends TestCase
{
    public function test_theory_schedule_access_is_limited_to_the_assigned_class(): void
    {
        $jadwalIds = Jadwal::query()
            ->assignedToDosen(15, 'teori')
            ->whereIn('id', [1879, 1919, 1882, 1922])
            ->pluck('id');

        $this->assertEqualsCanonicalizing([1879, 1882], $jadwalIds->all());
    }

    public function test_practice_schedule_access_is_limited_to_the_assigned_class(): void
    {
        $jadwalIds = JadwalPraktik::query()
            ->assignedToDosen(15, 'praktik')
            ->whereIn('id', [1162, 1163, 1166, 1167])
            ->pluck('id');

        $this->assertEqualsCanonicalizing([1162, 1166], $jadwalIds->all());
    }

    public function test_dosen_pages_and_grade_input_do_not_expose_another_class(): void
    {
        $dosen = Dosen::findOrFail(15);

        $jadwalResponse = $this->actingAs($dosen, 'dosen')->get(route('dosen.jadwal.index'))->assertOk();
        $jadwalIds = $jadwalResponse->viewData('jadwalList')->flatten(1)->pluck('jadwal_id');
        $this->assertTrue($jadwalIds->contains(1879));
        $this->assertFalse($jadwalIds->contains(1919));

        $absensiResponse = $this->get(route('dosen.absensi.index'))->assertOk();
        $absensiIds = $absensiResponse->viewData('absensiList')->flatten(1)->pluck('jadwal_id');
        $this->assertTrue($absensiIds->contains(1879));
        $this->assertFalse($absensiIds->contains(1919));

        $programStudiId = Jadwal::findOrFail(1879)->jurusan_id;
        $nilaiResponse = $this->get(route('dosen.mata-kuliah.by-filter', [$programStudiId, 20]))
            ->assertOk();
        $nilaiJadwalIds = collect($nilaiResponse->json())->pluck('jadwal_id');
        $this->assertTrue($nilaiJadwalIds->contains(1879));
        $this->assertFalse($nilaiJadwalIds->contains(1919));

        $this->get(route('dosen.input-nilai-dosen.mahasiswa', 1919))->assertForbidden();
    }
}
