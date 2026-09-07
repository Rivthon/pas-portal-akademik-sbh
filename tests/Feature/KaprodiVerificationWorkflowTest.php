<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\KaprodiAbsensiVerification;
use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\NilaiSubmission;
use App\Models\Pertemuan;
use App\Models\ProgramStudi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class KaprodiVerificationWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_assigned_kaprodi_can_open_verification_menu(): void
    {
        $prodi = ProgramStudi::query()->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $prodi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        $this->actingAs($kaprodi, 'dosen')->get(route('dosen.kaprodi.absensi.index'))->assertOk();
        $other = Dosen::query()->whereKeyNot($kaprodi->dosen_id)->firstOrFail();
        $this->actingAs($other, 'dosen')->get(route('dosen.kaprodi.absensi.index'))->assertForbidden();
    }

    public function test_kaprodi_can_open_integrated_monitoring_for_led_program(): void
    {
        $prodi = ProgramStudi::query()->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $prodi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        $taId = TahunAkademik::where('status_ta', 1)->value('ta_id') ?: TahunAkademik::query()->value('ta_id');

        $this->actingAs($kaprodi, 'dosen')
            ->get(route('dosen.kaprodi.monitoring.index', [
                'program_studi_id' => $prodi->jurusan_id,
                'ta_id' => $taId,
            ]))
            ->assertOk()
            ->assertSee('Progress Pengajaran')
            ->assertSee('RPS')
            ->assertSee('KRS')
            ->assertSee('EDOM')
            ->assertSee('Komentar ditampilkan anonim');

        $other = Dosen::query()->whereKeyNot($kaprodi->dosen_id)->firstOrFail();
        $this->actingAs($other, 'dosen')
            ->get(route('dosen.kaprodi.monitoring.index', ['program_studi_id' => $prodi->jurusan_id]))
            ->assertForbidden();
    }

    public function test_kaprodi_can_review_and_approve_submitted_grade(): void
    {
        $jadwal = Jadwal::with('programStudi')->whereHas('programStudi')->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $jadwal->programStudi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        $submission = NilaiSubmission::updateOrCreate(['jadwal_id' => $jadwal->id], ['program_studi_id' => $jadwal->jurusan_id, 'submitted_by_dosen_id' => $kaprodi->dosen_id, 'status' => 'submitted', 'submitted_at' => now()]);
        $this->actingAs($kaprodi, 'dosen')->get(route('dosen.kaprodi.nilai.show', $submission))->assertOk();
        $this->actingAs($kaprodi, 'dosen')->post(route('dosen.kaprodi.nilai.approve', $submission))->assertSessionHas('success');
        $this->assertSame('approved', $submission->fresh()->status);
    }

    public function test_kaprodi_can_verify_selected_attendance_recaps_in_bulk(): void
    {
        $jadwal = Jadwal::with('programStudi')->whereHas('programStudi')->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $jadwal->programStudi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        Pertemuan::firstOrCreate(
            ['jadwal_id' => $jadwal->id, 'tanggal_pertemuan' => now()->toDateString()],
            [
                'topik' => 'Pertemuan uji verifikasi massal',
                'sub_topik' => 'Pengujian',
                'dosen_id' => $kaprodi->dosen_id,
                'jam_mulai' => '08:00:00',
                'jam_selesai' => '09:00:00',
                'metode_pbm' => 'offline',
                'status' => 0,
            ]
        );
        KaprodiAbsensiVerification::where('jadwal_id', $jadwal->id)->delete();

        $this->actingAs($kaprodi, 'dosen')
            ->post(route('dosen.kaprodi.absensi.bulk-verify'), [
                'jadwal_ids' => [$jadwal->id],
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('kaprodi_absensi_verifications', [
            'jadwal_id' => $jadwal->id,
            'program_studi_id' => $jadwal->jurusan_id,
            'verified_by_dosen_id' => $kaprodi->dosen_id,
        ]);
    }

    public function test_kaprodi_can_approve_selected_grades_in_bulk_and_cannot_cross_program_scope(): void
    {
        $jadwal = Jadwal::with('programStudi')->whereHas('programStudi')->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $jadwal->programStudi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        $submission = NilaiSubmission::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                'program_studi_id' => $jadwal->jurusan_id,
                'submitted_by_dosen_id' => $kaprodi->dosen_id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'reviewed_by_dosen_id' => null,
                'reviewed_at' => null,
            ]
        );

        $this->actingAs($kaprodi, 'dosen')
            ->post(route('dosen.kaprodi.nilai.bulk-approve'), [
                'submission_ids' => [$submission->id],
            ])
            ->assertSessionHas('success');

        $this->assertSame('approved', $submission->fresh()->status);
        $this->assertSame((string) $kaprodi->dosen_id, (string) $submission->fresh()->reviewed_by_dosen_id);

        $foreignJadwal = Jadwal::with('programStudi')
            ->where('jurusan_id', '!=', $jadwal->jurusan_id)
            ->whereHas('programStudi')
            ->first();
        if ($foreignJadwal) {
            $foreignSubmission = NilaiSubmission::updateOrCreate(
                ['jadwal_id' => $foreignJadwal->id],
                [
                'program_studi_id' => $foreignJadwal->jurusan_id,
                'status' => 'submitted',
                'reviewed_by_dosen_id' => null,
                'reviewed_at' => null,
                'submitted_at' => now(),
                ]
            );

            $this->post(route('dosen.kaprodi.nilai.bulk-approve'), [
                'submission_ids' => [$foreignSubmission->id],
            ])->assertForbidden();

            $this->assertSame('submitted', $foreignSubmission->fresh()->status);
        }
    }

    public function test_kaprodi_can_search_attendance_by_course_name(): void
    {
        $jadwal = Jadwal::with(['programStudi', 'kurikulum.mataKuliah'])->whereHas('kurikulum.mataKuliah')->firstOrFail();
        $kaprodi = Dosen::query()->firstOrFail();
        $jadwal->programStudi->update(['kaprodi_dosen_id' => $kaprodi->dosen_id]);
        $nama = $jadwal->kurikulum->mataKuliah->nama;

        $this->actingAs($kaprodi, 'dosen')->get(route('dosen.kaprodi.absensi.index', [
            'ta_id' => $jadwal->ta_id,
            'search' => $nama,
        ]))->assertOk()->assertSee($nama);
    }

    public function test_publication_page_requires_permission(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin)->get(route('admin.nilai-publish.index'))->assertForbidden();
        $admin->givePermissionTo(Permission::findOrCreate('nilai-publish', 'web'));
        $this->actingAs($admin)->get(route('admin.nilai-publish.index', ['ta_id' => TahunAkademik::query()->value('ta_id')]))
            ->assertOk()
            ->assertSee('Terbitkan Satu Tahun Ajaran')
            ->assertSee('Terbitkan Per Semester')
            ->assertSee('Penerbitan Per Mata Kuliah');
    }

    public function test_baak_can_publish_khs_per_course_and_semester(): void
    {
        $jadwal = $this->jadwalWithMatchingParticipant();
        $semester = (int) $jadwal->kurikulum->mataKuliah->smt;
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('nilai-publish', 'web'));
        KhsPublication::where('ta_id', $jadwal->ta_id)->where('program_studi_id', $jadwal->jurusan_id)->delete();

        NilaiSubmission::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            ['program_studi_id' => $jadwal->jurusan_id, 'status' => 'approved', 'submitted_at' => now(), 'reviewed_at' => now()]
        );

        $this->actingAs($admin)->post(route('admin.nilai-publish.store'), [
            'ta_id' => $jadwal->ta_id,
            'program_studi_id' => $jadwal->jurusan_id,
            'scope_type' => 'course',
            'jadwal_id' => $jadwal->id,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('khs_publications', [
            'ta_id' => $jadwal->ta_id,
            'program_studi_id' => $jadwal->jurusan_id,
            'scope_type' => 'course',
            'scope_key' => 'jadwal:'.$jadwal->id,
        ]);
        $this->assertTrue(KhsPublication::coversJadwal($jadwal));
        $krs = Krs::with(['kurikulum.mataKuliah', 'mahasiswa'])
            ->where('ta_id', $jadwal->ta_id)
            ->where('kurikulum_id', $jadwal->kurikulum_id)
            ->whereHas('mahasiswa', function ($query) use ($jadwal) {
                strtolower((string) $jadwal->jenis_kelas) === 'karyawan'
                    ? $query->whereRaw("LOWER(kelas) = 'karyawan'")
                    : $query->where(fn ($kelas) => $kelas->whereNull('kelas')->orWhereRaw("LOWER(kelas) != 'karyawan'"));
            })
            ->firstOrFail();
        $this->assertCount(1, KhsPublication::filterPublishedKrs(collect([$krs]), $krs->mahasiswa));

        $semesterJadwal = $this->matchingParticipantQuery()
            ->where('jadwal.ta_id', $jadwal->ta_id)
            ->where('jadwal.jurusan_id', $jadwal->jurusan_id)
            ->whereHas('kurikulum.mataKuliah', fn ($query) => $query->where('smt', $semester))
            ->get();
        foreach ($semesterJadwal as $item) {
            NilaiSubmission::updateOrCreate(
                ['jadwal_id' => $item->id],
                ['program_studi_id' => $item->jurusan_id, 'status' => 'approved', 'submitted_at' => now(), 'reviewed_at' => now()]
            );
        }

        $this->actingAs($admin)->post(route('admin.nilai-publish.store'), [
            'ta_id' => $jadwal->ta_id,
            'program_studi_id' => $jadwal->jurusan_id,
            'scope_type' => 'semester',
            'semester' => $semester,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('khs_publications', [
            'ta_id' => $jadwal->ta_id,
            'program_studi_id' => $jadwal->jurusan_id,
            'scope_type' => 'semester',
            'scope_key' => 'semester:'.$semester,
        ]);
        $this->assertDatabaseMissing('khs_publications', ['scope_key' => 'jadwal:'.$jadwal->id]);
    }

    public function test_baak_can_temporarily_publish_submitted_grade_without_kaprodi_approval(): void
    {
        $jadwal = $this->jadwalWithMatchingParticipant();
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findOrCreate('nilai-publish', 'web'));
        KhsPublication::where('ta_id', $jadwal->ta_id)->where('program_studi_id', $jadwal->jurusan_id)->delete();

        $submission = NilaiSubmission::updateOrCreate(
            ['jadwal_id' => $jadwal->id],
            [
                'program_studi_id' => $jadwal->jurusan_id,
                'status' => 'submitted',
                'submitted_at' => now(),
                'reviewed_by_dosen_id' => null,
                'reviewed_at' => null,
                'review_note' => null,
            ]
        );

        $this->actingAs($admin)->post(route('admin.nilai-publish.store'), [
            'ta_id' => $jadwal->ta_id,
            'program_studi_id' => $jadwal->jurusan_id,
            'scope_type' => 'course',
            'jadwal_id' => $jadwal->id,
        ])->assertSessionHas('success');

        $submission->refresh();
        $this->assertSame('approved', $submission->status);
        $this->assertNull($submission->reviewed_by_dosen_id);
        $this->assertTrue($submission->isTemporaryBaakApproval());
        $this->assertTrue(KhsPublication::coversJadwal($jadwal));

        $publication = KhsPublication::where('scope_type', 'course')
            ->where('jadwal_id', $jadwal->id)
            ->firstOrFail();
        $this->actingAs($admin)
            ->delete(route('admin.nilai-publish.destroy', $publication))
            ->assertSessionHas('success');

        $this->assertSame('submitted', $submission->fresh()->status);
        $this->assertFalse(KhsPublication::coversJadwal($jadwal));
    }

    private function jadwalWithMatchingParticipant(): Jadwal
    {
        return $this->matchingParticipantQuery()
            ->with('kurikulum.mataKuliah')
            ->firstOrFail();
    }

    private function matchingParticipantQuery()
    {
        return Jadwal::query()->whereExists(function ($query) {
            $query->selectRaw('1')
                ->from('krs')
                ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
                ->whereColumn('krs.kurikulum_id', 'jadwal.kurikulum_id')
                ->whereColumn('krs.ta_id', 'jadwal.ta_id')
                ->where(function ($kelas) {
                    $kelas->where(function ($karyawan) {
                        $karyawan->whereRaw("LOWER(jadwal.jenis_kelas) = 'karyawan'")
                            ->whereRaw("LOWER(mahasiswa.kelas) = 'karyawan'");
                    })->orWhere(function ($reguler) {
                        $reguler->whereRaw("LOWER(jadwal.jenis_kelas) != 'karyawan'")
                            ->where(fn ($mahasiswa) => $mahasiswa->whereNull('mahasiswa.kelas')->orWhereRaw("LOWER(mahasiswa.kelas) != 'karyawan'"));
                    });
                });
        });
    }
}
