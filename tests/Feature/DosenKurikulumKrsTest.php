<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenKurikulumKrsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_view_active_curriculum_grouped_by_semester(): void
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignment = DosenMatakuliah::with('kurikulum')
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTa->ta_id))
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $prodiId = (string) $assignment->kurikulum->jurusan_id;

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.kurikulum-krs.index', ['prodi' => $prodiId]))
            ->assertOk()
            ->assertSee('Kurikulum KRS Mahasiswa')
            ->assertSee('Kurikulum KRS')
            ->assertSee('Jenis Kelas')
            ->assertSee('Mata Kuliah Wajib')
            ->assertSee('Mata Kuliah Pilihan');

        $groups = $response->viewData('kurikulumPerSemester');
        $this->assertNotEmpty($groups);

        foreach ($groups as $semester => $items) {
            foreach ($items as $item) {
                $this->assertSame($activeTa->ta_id, (int) $item->ta_id);
                $this->assertSame($prodiId, (string) $item->jurusan_id);
                $this->assertSame((int) $semester, (int) $item->mataKuliah->smt);
            }
        }
    }

    public function test_class_filter_only_returns_curriculum_offered_for_selected_class(): void
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignment = DosenMatakuliah::with('kurikulum')
            ->whereIn('jenis_kelas', ['reguler', 'karyawan'])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTa->ta_id))
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $kelas = strtolower($assignment->jenis_kelas);

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.kurikulum-krs.index', [
                'prodi' => $assignment->kurikulum->jurusan_id,
                'jenis_kelas' => $kelas,
            ]))
            ->assertOk();

        foreach ($response->viewData('kurikulumPerSemester')->flatten(1) as $item) {
            $this->assertTrue($item->kelas_tersedia->contains($kelas));
        }
    }
}
