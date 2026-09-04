<?php

namespace Tests\Feature;

use App\Models\KhsPublication;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaKhsHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_select_a_published_khs_from_a_previous_academic_year(): void
    {
        $group = Krs::query()
            ->whereNotNull('khs')
            ->with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->get()
            ->filter(fn ($item) => $item->mahasiswa && $item->kurikulum?->mataKuliah)
            ->groupBy('mahasiswa_id')
            ->first(fn ($items) => $items->pluck('ta_id')->unique()->count() >= 2);

        $this->assertNotNull($group, 'Tidak ada mahasiswa dengan KHS pada dua tahun akademik.');
        $mahasiswa = Mahasiswa::findOrFail($group->first()->mahasiswa_id);
        $mahasiswa->update(['status_akhir' => 1, 'status_edom' => 0]);
        $years = $group->pluck('ta_id')->unique()->sort()->values();
        $selectedTaId = (int) $years->first();
        $selectedYear = TahunAkademik::findOrFail($selectedTaId);
        $selectedKrs = $group->firstWhere('ta_id', $selectedTaId);

        KhsPublication::updateOrCreate([
            'ta_id' => $selectedTaId,
            'program_studi_id' => $mahasiswa->jurusan_id,
            'scope_key' => 'all',
        ], [
            'scope_type' => 'all',
            'published_by_user_id' => null,
            'published_at' => now(),
        ]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.kartu-hasil.index', ['ta_id' => $selectedTaId]))
            ->assertOk()
            ->assertSee('Riwayat Tahun Akademik')
            ->assertSee($selectedYear->nama)
            ->assertSee($selectedKrs->kurikulum->mataKuliah->nama)
            ->assertSee('arsip KHS tahun akademik sebelumnya');
    }
}
