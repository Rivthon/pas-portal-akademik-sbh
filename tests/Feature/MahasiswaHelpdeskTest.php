<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaHelpdeskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_ticket_goes_directly_to_admin_helpdesk(): void
    {
        $mahasiswa = Mahasiswa::query()->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.permintaan.store'), [
                'jenis_permintaan' => 'akses',
                'judul' => 'Tiket mahasiswa langsung',
                'deskripsi' => 'Tiket ini tidak menggunakan WhatsApp.',
                'prioritas' => 'sedang',
            ])
            ->assertOk()
            ->assertJson([
                'success' => true,
                'redirect_url' => route('mahasiswa.permintaan.index'),
            ]);

        $this->assertDatabaseHas('permintaan_perubahans', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'dosen_id' => null,
            'judul' => 'Tiket mahasiswa langsung',
            'status' => 'menunggu',
        ]);

        $admin = User::all()->first(fn (User $user) => $user->can('permintaan-list'));
        $this->assertNotNull($admin, 'Tidak ada admin dengan izin Helpdesk pada data pengujian.');

        $this->actingAs($admin)
            ->get(route('admin.helpdesk.index', ['search' => 'Tiket mahasiswa langsung']))
            ->assertOk()
            ->assertSee('Tiket mahasiswa langsung')
            ->assertSee('Mahasiswa');
    }
}
