<?php

namespace Tests\Feature;

use App\Models\AbsensiPraktik;
use App\Models\JadwalPraktik;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaAbsensiPraktikHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_history_only_lists_active_krs_courses_with_matching_practice_class(): void
    {
        $mahasiswa = Mahasiswa::where('nama', 'Nanda Widia')->firstOrFail();
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();

        $response = $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.absensi-praktik.index'))
            ->assertOk()
            ->assertSee('mata kuliah di KRS Anda yang memiliki kelas praktik');

        $jadwal = $response->viewData('jadwal');
        foreach ($jadwal as $item) {
            $this->assertSame($activeTa->ta_id, $item->ta_id);
            $this->assertTrue($item->kurikulum->krs()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $activeTa->ta_id)
                ->exists());
            $this->assertTrue($item->kurikulum->dosenToMatakuliah()
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($item->jenis_kelas)])
                ->exists());
        }

        $theoryOnlySchedule = JadwalPraktik::where('ta_id', $activeTa->ta_id)
            ->whereHas('kurikulum.krs', fn ($query) => $query
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $activeTa->ta_id))
            ->whereDoesntHave('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik']))
            ->first();

        if ($theoryOnlySchedule) {
            $this->assertFalse($jadwal->contains('id', $theoryOnlySchedule->id));
        }
    }

    public function test_student_can_only_open_practice_meetings_with_own_attendance_record(): void
    {
        $absensi = AbsensiPraktik::whereHas('jadwal.kurikulum.mataKuliah', fn ($query) => $query
            ->where('nama', 'Mikrobiologi Pangan'))
            ->with(['mahasiswa', 'jadwal.kurikulum.mataKuliah'])
            ->firstOrFail();

        $mahasiswa = $absensi->mahasiswa;
        $jadwal = $absensi->jadwal;

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.absensi-praktik.index'))
            ->assertOk()
            ->assertSee('Mikrobiologi Pangan');

        $detail = $this->get(route('mahasiswa.absensi-praktik.show', $jadwal))
            ->assertOk();

        foreach ($detail->viewData('pertemuan') as $pertemuan) {
            $this->assertTrue(
                $pertemuan->absensi->contains('mahasiswa_id', $mahasiswa->mahasiswa_id)
            );
        }

        $nanda = Mahasiswa::where('nama', 'Nanda Widia')->firstOrFail();
        $this->actingAs($nanda, 'mahasiswa')
            ->get(route('mahasiswa.absensi-praktik.show', $jadwal))
            ->assertForbidden();
    }
}
