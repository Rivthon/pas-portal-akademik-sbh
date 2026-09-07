<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\Kurikulum;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KrsDuplicatePreventionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_cannot_take_the_same_course_twice(): void
    {
        [$mahasiswa, $kurikulum, $ta] = $this->eligibleEnrollment();
        $this->removeEnrollment($mahasiswa, $kurikulum, $ta);

        $payload = ['krs' => [$kurikulum->kurikulum_id]];

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.simpan.krs'), $payload)
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.simpan.krs'), $payload)
            ->assertUnprocessable()
            ->assertJson(['success' => false]);

        $this->assertSame(1, Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->where('matakuliah_id', $kurikulum->matakuliah_id)
            ->count());
    }

    public function test_two_curriculums_for_the_same_course_are_rejected_together(): void
    {
        [$mahasiswa, $kurikulum, $ta] = $this->eligibleEnrollment();
        $this->removeEnrollment($mahasiswa, $kurikulum, $ta);

        $duplicateId = DB::table('kurikulum')->insertGetId([
            'ta_id' => $kurikulum->ta_id,
            'jurusan_id' => $kurikulum->jurusan_id,
            'matakuliah_id' => $kurikulum->matakuliah_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.simpan.krs'), [
                'krs' => [$kurikulum->kurikulum_id, $duplicateId],
            ])
            ->assertUnprocessable()
            ->assertJsonFragment([
                'message' => 'Mata kuliah yang sama tidak boleh dipilih lebih dari satu kali.',
            ]);

        $this->assertDatabaseMissing('krs', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'ta_id' => $ta->ta_id,
            'matakuliah_id' => $kurikulum->matakuliah_id,
        ]);
    }

    private function eligibleEnrollment(): array
    {
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $kurikulum = Kurikulum::with('mataKuliah')
            ->where('ta_id', $ta->ta_id)
            ->whereHas('mataKuliah')
            ->firstOrFail();
        $mahasiswa = Mahasiswa::where('jurusan_id', $kurikulum->jurusan_id)
            ->where('semester', $kurikulum->mataKuliah->smt)
            ->firstOrFail();
        $mahasiswa->setAttribute('status_krs', 1);

        return [$mahasiswa, $kurikulum, $ta];
    }

    private function removeEnrollment(
        Mahasiswa $mahasiswa,
        Kurikulum $kurikulum,
        TahunAkademik $ta
    ): void {
        Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->where(function ($query) use ($kurikulum) {
                $query->where('matakuliah_id', $kurikulum->matakuliah_id)
                    ->orWhere('kurikulum_id', $kurikulum->kurikulum_id);
            })
            ->delete();
    }
}
