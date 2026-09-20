<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminJadwalDayOrderTest extends TestCase
{
    use DatabaseTransactions;

    public function test_theory_and_practical_schedules_are_ordered_from_monday(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo([
            Permission::findOrCreate('jadwal-list', 'web'),
            Permission::findOrCreate('jadwal-praktik-list', 'web'),
        ]);
        $taId = TahunAkademik::where('status_ta', 1)->value('ta_id');

        $this->assertScheduleOrder($admin, Jadwal::class, 'admin.jadwal.filter', $taId);
        $this->assertScheduleOrder($admin, JadwalPraktik::class, 'admin.jadwal-praktik.filter', $taId);
    }

    private function assertScheduleOrder(User $admin, string $model, string $routeName, int $taId): void
    {
        $sample = $model::with('kurikulum.mataKuliah')
            ->where('ta_id', $taId)
            ->whereHas('kurikulum.mataKuliah')
            ->firstOrFail();

        $response = $this->actingAs($admin)->getJson(route($routeName, [
            'programStudi' => $sample->jurusan_id,
            'semester' => $sample->kurikulum->mataKuliah->smt,
            'jenis_kelas' => $sample->jenis_kelas,
        ]));

        $response->assertOk();
        $actualOrder = collect($response->json())
            ->pluck('hari')
            ->map(fn ($hari) => $this->dayRank($hari))
            ->values();

        $this->assertSame($actualOrder->sort()->values()->all(), $actualOrder->all());
    }

    private function dayRank(?string $hari): int
    {
        return match (strtolower(trim((string) $hari))) {
            'senin' => 1,
            'selasa' => 2,
            'rabu' => 3,
            'kamis' => 4,
            'jumat' => 5,
            'sabtu' => 6,
            'minggu' => 7,
            default => 8,
        };
    }
}
