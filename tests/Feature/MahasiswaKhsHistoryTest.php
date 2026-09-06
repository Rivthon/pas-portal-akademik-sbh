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
        $mahasiswa->update(['status_akhir' => 0, 'status_edom' => 0]);
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');
        $selectedKrs = $group->first(fn ($item) => ! $activeTaId || (int) $item->ta_id !== (int) $activeTaId);
        $this->assertNotNull($selectedKrs, 'Tidak ada KHS tahun akademik lama untuk pengujian.');
        $selectedTaId = (int) $selectedKrs->ta_id;
        $selectedYear = TahunAkademik::findOrFail($selectedTaId);

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
            ->get(route('mahasiswa.khs.riwayat', ['ta_id' => $selectedTaId]))
            ->assertOk()
            ->assertSee('Riwayat KHS')
            ->assertSee('Tahun Akademik Sebelumnya')
            ->assertSee($selectedYear->nama)
            ->assertSee($selectedKrs->kurikulum->mataKuliah->nama)
            ->assertSee('tidak mengikuti status aktivasi KHS semester aktif');

        $pdfResponse = $this->get(route('mahasiswa.khs.cetak', ['ta_id' => $selectedTaId]));
        $this->assertFalse(
            $pdfResponse->isRedirection(),
            'Cetak KHS lama tidak boleh dialihkan.'
        );
        $pdfResponse->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_active_semester_khs_still_requires_administration_activation(): void
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $krs = Krs::query()
            ->where('ta_id', $activeTa->ta_id)
            ->whereNotNull('khs')
            ->with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->whereHas('mahasiswa')
            ->whereHas('kurikulum.mataKuliah')
            ->firstOrFail();
        $mahasiswa = $krs->mahasiswa;
        $mahasiswa->update(['status_akhir' => 0, 'status_edom' => 1]);

        KhsPublication::updateOrCreate([
            'ta_id' => $activeTa->ta_id,
            'program_studi_id' => $mahasiswa->jurusan_id,
            'scope_key' => 'all',
        ], [
            'scope_type' => 'all',
            'published_by_user_id' => null,
            'published_at' => now(),
        ]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.kartu-hasil.index'))
            ->assertForbidden();

        $this->get(route('mahasiswa.khs.cetak', ['ta_id' => $activeTa->ta_id]))
            ->assertForbidden();
    }

    public function test_main_khs_page_ignores_historical_ta_parameter_and_only_uses_active_year(): void
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $krs = Krs::query()
            ->where('ta_id', $activeTa->ta_id)
            ->whereNotNull('khs')
            ->with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->whereHas('mahasiswa')
            ->whereHas('kurikulum.mataKuliah')
            ->firstOrFail();
        $mahasiswa = $krs->mahasiswa;
        $mahasiswa->update(['status_akhir' => 1, 'status_edom' => 1]);

        KhsPublication::updateOrCreate([
            'ta_id' => $activeTa->ta_id,
            'program_studi_id' => $mahasiswa->jurusan_id,
            'scope_key' => 'all',
        ], [
            'scope_type' => 'all',
            'published_by_user_id' => null,
            'published_at' => now(),
        ]);

        $historicalTaId = TahunAkademik::where('ta_id', '<', $activeTa->ta_id)->max('ta_id');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.kartu-hasil.index', ['ta_id' => $historicalTaId]))
            ->assertOk()
            ->assertSee('SEMESTER AKTIF')
            ->assertSee($activeTa->nama)
            ->assertSee($krs->kurikulum->mataKuliah->nama);
    }
}
