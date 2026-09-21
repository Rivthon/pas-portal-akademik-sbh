<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MahasiswaRekapAbsensiAcademicYearTest extends TestCase
{
    use DatabaseTransactions;

    public function test_rekap_only_contains_approved_krs_from_selected_academic_year(): void
    {
        $mahasiswa = Mahasiswa::query()
            ->whereHas('krs', fn ($query) => $query->whereNotNull('disetujui_pada'))
            ->firstOrFail();
        $krsAcuan = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereNotNull('disetujui_pada')
            ->with('kurikulum.mataKuliah')
            ->firstOrFail();
        $semester = (int) $krsAcuan->kurikulum->mataKuliah->smt;

        $response = $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.rekap.absensi', [
                'ta_id' => $krsAcuan->ta_id,
                'semester' => $semester,
            ]))
            ->assertOk()
            ->assertViewHas('selectedTaId', (int) $krsAcuan->ta_id);

        $response->assertViewHas('krs', function ($rekap) use ($krsAcuan) {
            return $rekap->isNotEmpty()
                && $rekap->every(fn ($item) => (int) $item->ta_id === (int) $krsAcuan->ta_id
                    && $item->disetujui_pada !== null);
        });
    }

    public function test_default_rekap_uses_active_academic_year_without_mixing_old_attendance(): void
    {
        $mahasiswa = Mahasiswa::query()->where('status_mhs', 'aktif')->firstOrFail();
        $tahunAkademikAktif = TahunAkademik::query()->where('status_ta', 1)->firstOrFail();

        $response = $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.rekap.absensi'))
            ->assertOk()
            ->assertViewHas('selectedTaId', (int) $tahunAkademikAktif->ta_id);

        $response->assertViewHas('krs', fn ($rekap) => $rekap->every(
            fn ($item) => (int) $item->ta_id === (int) $tahunAkademikAktif->ta_id
                && $item->disetujui_pada !== null
        ));
    }
}
