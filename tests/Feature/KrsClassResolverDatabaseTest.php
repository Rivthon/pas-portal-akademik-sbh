<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsMateri;
use App\Models\TahunAkademik;
use App\Services\LmsCalendarService;
use App\Support\KrsClassResolver;
use Tests\TestCase;

class KrsClassResolverDatabaseTest extends TestCase
{
    public function test_resolver_returns_matching_schedule_from_student_krs(): void
    {
        $match = null;

        foreach (Krs::with('mahasiswa')->get() as $krs) {
            if (! $krs->mahasiswa) {
                continue;
            }

            $jadwal = Jadwal::where('ta_id', $krs->ta_id)
                ->where('kurikulum_id', $krs->kurikulum_id)
                ->get()
                ->first(fn (Jadwal $item) => KrsClassResolver::matches(
                    $krs,
                    $item,
                    $krs->mahasiswa
                ));

            if ($jadwal) {
                $match = [$krs, $jadwal];
                break;
            }
        }

        $this->assertNotNull($match, 'Tidak ditemukan pasangan KRS dan jadwal yang cocok.');

        [$krs, $jadwal] = $match;
        $resolved = KrsClassResolver::jadwalIdsForMahasiswa(
            $krs->mahasiswa,
            (int) $krs->ta_id
        );

        $this->assertTrue(
            $resolved->contains((int) $jadwal->id),
            'Jadwal yang sesuai KRS tidak dikembalikan oleh resolver.'
        );
    }

    public function test_matching_student_receives_lms_calendar_event(): void
    {
        $match = null;
        $activeTaId = TahunAkademik::where('status_ta', true)->value('ta_id');
        $this->assertNotNull($activeTaId, 'Tahun akademik aktif tidak ditemukan.');

        foreach (LmsMateri::with('jadwal')
            ->where('status', true)
            ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $activeTaId))
            ->get() as $materi) {
            if (! $materi->jadwal) {
                continue;
            }

            $krs = Krs::with('mahasiswa')
                ->where('ta_id', $materi->jadwal->ta_id)
                ->where('kurikulum_id', $materi->jadwal->kurikulum_id)
                ->get()
                ->first(fn (Krs $item) => $item->mahasiswa
                    && KrsClassResolver::matches($item, $materi->jadwal, $item->mahasiswa));

            if ($krs?->mahasiswa) {
                $match = [$materi, $krs->mahasiswa];
                break;
            }
        }

        $this->assertNotNull($match, 'Tidak ditemukan materi dengan mahasiswa kelas yang sesuai.');

        [$materi, $mahasiswa] = $match;
        $jadwalIds = KrsClassResolver::jadwalIdsForMahasiswa(
            $mahasiswa,
            (int) $materi->jadwal->ta_id
        );
        $events = app(LmsCalendarService::class)->events(
            $jadwalIds,
            'mahasiswa',
            'mahasiswa',
            (int) $mahasiswa->mahasiswa_id
        );

        $this->assertContains(
            'materi-'.$materi->materi_id,
            collect($events)->pluck('id')->all()
        );

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.lms.index'))
            ->assertOk()
            ->assertSee('Materi baru: '.$materi->judul);
    }
}
