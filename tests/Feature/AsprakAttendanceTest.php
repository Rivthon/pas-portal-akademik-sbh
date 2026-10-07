<?php

namespace Tests\Feature;

use App\Models\AsprakAssignment;
use App\Models\AsprakAttendance;
use App\Models\Dosen;
use App\Models\JadwalPraktik;
use App\Models\Mahasiswa;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AsprakAttendanceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_assign_attend_and_student_can_view_asprak_recap(): void
    {
        [$jadwal, $dosen, $mahasiswa, $otherStudent] = $this->context();

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi-praktik.asprak.manage', $jadwal))
            ->assertOk()
            ->assertSee('Kelola Asprak')
            ->assertSee($mahasiswa->nim);

        $this->put(route('dosen.absensi-praktik.asprak.update', $jadwal), [
            'mahasiswa_ids' => [$mahasiswa->mahasiswa_id],
        ])->assertRedirect(route('dosen.absensi-praktik.index'));

        $assignment = AsprakAssignment::where('jadwal_praktik_id', $jadwal->id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->firstOrFail();
        $this->assertTrue($assignment->aktif);

        $this->post(route('dosen.absensi-praktik.pertemuan.store'), [
            'jadwal_praktik_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '10:00',
            'metode_pbm' => 'offline',
            'topik' => 'Pertemuan Uji Asprak',
            'sub_topik' => 'Pengujian rekap kehadiran',
            'asprak_ids' => [$assignment->id],
        ])->assertRedirect();

        $meeting = PertemuanPraktik::where('jadwal_praktik_id', $jadwal->id)
            ->where('topik', 'Pertemuan Uji Asprak')
            ->latest('pertemuan_praktik_id')
            ->firstOrFail();
        $attendance = AsprakAttendance::where('pertemuan_praktik_id', $meeting->pertemuan_praktik_id)
            ->where('asprak_penugasan_id', $assignment->id)
            ->firstOrFail();
        $this->assertSame('belum diabsen', $attendance->status);

        $this->get(route('dosen.absensi-praktik.show', $meeting))
            ->assertOk()
            ->assertSee($mahasiswa->nama)
            ->assertSee('Kehadiran Asprak');

        $this->put(route('dosen.absensi-praktik.asprak-attendance.update', $meeting), [
            'status_asprak' => [$attendance->id => 'hadir'],
            'keterangan_asprak' => [$attendance->id => 'Mendampingi praktikum'],
        ])->assertRedirect();
        $this->assertDatabaseHas('asprak_absensi', [
            'id' => $attendance->id,
            'status' => 'hadir',
            'diabsen_oleh_dosen_id' => $dosen->dosen_id,
        ]);

        auth('dosen')->logout();
        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.asprak.index'))
            ->assertOk()
            ->assertSee('Rekap Absensi Asprak')
            ->assertSee('Pertemuan Uji Asprak')
            ->assertSee('Mendampingi praktikum');

        auth('mahasiswa')->logout();
        $this->actingAs($otherStudent, 'mahasiswa')
            ->get(route('mahasiswa.asprak.index'))
            ->assertForbidden();
    }

    public function test_unrelated_dosen_cannot_manage_another_dosens_practice_asprak(): void
    {
        [$jadwal, $dosen] = $this->context();
        $otherDosen = Dosen::where('dosen_id', '!=', $dosen->dosen_id)->firstOrFail();

        $this->actingAs($otherDosen, 'dosen')
            ->get(route('dosen.absensi-praktik.asprak.manage', $jadwal))
            ->assertNotFound();
    }

    private function context(): array
    {
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        foreach (JadwalPraktik::with(['kurikulum.dosenToMatakuliah.dosen'])->where('ta_id', $activeTaId)->get() as $jadwal) {
            $assignment = $jadwal->kurikulum?->dosenToMatakuliah
                ?->first(fn ($item) => strtolower((string) $item->jenis_dosen) === 'praktik'
                    && KrsClassResolver::normalize($item->jenis_kelas) === KrsClassResolver::normalize($jadwal->jenis_kelas)
                    && $item->dosen);
            if (! $assignment) {
                continue;
            }
            $jurusanId = $jadwal->jurusan_id ?: $jadwal->kurikulum?->jurusan_id;
            $students = Mahasiswa::where('jurusan_id', $jurusanId)
                ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
                ->take(2)
                ->get();
            if ($students->count() >= 2) {
                return [$jadwal, $assignment->dosen, $students[0], $students[1]];
            }
        }

        $this->markTestSkipped('Tidak ada jadwal praktik aktif dengan dosen dan dua mahasiswa aktif yang sesuai.');
    }
}
