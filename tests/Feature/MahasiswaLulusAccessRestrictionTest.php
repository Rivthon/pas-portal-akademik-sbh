<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaLulusAccessRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_lulus_masuk_mode_arsip_dan_fitur_semester_aktif_dikunci(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'lulus']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Status Akademik: Lulus')
            ->assertDontSee('semesterModal')
            ->assertDontSee('Materi baru:');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.khs.riwayat'))
            ->assertSessionMissing('graduate_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.krs.index'))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('graduate_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->getJson(route('mahasiswa.lms.index'))
            ->assertForbidden()
            ->assertJsonPath('status', 'lulus');
    }

    public function test_mahasiswa_lulus_tidak_boleh_mengubah_data_akademik_melalui_api(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'lulus']);

        $this->actingAs($mahasiswa, 'sanctum')
            ->putJson('/api/v1/mahasiswa/krs', ['kurikulum_ids' => []])
            ->assertForbidden()
            ->assertJsonPath('status', 'lulus');

        $this->actingAs($mahasiswa, 'sanctum')
            ->getJson('/api/v1/mahasiswa/dashboard')
            ->assertOk()
            ->assertJsonPath('ringkasan.jumlah_kelas_lms', 0);
    }
}
