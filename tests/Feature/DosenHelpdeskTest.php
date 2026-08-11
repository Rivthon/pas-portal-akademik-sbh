<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Permintaan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenHelpdeskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_open_helpdesk_and_create_a_ticket(): void
    {
        $dosen = Dosen::query()->firstOrFail();

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.permintaan.index'))
            ->assertOk()
            ->assertSee('Helpdesk Dosen');

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.permintaan.store'), [
                'jenis_permintaan' => 'bug',
                'judul' => 'Pengujian Helpdesk Dosen',
                'deskripsi' => 'Deskripsi tiket untuk pengujian.',
                'prioritas' => 'sedang',
            ])
            ->assertRedirect(route('dosen.permintaan.index'));

        $this->assertDatabaseHas('permintaan_perubahans', [
            'dosen_id' => $dosen->dosen_id,
            'mahasiswa_id' => null,
            'judul' => 'Pengujian Helpdesk Dosen',
            'status' => 'menunggu',
        ]);
    }

    public function test_dosen_cannot_delete_another_dosens_ticket(): void
    {
        $dosen = Dosen::query()->firstOrFail();
        $dosenLain = Dosen::query()->whereKeyNot($dosen->getKey())->firstOrFail();
        $ticket = Permintaan::create([
            'dosen_id' => $dosenLain->dosen_id,
            'jenis_permintaan' => 'akses',
            'judul' => 'Tiket dosen lain',
            'deskripsi' => 'Tidak boleh dihapus dosen lain.',
            'prioritas' => 'rendah',
            'status' => 'menunggu',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->delete(route('dosen.permintaan.destroy', $ticket->id))
            ->assertNotFound();

        $this->assertDatabaseHas('permintaan_perubahans', ['id' => $ticket->id]);
    }

    public function test_admin_can_see_and_process_a_dosen_ticket(): void
    {
        $admin = User::all()->first(fn (User $user) => $user->can('permintaan-list')
            && $user->can('permintaan-edit'));
        $this->assertNotNull($admin, 'Tidak ada admin dengan izin Helpdesk pada data pengujian.');

        $dosen = Dosen::query()->firstOrFail();
        $ticket = Permintaan::create([
            'dosen_id' => $dosen->dosen_id,
            'jenis_permintaan' => 'fitur',
            'judul' => 'Tiket integrasi Admin',
            'deskripsi' => 'Harus terlihat dan dapat diproses Admin.',
            'prioritas' => 'sedang',
            'status' => 'menunggu',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.helpdesk.index', ['search' => $dosen->nama]))
            ->assertOk()
            ->assertSee('Tiket integrasi Admin')
            ->assertSee('Dosen');

        $this->actingAs($admin)
            ->put(route('admin.helpdesk.updateStatus', $ticket->id), [
                'status' => 'selesai',
                'komentar_admin' => 'Sudah ditangani.',
            ])
            ->assertRedirect(route('admin.helpdesk.index'));

        $this->assertDatabaseHas('permintaan_perubahans', [
            'id' => $ticket->id,
            'status' => 'selesai',
            'komentar_admin' => 'Sudah ditangani.',
        ]);
    }
}
