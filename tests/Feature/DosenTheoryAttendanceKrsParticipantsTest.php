<?php

namespace Tests\Feature;

use App\Models\Absensi;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DosenTheoryAttendanceKrsParticipantsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_new_meeting_uses_approved_krs_when_profile_semester_has_matching_parity(): void
    {
        [$dosen, $jadwal, $pesertaKrs] = $this->scheduleWithApprovedParticipants(2);
        $mahasiswa = $pesertaKrs->first();
        $periodeGanjil = strtolower((string) $jadwal->tahunAjaran->semester) === 'ganjil';

        $mahasiswa->update([
            'semester' => $periodeGanjil ? 7 : 8,
        ]);

        $response = $this->actingAs($dosen, 'dosen')
            ->postJson(route('dosen.absensi.store'), [
                'jadwal_id' => $jadwal->id,
                'tanggal_pertemuan' => now()->toDateString(),
                'jam_mulai' => '08:00',
                'jam_selesai' => '09:30',
                'metode_pbm' => 'offline',
                'topik' => 'Uji peserta berdasarkan KRS '.uniqid(),
                'sub_topik' => 'Semester profil tidak menentukan peserta',
            ])
            ->assertOk();

        $this->assertDatabaseHas('absensi', [
            'pertemuan_id' => $response->json('pertemuan_id'),
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'status' => 'belum diabsen',
            'tanggal' => now()->toDateString(),
        ]);
    }

    public function test_new_meeting_excludes_approved_krs_when_profile_semester_parity_is_outdated(): void
    {
        [$dosen, $jadwal, $pesertaKrs] = $this->scheduleWithApprovedParticipants(2);
        $mahasiswa = $pesertaKrs->first();
        $periodeGanjil = strtolower((string) $jadwal->tahunAjaran->semester) === 'ganjil';
        $mahasiswa->update(['semester' => $periodeGanjil ? 2 : 1]);

        $response = $this->actingAs($dosen, 'dosen')
            ->postJson(route('dosen.absensi.store'), [
                'jadwal_id' => $jadwal->id,
                'tanggal_pertemuan' => now()->toDateString(),
                'jam_mulai' => '08:00',
                'jam_selesai' => '09:30',
                'metode_pbm' => 'offline',
                'topik' => 'Uji semester belum diperbarui '.uniqid(),
                'sub_topik' => 'Mahasiswa harus memperbarui semester profil',
            ])
            ->assertOk();

        $this->assertDatabaseMissing('absensi', [
            'pertemuan_id' => $response->json('pertemuan_id'),
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);
    }

    public function test_opening_old_meeting_adds_missing_krs_participant_without_overwriting_attendance(): void
    {
        [$dosen, $jadwal, $pesertaKrs] = $this->scheduleWithApprovedParticipants(2);
        $sudahTercatat = $pesertaKrs->first();
        $belumTercatat = $pesertaKrs->get(1);
        $pertemuan = Pertemuan::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'metode_pbm' => 'offline',
            'topik' => 'Uji sinkronisasi peserta '.uniqid(),
            'sub_topik' => 'Status lama harus dipertahankan',
            'dosen_id' => $dosen->dosen_id,
            'status' => 1,
        ]);

        Absensi::create([
            'jadwal_id' => $jadwal->id,
            'mahasiswa_id' => $sudahTercatat->mahasiswa_id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'tanggal' => $pertemuan->tanggal_pertemuan,
            'status' => 'hadir',
            'keterangan' => 'Status yang sudah disimpan',
        ]);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi.create', $pertemuan->pertemuan_id))
            ->assertOk();

        $this->assertDatabaseHas('absensi', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'mahasiswa_id' => $sudahTercatat->mahasiswa_id,
            'status' => 'hadir',
            'keterangan' => 'Status yang sudah disimpan',
        ]);
        $this->assertDatabaseHas('absensi', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'mahasiswa_id' => $belumTercatat->mahasiswa_id,
            'status' => 'belum diabsen',
        ]);
    }

    public function test_attendance_submission_rejects_student_outside_meeting_and_krs_roster(): void
    {
        [$dosen, $jadwal] = $this->scheduleWithApprovedParticipants(2);
        $pertemuan = Pertemuan::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'metode_pbm' => 'offline',
            'topik' => 'Uji pembatasan peserta '.uniqid(),
            'sub_topik' => 'Mahasiswa di luar KRS harus ditolak',
            'dosen_id' => $dosen->dosen_id,
            'status' => 1,
        ]);
        $mahasiswaLain = Mahasiswa::query()
            ->whereNotIn('mahasiswa_id', Krs::query()
                ->where('kurikulum_id', $jadwal->kurikulum_id)
                ->where('ta_id', $jadwal->ta_id)
                ->pluck('mahasiswa_id'))
            ->firstOrFail();

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.absensi-store'), [
                'pertemuan_id' => $pertemuan->pertemuan_id,
                'status' => [$mahasiswaLain->mahasiswa_id => 'hadir'],
                'keterangan' => [],
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('absensi', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'mahasiswa_id' => $mahasiswaLain->mahasiswa_id,
        ]);
    }

    public function test_roster_shows_unapproved_and_missing_krs_but_keeps_them_locked(): void
    {
        [$dosen, $jadwal] = $this->scheduleWithApprovedParticipants(2);
        $semesterMatkul = (int) ($jadwal->kurikulum->mataKuliah->smt
            ?: $jadwal->kurikulum->mataKuliah->semester);
        $kelasMahasiswa = strtolower((string) $jadwal->jenis_kelas) === 'karyawan'
            ? 'karyawan'
            : 'pagi';
        $tanpaKrs = Mahasiswa::query()
            ->whereNotIn('mahasiswa_id', Krs::query()
                ->where('kurikulum_id', $jadwal->kurikulum_id)
                ->where('ta_id', $jadwal->ta_id)
                ->pluck('mahasiswa_id'))
            ->take(2)
            ->get();

        $this->assertCount(2, $tanpaKrs, 'Data uji membutuhkan dua mahasiswa di luar KRS jadwal.');

        foreach ($tanpaKrs as $mahasiswa) {
            $mahasiswa->update([
                'jurusan_id' => $jadwal->jurusan_id ?: $jadwal->kurikulum->jurusan_id,
                'semester' => $semesterMatkul,
                'kelas' => $kelasMahasiswa,
                'status_mhs' => 'aktif',
            ]);
        }

        $menunggu = $tanpaKrs->first();
        $belumMengambil = $tanpaKrs->last();
        Krs::create([
            'kurikulum_id' => $jadwal->kurikulum_id,
            'matakuliah_id' => $jadwal->kurikulum->matakuliah_id,
            'ta_id' => $jadwal->ta_id,
            'mahasiswa_id' => $menunggu->mahasiswa_id,
            'jenis_kelas' => $jadwal->jenis_kelas,
            'disetujui_oleh' => null,
            'disetujui_pada' => null,
        ]);

        $pertemuan = Pertemuan::create([
            'jadwal_id' => $jadwal->id,
            'tanggal_pertemuan' => now()->toDateString(),
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '09:30:00',
            'metode_pbm' => 'offline',
            'topik' => 'Uji informasi status KRS '.uniqid(),
            'sub_topik' => 'Mahasiswa terkunci tetap terlihat',
            'dosen_id' => $dosen->dosen_id,
            'status' => 1,
        ]);

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.absensi.create', $pertemuan))
            ->assertOk()
            ->assertSee($menunggu->nama)
            ->assertSee('Menunggu ACC Dospem')
            ->assertSee($belumMengambil->nama)
            ->assertSee('Belum Mengambil KRS');

        $this->post(route('dosen.absensi-store'), [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'status' => [$menunggu->mahasiswa_id => 'hadir'],
            'keterangan' => [],
        ])->assertForbidden();

        $this->assertDatabaseMissing('absensi', [
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'mahasiswa_id' => $menunggu->mahasiswa_id,
        ]);
    }

    private function scheduleWithApprovedParticipants(int $minimumParticipants): array
    {
        $activeTaId = TahunAkademik::query()->where('status_ta', 1)->value('ta_id');
        $this->assertNotNull($activeTaId, 'Data uji membutuhkan Tahun Akademik aktif.');

        $jadwal = Jadwal::query()
            ->where('ta_id', $activeTaId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) {
                $query->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)');
            })
            ->get()
            ->first(function (Jadwal $jadwal) use ($minimumParticipants) {
                return $this->approvedParticipants($jadwal)->count() >= $minimumParticipants;
            });

        $this->assertNotNull($jadwal, 'Data uji membutuhkan jadwal dengan sedikitnya dua peserta KRS disetujui.');

        $assignment = $jadwal->kurikulum->dosenToMatakuliah()
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $jadwal->jenis_kelas)])
            ->firstOrFail();

        return [
            Dosen::findOrFail($assignment->dosen_id),
            $jadwal,
            $this->approvedParticipants($jadwal),
        ];
    }

    private function approvedParticipants(Jadwal $jadwal)
    {
        $jenisKelas = strtolower((string) $jadwal->jenis_kelas);

        $ids = Krs::query()
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->where('ta_id', $jadwal->ta_id)
            ->whereNotNull('disetujui_pada')
            ->whereHas('mahasiswa', function ($query) use ($jenisKelas, $jadwal) {
                $query->whereRaw('LOWER(status_mhs) = ?', ['aktif']);

                if (strtolower((string) $jadwal->tahunAjaran->semester) === 'ganjil') {
                    $query->whereIn('semester', [1, 3, 5, 7]);
                } else {
                    $query->whereIn('semester', [2, 4, 6, 8]);
                }

                if ($jenisKelas === 'karyawan') {
                    $query->whereRaw('LOWER(kelas) = ?', ['karyawan']);
                } else {
                    $query->whereIn(DB::raw('LOWER(kelas)'), ['pagi', 'reguler']);
                }
            })
            ->pluck('mahasiswa_id');

        return Mahasiswa::query()->whereIn('mahasiswa_id', $ids)->get();
    }
}
