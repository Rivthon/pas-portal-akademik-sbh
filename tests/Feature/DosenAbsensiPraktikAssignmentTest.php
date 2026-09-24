<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\JadwalPraktik;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenAbsensiPraktikAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_existing_practice_assignment_is_synchronized_and_visible_for_dosen(): void
    {
        $dosen = Dosen::findOrFail(45);

        JadwalPraktik::query()
            ->where('ta_id', 18)
            ->where('kurikulum_id', 921)
            ->delete();

        $response = $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi-praktik.index'));

        $response->assertOk()
            ->assertSee('Analisis Sediaan Farmasi')
            ->assertSee('Reguler A')
            ->assertSee('Reguler B')
            ->assertSee('Semester Mata Kuliah')
            ->assertSee('Program Studi')
            ->assertSee('Jenis Kelas')
            ->assertSee('Ruangan')
            ->assertSee('Tahun Akademik');

        $this->assertDatabaseHas('jadwal_praktik', [
            'ta_id' => 18,
            'kurikulum_id' => 921,
            'jenis_kelas' => 'reguler',
        ]);
        $this->assertDatabaseHas('jadwal_praktik', [
            'ta_id' => 18,
            'kurikulum_id' => 921,
            'jenis_kelas' => 'karyawan',
        ]);
    }

    public function test_practice_roster_shows_krs_status_and_rejects_unapproved_student(): void
    {
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        $jadwal = JadwalPraktik::with(['kurikulum.mataKuliah', 'kurikulum.dosenToMatakuliah'])
            ->where('ta_id', $activeTaId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) {
                $query->whereRaw('LOWER(jenis_dosen) = ?', ['praktik'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal_praktik.jenis_kelas)');
            })
            ->firstOrFail();
        $assignment = $jadwal->kurikulum->dosenToMatakuliah
            ->first(fn ($item) => strtolower((string) $item->jenis_dosen) === 'praktik'
                && strtolower((string) $item->jenis_kelas) === strtolower((string) $jadwal->jenis_kelas));
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $semesterMatkul = (int) ($jadwal->kurikulum->mataKuliah->smt
            ?: $jadwal->kurikulum->mataKuliah->semester);
        $mahasiswa = Mahasiswa::whereNotIn('mahasiswa_id', Krs::query()
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->pluck('mahasiswa_id'))
            ->firstOrFail();
        $mahasiswa->update([
            'jurusan_id' => $jadwal->jurusan_id ?: $jadwal->kurikulum->jurusan_id,
            'semester' => $semesterMatkul,
            'kelas' => strtolower((string) $jadwal->jenis_kelas) === 'karyawan' ? 'karyawan' : 'pagi',
            'status_mhs' => 'aktif',
        ]);
        Krs::create([
            'kurikulum_id' => $jadwal->kurikulum_id,
            'matakuliah_id' => $jadwal->kurikulum->matakuliah_id,
            'ta_id' => $jadwal->ta_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'jenis_kelas' => $jadwal->jenis_kelas,
            'disetujui_pada' => null,
            'disetujui_oleh' => null,
        ]);
        $pertemuan = PertemuanPraktik::create([
            'jadwal_praktik_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00',
            'jam_selesai' => '09:30',
            'metode_pbm' => 'offline',
            'topik' => 'Uji status KRS praktik',
            'sub_topik' => 'Peserta terkunci',
            'dosen_id' => $dosen->dosen_id,
        ]);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi-praktik.show', $pertemuan))
            ->assertOk()
            ->assertSee($mahasiswa->nama)
            ->assertSee('Menunggu ACC Dospem');

        $this->put(route('dosen.absensi-praktik.update', $pertemuan), [
            'status' => [$mahasiswa->mahasiswa_id => 'hadir'],
            'keterangan' => [],
        ])->assertStatus(422);
    }
}
