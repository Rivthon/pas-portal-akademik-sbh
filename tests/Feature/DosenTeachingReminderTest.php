<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Pertemuan;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenTeachingReminderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_reminder_appears_after_schedule_passes_and_disappears_after_teaching(): void
    {
        Carbon::setTestNow('2026-08-05 12:00:00');

        try {
            $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
            $jadwal = Jadwal::with(['kurikulum.dosenToMatakuliah.dosen', 'kurikulum.mataKuliah'])
                ->where('ta_id', $ta->ta_id)
                ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)'))
                ->firstOrFail();
            $assignment = $jadwal->kurikulum->dosenToMatakuliah
                ->first(fn ($item) => strtolower((string) $item->jenis_dosen) === 'teori'
                    && strtolower((string) $item->jenis_kelas) === strtolower((string) $jadwal->jenis_kelas));
            $dosen = $assignment->dosen;

            Pertemuan::where('jadwal_id', $jadwal->id)
                ->where('dosen_id', $dosen->dosen_id)
                ->delete();

            $jadwal->update(['hari' => 'Jumat', 'jam_mulai' => '08:00', 'jam_selesai' => '10:00']);
            $futureResponse = $this->actingAs($dosen, 'dosen')
                ->get(route('dosen.dashboard'))
                ->assertOk();
            $this->assertFalse($futureResponse->viewData('teachingReminders')->contains(
                fn (array $item) => $item['type'] === 'teori' && $item['schedule_id'] === $jadwal->id
            ));

            $jadwal->update(['hari' => 'Senin']);
            $reminderResponse = $this->get(route('dosen.dashboard'))
                ->assertOk()
                ->assertSee('Pengingat Pengajaran Minggu Ini')
                ->assertSee('Anda belum mengajar '.$jadwal->kurikulum->mataKuliah->nama.' minggu ini');
            $this->assertTrue($reminderResponse->viewData('teachingReminders')->contains(
                fn (array $item) => $item['type'] === 'teori' && $item['schedule_id'] === $jadwal->id
            ));

            Pertemuan::create([
                'jadwal_id' => $jadwal->id,
                'tanggal_pertemuan' => '2026-08-04',
                'jam_mulai' => '08:00',
                'jam_selesai' => '10:00',
                'metode_pbm' => 'offline',
                'topik' => 'Pengujian pengingat dashboard',
                'sub_topik' => 'Pertemuan minggu berjalan',
                'dosen_id' => $dosen->dosen_id,
            ]);

            $afterTeachingResponse = $this->get(route('dosen.dashboard'))->assertOk();
            $this->assertFalse($afterTeachingResponse->viewData('teachingReminders')->contains(
                fn (array $item) => $item['type'] === 'teori' && $item['schedule_id'] === $jadwal->id
            ));
        } finally {
            Carbon::setTestNow();
        }
    }
}
