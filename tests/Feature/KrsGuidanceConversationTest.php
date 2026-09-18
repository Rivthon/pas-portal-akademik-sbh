<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Krs;
use App\Models\KrsGuidanceMessage;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class KrsGuidanceConversationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_advisor_and_student_can_exchange_krs_messages(): void
    {
        $tahunAktif = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $mahasiswa = Mahasiswa::where('status_mhs', 'aktif')
            ->whereNotNull('dosen_id')
            ->whereHas('dosen')
            ->firstOrFail();
        $mahasiswa->update(['status_krs' => 1]);
        $dosen = Dosen::findOrFail($mahasiswa->dosen_id);
        Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $tahunAktif->ta_id)
            ->update(['disetujui_oleh' => null, 'disetujui_pada' => null]);
        $komentarDosen = 'Jumlah SKS masih kurang '.uniqid();
        $balasanMahasiswa = 'Sudah diperbaiki, mohon diperiksa kembali '.uniqid();

        $this->actingAs($dosen, 'dosen')->post(route('dosen.mahasiswa.guidance.store', $mahasiswa), [
            'message' => $komentarDosen,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('krs_guidance_messages', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'dosen_id' => $dosen->dosen_id,
            'ta_id' => $tahunAktif->ta_id,
            'sender_type' => 'dosen',
            'message' => $komentarDosen,
        ]);

        $this->actingAs($mahasiswa, 'mahasiswa')->get(route('mahasiswa.status.krs.index'))
            ->assertOk()
            ->assertSee($komentarDosen)
            ->assertSee('Kirim Umpan Balik');

        $this->actingAs($mahasiswa, 'mahasiswa')->post(route('mahasiswa.status.krs.comment.store'), [
            'message' => $balasanMahasiswa,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('krs_guidance_messages', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'dosen_id' => $dosen->dosen_id,
            'ta_id' => $tahunAktif->ta_id,
            'sender_type' => 'mahasiswa',
            'message' => $balasanMahasiswa,
        ]);

        $this->actingAs($dosen, 'dosen')->get(route('dosen.nilai-dosen.lihat'))
            ->assertOk()
            ->assertSee($balasanMahasiswa)
            ->assertSee('Diskusi KRS');
    }

    public function test_other_dosen_cannot_message_students_outside_their_guidance(): void
    {
        $mahasiswa = Mahasiswa::where('status_mhs', 'aktif')
            ->whereNotNull('dosen_id')
            ->whereHas('dosen')
            ->firstOrFail();
        $dosenLain = Dosen::where('dosen_id', '!=', $mahasiswa->dosen_id)->firstOrFail();

        $this->actingAs($dosenLain, 'dosen')->post(route('dosen.mahasiswa.guidance.store', $mahasiswa), [
            'message' => 'Pesan yang tidak diizinkan',
        ])->assertForbidden();

        $this->assertFalse(KrsGuidanceMessage::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('dosen_id', $dosenLain->dosen_id)
            ->where('message', 'Pesan yang tidak diizinkan')
            ->exists());
    }

    public function test_discussion_is_locked_after_approval_and_reopens_after_cancellation(): void
    {
        $tahunAktif = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $mahasiswa = Mahasiswa::where('status_mhs', 'aktif')
            ->whereNotNull('dosen_id')
            ->whereHas('dosen')
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $tahunAktif->ta_id))
            ->firstOrFail();
        $dosen = Dosen::findOrFail($mahasiswa->dosen_id);
        $pesanTerkunciDosen = 'Pesan dosen setelah ACC '.uniqid();
        $pesanTerkunciMahasiswa = 'Pesan mahasiswa setelah ACC '.uniqid();

        Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $tahunAktif->ta_id)
            ->update([
                'disetujui_oleh' => $dosen->dosen_id,
                'disetujui_pada' => now(),
            ]);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.guidance.store', $mahasiswa), ['message' => $pesanTerkunciDosen])
            ->assertSessionHas('error');
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->post(route('mahasiswa.status.krs.comment.store'), ['message' => $pesanTerkunciMahasiswa])
            ->assertSessionHas('error');

        $this->assertDatabaseMissing('krs_guidance_messages', ['message' => $pesanTerkunciDosen]);
        $this->assertDatabaseMissing('krs_guidance_messages', ['message' => $pesanTerkunciMahasiswa]);
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.status.krs.index'))
            ->assertOk()
            ->assertSee('Diskusi KRS telah ditutup');
        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.mahasiswa.krs.show', $mahasiswa))
            ->assertOk()
            ->assertSee('Diskusi KRS telah ditutup');

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.krs.cancel-approval', $mahasiswa))
            ->assertSessionHas('success');

        $pesanDibuka = 'Forum aktif kembali '.uniqid();
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->post(route('mahasiswa.status.krs.comment.store'), ['message' => $pesanDibuka])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('krs_guidance_messages', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'ta_id' => $tahunAktif->ta_id,
            'message' => $pesanDibuka,
        ]);
    }
}
