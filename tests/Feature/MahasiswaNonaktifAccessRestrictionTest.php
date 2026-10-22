<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaNonaktifAccessRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_nonaktif_hanya_dapat_membuka_profil_dan_logout(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'nonaktif']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.dashboard'))
            ->assertRedirect(route('mahasiswa.profile.index'))
            ->assertSessionHas('inactive_notice');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.profile.index'))
            ->assertOk()
            ->assertSee('Status Nonaktif')
            ->assertSee('Edit Profil')
            ->assertDontSee('Dashboard Utama');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->getJson(route('mahasiswa.lms.index'))
            ->assertStatus(423)
            ->assertJsonPath('status', 'nonaktif');
    }

    public function test_mahasiswa_nonaktif_tidak_dapat_mengaktifkan_diri_dari_form_profil(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'nonaktif']);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->post(route('mahasiswa.profile.update'), [
                'nama' => 'Nama Profil Nonaktif',
                'email' => 'nonaktif-'.$mahasiswa->mahasiswa_id.'@example.test',
                'nisn' => filled($mahasiswa->nisn) ? (string) $mahasiswa->nisn : null,
                'nik' => filled($mahasiswa->nik) ? (string) $mahasiswa->nik : null,
                'jenis_kelamin' => in_array($mahasiswa->jenis_kelamin, ['Laki-Laki', 'Perempuan'], true)
                    ? $mahasiswa->jenis_kelamin
                    : 'Laki-Laki',
                'tanggal_lahir' => $mahasiswa->tanggal_lahir ?: '2000-01-01',
                'alamat' => $mahasiswa->alamat ?: 'Alamat mahasiswa',
                'nama_ayah' => $mahasiswa->nama_ayah ?: 'Ayah',
                'nama_ibu' => $mahasiswa->nama_ibu ?: 'Ibu',
                'no_telp_ortu' => filled($mahasiswa->no_telp_ortu) ? (string) $mahasiswa->no_telp_ortu : '08123456789',
                'pendapatan_ortu' => filled($mahasiswa->pendapatan_ortu) ? (string) $mahasiswa->pendapatan_ortu : '0',
                'alamat_ortu' => $mahasiswa->alamat_ortu ?: 'Alamat orang tua',
                'status_mhs' => 'aktif',
            ])
            ->assertRedirect(route('mahasiswa.profile.index'));

        $mahasiswa->refresh();
        $this->assertSame('nonaktif', strtolower((string) $mahasiswa->status_mhs));
        $this->assertSame('Nama Profil Nonaktif', $mahasiswa->nama);
    }

    public function test_api_nonaktif_hanya_mengizinkan_identitas_profil_dan_logout(): void
    {
        $mahasiswa = Mahasiswa::firstOrFail();
        $mahasiswa->update(['status_mhs' => 'nonaktif']);

        $this->actingAs($mahasiswa, 'sanctum')
            ->getJson('/api/v1/mahasiswa/me')
            ->assertOk();

        $this->actingAs($mahasiswa, 'sanctum')
            ->getJson('/api/v1/mahasiswa/pelayanan/profil')
            ->assertOk();

        $this->actingAs($mahasiswa, 'sanctum')
            ->getJson('/api/v1/mahasiswa/dashboard')
            ->assertStatus(423)
            ->assertJsonPath('status', 'nonaktif');
    }
}
