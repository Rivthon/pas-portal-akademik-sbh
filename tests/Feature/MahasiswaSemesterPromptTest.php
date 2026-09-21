<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaSemesterPromptTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dashboard_shows_clear_semester_confirmation_prompt(): void
    {
        $mahasiswa = Mahasiswa::query()->where('status_mhs', 'aktif')->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Konfirmasi Semester Perkuliahan')
            ->assertSee('mata kuliah yang diikuti tetap mengikuti KRS');
    }

    public function test_semester_selection_only_accepts_semester_one_through_eight(): void
    {
        $mahasiswa = Mahasiswa::query()->where('status_mhs', 'aktif')->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->from(route('mahasiswa.dashboard'))
            ->post(route('mahasiswa.semester.update'), ['semester' => 9])
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHasErrors('semester');
    }

    public function test_dashboard_shows_persistent_warning_when_semester_parity_does_not_match_active_term(): void
    {
        $tahunAkademik = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();
        $mahasiswa = Mahasiswa::query()->where('status_mhs', 'aktif')->firstOrFail();
        $semesterTidakSesuai = strtolower((string) $tahunAkademik->semester) === 'ganjil' ? 2 : 1;
        $mahasiswa->update(['semester' => $semesterTidakSesuai]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Semester Anda tidak sesuai dengan tahun ajaran aktif!')
            ->assertSee('Perbaiki Semester Sekarang');
    }
}
