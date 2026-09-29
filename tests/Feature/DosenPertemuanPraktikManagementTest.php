<?php

namespace Tests\Feature;

use App\Models\AbsensiPraktik;
use App\Models\Dosen;
use App\Models\JadwalPraktik;
use App\Models\Mahasiswa;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenPertemuanPraktikManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_practice_index_is_grouped_by_semester_and_has_course_filters(): void
    {
        [$dosen, $jadwal] = $this->assignedPracticeSchedule();
        $semester = (int) ($jadwal->kurikulum?->mataKuliah?->smt
            ?: $jadwal->kurikulum?->mataKuliah?->semester);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi-praktik.index'))
            ->assertOk()
            ->assertSee('semesterPraktikAccordion', false)
            ->assertSee('Semester '.$semester)
            ->assertSee('filterPraktikSearch', false)
            ->assertSee('filterPraktikKelas', false)
            ->assertSee('Kelola Pertemuan &amp; Absensi', false);
    }

    public function test_dosen_can_update_their_practice_meeting_and_attendance_date(): void
    {
        [$dosen, $jadwal] = $this->assignedPracticeSchedule();
        $pertemuan = $this->createMeeting($jadwal, $dosen->dosen_id);
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $absensi = AbsensiPraktik::create([
            'jadwal_praktik_id' => $jadwal->id,
            'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'tanggal' => now()->toDateString(),
            'status' => 'hadir',
        ]);
        $tanggalBaru = now()->addDay()->toDateString();

        $this->actingAs($dosen, 'dosen')
            ->putJson(route('dosen.absensi-praktik.pertemuan.update', $pertemuan), [
                'tanggal_pertemuan' => $tanggalBaru,
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:30',
                'metode_pbm' => 'online',
                'topik' => 'Topik praktik diperbarui',
                'sub_topik' => 'Subtopik praktik diperbarui',
            ])
            ->assertOk()
            ->assertJsonPath('message', 'Pertemuan praktik berhasil diperbarui.');

        $this->assertDatabaseHas('pertemuan_praktik', [
            'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
            'tanggal_pertemuan' => $tanggalBaru,
            'jam_mulai' => '09:00:00',
            'jam_selesai' => '10:30:00',
            'metode_pbm' => 'online',
            'topik' => 'Topik praktik diperbarui',
        ]);
        $this->assertDatabaseHas('absensi_praktik', [
            'absensi_praktik_id' => $absensi->absensi_praktik_id,
            'tanggal' => $tanggalBaru,
        ]);
    }

    public function test_dosen_can_delete_their_practice_meeting_with_attendance(): void
    {
        [$dosen, $jadwal] = $this->assignedPracticeSchedule();
        $pertemuan = $this->createMeeting($jadwal, $dosen->dosen_id);
        $mahasiswa = Mahasiswa::query()->firstOrFail();
        $absensi = AbsensiPraktik::create([
            'jadwal_praktik_id' => $jadwal->id,
            'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'tanggal' => now()->toDateString(),
            'status' => 'belum diabsen',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->deleteJson(route('dosen.absensi-praktik.pertemuan.destroy', $pertemuan))
            ->assertOk()
            ->assertJsonPath('message', 'Pertemuan praktik dan data absensi terkait berhasil dihapus.');

        $this->assertDatabaseMissing('pertemuan_praktik', [
            'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
        ]);
        $this->assertDatabaseMissing('absensi_praktik', [
            'absensi_praktik_id' => $absensi->absensi_praktik_id,
        ]);
    }

    public function test_dosen_cannot_manage_practice_meeting_created_by_another_lecturer(): void
    {
        [$dosen, $jadwal] = $this->assignedPracticeSchedule();
        $dosenLain = Dosen::query()->whereKeyNot($dosen->dosen_id)->firstOrFail();
        $pertemuan = $this->createMeeting($jadwal, $dosenLain->dosen_id);

        $this->actingAs($dosen, 'dosen')
            ->putJson(route('dosen.absensi-praktik.pertemuan.update', $pertemuan), [
                'tanggal_pertemuan' => now()->toDateString(),
                'jam_mulai' => '09:00',
                'jam_selesai' => '10:30',
                'metode_pbm' => 'offline',
                'topik' => 'Perubahan tidak sah',
                'sub_topik' => 'Perubahan tidak sah',
            ])
            ->assertForbidden();

        $this->actingAs($dosen, 'dosen')
            ->deleteJson(route('dosen.absensi-praktik.pertemuan.destroy', $pertemuan))
            ->assertForbidden();

        $this->assertDatabaseHas('pertemuan_praktik', [
            'pertemuan_praktik_id' => $pertemuan->pertemuan_praktik_id,
        ]);
    }

    private function assignedPracticeSchedule(): array
    {
        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $this->assertNotNull($activeTaId, 'Data uji membutuhkan Tahun Akademik aktif.');

        $jadwal = JadwalPraktik::with('kurikulum.dosenToMatakuliah')
            ->where('ta_id', $activeTaId)
            ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)'))
            ->firstOrFail();
        $assignment = $jadwal->kurikulum->dosenToMatakuliah
            ->first(fn ($item) => strtolower((string) $item->jenis_dosen) === 'praktik'
                && strtolower((string) $item->jenis_kelas) === strtolower((string) $jadwal->jenis_kelas));
        $this->assertNotNull($assignment);

        return [Dosen::findOrFail($assignment->dosen_id), $jadwal];
    }

    private function createMeeting(JadwalPraktik $jadwal, int $dosenId): PertemuanPraktik
    {
        return PertemuanPraktik::create([
            'jadwal_praktik_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'metode_pbm' => 'offline',
            'topik' => 'Pertemuan praktik uji '.uniqid(),
            'sub_topik' => 'Data pengujian otomatis',
            'dosen_id' => $dosenId,
        ]);
    }
}
