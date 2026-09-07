<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaExamGradeHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_view_only_uts_and_uas_from_a_previous_academic_year(): void
    {
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        $krs = Krs::with(['mahasiswa', 'kurikulum.mataKuliah', 'tahunAjaran'])
            ->where('ta_id', '!=', $activeTaId)
            ->whereHas('mahasiswa')
            ->whereHas('kurikulum.mataKuliah')
            ->where(function ($query) {
                $query->whereNotNull('uts')->orWhereNotNull('uas');
            })
            ->firstOrFail();

        $response = $this->actingAs($krs->mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.nilai-ujian.riwayat', ['ta_id' => $krs->ta_id]));

        $response->assertOk()
            ->assertSee('Riwayat Nilai UTS')
            ->assertSee($krs->tahunAjaran->nama)
            ->assertSee($krs->kurikulum->mataKuliah->nama)
            ->assertSee('UTS')
            ->assertSee('UAS')
            ->assertSee('Nilai absolut, huruf mutu, dan IP tidak ditampilkan');
    }

    public function test_active_academic_year_cannot_be_selected_as_exam_grade_history(): void
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $historicalKrs = Krs::with('mahasiswa')
            ->where('ta_id', '!=', $activeTa->ta_id)
            ->whereHas('mahasiswa')
            ->where(function ($query) {
                $query->whereNotNull('uts')->orWhereNotNull('uas');
            })
            ->firstOrFail();

        $response = $this->actingAs($historicalKrs->mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.nilai-ujian.riwayat', ['ta_id' => $activeTa->ta_id]));

        $response->assertOk()
            ->assertDontSee('<option value="'.$activeTa->ta_id.'" selected', false);
    }
}
