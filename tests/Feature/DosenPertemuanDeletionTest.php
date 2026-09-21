<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenPertemuanDeletionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_delete_a_meeting_they_created(): void
    {
        [$dosen, $jadwal] = $this->assignedTheorySchedule();
        $pertemuan = $this->createMeeting($jadwal, $dosen->dosen_id);

        $this->actingAs($dosen, 'dosen')
            ->deleteJson(route('dosen.pertemuan.destroy', $pertemuan))
            ->assertOk()
            ->assertJsonPath('message', 'Pertemuan dan data absensi terkait berhasil dihapus.');

        $this->assertDatabaseMissing('pertemuan', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
        ]);
    }

    public function test_dosen_cannot_delete_a_meeting_created_by_another_lecturer(): void
    {
        [$dosen, $jadwal] = $this->assignedTheorySchedule();
        $dosenLain = Dosen::query()
            ->whereKeyNot($dosen->dosen_id)
            ->firstOrFail();
        $pertemuan = $this->createMeeting($jadwal, $dosenLain->dosen_id);

        $this->actingAs($dosen, 'dosen')
            ->deleteJson(route('dosen.pertemuan.destroy', $pertemuan))
            ->assertForbidden();

        $this->assertDatabaseHas('pertemuan', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
        ]);
    }

    public function test_meeting_list_marks_only_the_creators_meeting_as_deletable(): void
    {
        [$dosen, $jadwal] = $this->assignedTheorySchedule();
        $milikDosen = $this->createMeeting($jadwal, $dosen->dosen_id);

        $response = $this->actingAs($dosen, 'dosen')
            ->getJson(route('dosen.pertemuan.list', $jadwal->id))
            ->assertOk();

        $item = collect($response->json())->firstWhere('pertemuan_id', $milikDosen->pertemuan_id);

        $this->assertNotNull($item);
        $this->assertTrue($item['can_delete']);
        $this->assertArrayHasKey('absensi_count', $item);
    }

    public function test_dosen_can_update_their_meeting_and_attendance_date_stays_in_sync(): void
    {
        [$dosen, $jadwal] = $this->assignedTheorySchedule();
        $pertemuan = $this->createMeeting($jadwal, $dosen->dosen_id);
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $absensi = Absensi::create([
            'jadwal_id' => $jadwal->id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);
        $tanggalBaru = now()->addDay()->toDateString();

        $this->actingAs($dosen, 'dosen')
            ->putJson(route('dosen.pertemuan.update', $pertemuan), [
                'tanggal_pertemuan' => $tanggalBaru,
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:30',
                'metode_pbm' => 'online',
                'topik' => 'Topik pertemuan diperbarui',
                'sub_topik' => 'Subtopik pertemuan diperbarui',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Pertemuan berhasil diperbarui.');

        $this->assertDatabaseHas('pertemuan', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'tanggal_pertemuan' => $tanggalBaru,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'metode_pbm' => 'online',
            'topik' => 'Topik pertemuan diperbarui',
        ]);
        $this->assertDatabaseHas('absensi', [
            'absensi_id' => $absensi->absensi_id,
            'tanggal' => $tanggalBaru,
        ]);
    }

    public function test_dosen_cannot_update_a_meeting_created_by_another_lecturer(): void
    {
        [$dosen, $jadwal] = $this->assignedTheorySchedule();
        $dosenLain = Dosen::query()
            ->whereKeyNot($dosen->dosen_id)
            ->firstOrFail();
        $pertemuan = $this->createMeeting($jadwal, $dosenLain->dosen_id);

        $this->actingAs($dosen, 'dosen')
            ->putJson(route('dosen.pertemuan.update', $pertemuan), [
                'tanggal_pertemuan' => now()->toDateString(),
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:30',
                'metode_pbm' => 'offline',
                'topik' => 'Perubahan tidak sah',
                'sub_topik' => 'Perubahan tidak sah',
            ])
            ->assertForbidden();

        $this->assertNotSame('Perubahan tidak sah', $pertemuan->fresh()->topik);
    }

    private function assignedTheorySchedule(): array
    {
        $activeTaId = TahunAkademik::query()
            ->where('status_ta', 1)
            ->value('ta_id');

        $this->assertNotNull($activeTaId, 'Data uji membutuhkan Tahun Akademik aktif.');

        $dosen = Dosen::findOrFail(22);
        $jadwal = Jadwal::query()
            ->where('ta_id', $activeTaId)
            ->assignedToDosen($dosen->dosen_id, 'teori')
            ->firstOrFail();

        return [$dosen, $jadwal];
    }

    private function createMeeting(Jadwal $jadwal, int $dosenId): Pertemuan
    {
        return Pertemuan::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:00:00',
            'metode_pbm' => 'offline',
            'topik' => 'Pertemuan uji hapus '.uniqid(),
            'sub_topik' => 'Data pengujian otomatis',
            'dosen_id' => $dosenId,
            'status' => 1,
        ]);
    }
}
