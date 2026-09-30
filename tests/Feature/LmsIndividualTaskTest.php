<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsTugas;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LmsIndividualTaskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_individual_task_is_only_visible_and_accessible_by_selected_students(): void
    {
        [$jadwal, $selected, $notSelected] = $this->scheduleAndStudents();
        $pertemuan = $jadwal->pertemuan()->firstOrFail();

        $task = LmsTugas::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'dosen_id' => $pertemuan->dosen_id,
            'judul' => 'Tugas Individu Pengujian',
            'deskripsi' => 'Hanya untuk mahasiswa terpilih.',
            'tipe' => 'teks',
            'cakupan' => 'individu',
            'deadline' => now()->addDay(),
            'nilai_maksimal' => 100,
            'aktif' => true,
        ]);
        $task->targetMahasiswa()->attach($selected->mahasiswa_id);

        $this->assertTrue(LmsTugas::visibleForMahasiswa($selected->mahasiswa_id)->whereKey($task->tugas_id)->exists());
        $this->assertFalse(LmsTugas::visibleForMahasiswa($notSelected->mahasiswa_id)->whereKey($task->tugas_id)->exists());

        $this->actingAs($selected, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $task))
            ->assertOk()
            ->assertSee('Tugas Individu Pengujian');

        auth('mahasiswa')->logout();
        $this->actingAs($notSelected, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $task))
            ->assertForbidden();
    }

    private function scheduleAndStudents(): array
    {
        foreach (Jadwal::with(['pertemuan', 'kurikulum'])->get() as $jadwal) {
            if ($jadwal->pertemuan->isEmpty()) {
                continue;
            }

            $students = Krs::with('mahasiswa')
                ->where('kurikulum_id', $jadwal->kurikulum_id)
                ->where('ta_id', $jadwal->ta_id)
                ->whereNotNull('disetujui_pada')
                ->get()
                ->filter(fn (Krs $krs) => $krs->mahasiswa
                    && KrsClassResolver::matches($krs, $jadwal, $krs->mahasiswa))
                ->pluck('mahasiswa')
                ->unique('mahasiswa_id')
                ->values();

            if ($students->count() >= 2) {
                return [$jadwal, $students[0], $students[1]];
            }
        }

        $this->markTestSkipped('Tidak ada jadwal dengan minimal dua peserta KRS disetujui.');
    }
}
