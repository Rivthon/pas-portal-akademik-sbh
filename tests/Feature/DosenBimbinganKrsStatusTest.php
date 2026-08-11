<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenBimbinganKrsStatusTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_sees_active_krs_status_for_each_guidance_student(): void
    {
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $dosen = Dosen::whereHas('mahasiswa', fn ($query) => $query
            ->where('status_mhs', 'aktif'))
            ->firstOrFail();

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.nilai-dosen.lihat'))
            ->assertOk()
            ->assertSee('STATUS KRS');

        $mahasiswaList = $response->viewData('mahasiswaList');
        $this->assertNotEmpty($mahasiswaList);

        foreach ($mahasiswaList as $mahasiswa) {
            $expected = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->count();

            $this->assertSame($expected, (int) $mahasiswa->krs_aktif_count);

            if ($mahasiswa->sks_kurikulum !== null) {
                $totalSksKrs = $mahasiswa->krs->sum(
                    fn ($krs) => (int) ($krs->kurikulum?->mataKuliah?->sks ?? 0)
                );
                $selisih = (int) $mahasiswa->sks_kurikulum - $totalSksKrs;

                $this->assertSame($selisih, (int) $mahasiswa->selisih_sks_kurikulum);

                if ($mahasiswa->krs->isNotEmpty() && $selisih > 0) {
                    $response->assertSee("Kurang {$selisih} SKS");
                } elseif ($mahasiswa->krs->isNotEmpty() && $selisih < 0) {
                    $response->assertSee('Lebih '.abs($selisih).' SKS');
                }
            }
        }

        if ($mahasiswaList->contains(fn ($mahasiswa) => $mahasiswa->krs_aktif_count > 0)) {
            $response->assertSee('Menunggu ACC');
        }

        if ($mahasiswaList->contains(fn ($mahasiswa) => $mahasiswa->krs_aktif_count === 0)) {
            $response->assertSee('Belum Diambil');
        }
    }

    public function test_dosen_cannot_open_another_advisors_student_transcript(): void
    {
        $advisor = Dosen::whereHas('mahasiswa')->firstOrFail();
        $mahasiswa = $advisor->mahasiswa()->firstOrFail();
        $dosenLain = Dosen::whereKeyNot($advisor->dosen_id)->firstOrFail();

        $this->actingAs($dosenLain, 'dosen')
            ->get(route('dosen.mahasiswa.transkrip', $mahasiswa))
            ->assertForbidden();
    }

    public function test_dosen_can_sort_and_filter_guidance_students(): void
    {
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $dosen = Dosen::whereHas('mahasiswa', fn ($query) => $query
            ->where('status_mhs', 'aktif')
            ->whereHas('krs', fn ($krs) => $krs->where('ta_id', $ta->ta_id)))
            ->firstOrFail();

        $sortedResponse = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.nilai-dosen.lihat', ['sort' => 'semester_desc']))
            ->assertOk()
            ->assertSee('Nama A - Z')
            ->assertSee('Semester Tertinggi')
            ->assertSee('Semua Status');

        $sortedStudents = collect($sortedResponse->viewData('mahasiswaList')->items());
        $this->assertSame(
            $sortedStudents->pluck('semester')->sortDesc()->values()->all(),
            $sortedStudents->pluck('semester')->values()->all()
        );

        $semester = (int) $sortedStudents->first()->semester;
        $semesterResponse = $this->get(route('dosen.nilai-dosen.lihat', [
            'semester' => $semester,
            'sort' => 'semester_asc',
        ]))->assertOk();

        foreach ($semesterResponse->viewData('mahasiswaList') as $mahasiswa) {
            $this->assertSame($semester, (int) $mahasiswa->semester);
        }

        $mahasiswaDisetujui = $dosen->mahasiswa()
            ->where('status_mhs', 'aktif')
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $ta->ta_id))
            ->firstOrFail();

        Krs::where('mahasiswa_id', $mahasiswaDisetujui->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->update([
                'disetujui_oleh' => $dosen->dosen_id,
                'disetujui_pada' => now(),
            ]);

        $approvedResponse = $this->get(route('dosen.nilai-dosen.lihat', [
            'status_krs' => 'disetujui',
        ]))->assertOk();

        $this->assertTrue(
            collect($approvedResponse->viewData('mahasiswaList')->items())
                ->contains('mahasiswa_id', $mahasiswaDisetujui->mahasiswa_id)
        );
    }
}
