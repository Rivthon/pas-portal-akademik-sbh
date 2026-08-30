<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\BapPengajaranController;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\TahunAkademik;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class BapAndKhsSynchronizationTest extends TestCase
{
    private function dendy(): Dosen
    {
        return Dosen::findOrFail(22);
    }

    private function activeTa(): TahunAkademik
    {
        return TahunAkademik::where('status_ta', 1)->firstOrFail();
    }

    private function tikFarmasiKaryawan(): Jadwal
    {
        return Jadwal::query()
            ->where('ta_id', $this->activeTa()->ta_id)
            ->whereRaw('LOWER(jenis_kelas) = ?', ['karyawan'])
            ->whereHas('kurikulum.mataKuliah', fn ($query) => $query
                ->where('nama', 'Teknologi Informasi dan Komunikasi'))
            ->whereHas('kurikulum.programStudi', fn ($query) => $query
                ->where('nama', 'FARMASI'))
            ->whereHas('kurikulum.dosenToMatakuliah', fn ($query) => $query
                ->where('dosen_id', 22)
                ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']))
            ->firstOrFail();
    }

    public function test_bap_does_not_count_future_meetings(): void
    {
        $jadwal = $this->tikFarmasiKaryawan();
        $firstMeetingDate = $jadwal->pertemuan()
            ->where('dosen_id', $this->dendy()->dosen_id)
            ->min('tanggal_pertemuan');
        $this->assertNotNull($firstMeetingDate);

        $this->travelTo(Carbon::parse($firstMeetingDate)->subDay());

        try {
            $view = app(BapPengajaranController::class)->show(
                Request::create('/admin/bap-pengajaran/dosen/22', 'GET', [
                    'ta_id' => $this->activeTa()->ta_id,
                ]),
                $this->dendy()
            );

            $bapJadwal = $view->getData()['jadwalTeori']->firstWhere('id', $jadwal->id);

            $this->assertNotNull($bapJadwal);
            $this->assertSame(0, (int) $bapJadwal->jumlah_pertemuan);
            $this->assertCount(0, $bapJadwal->pertemuan);
        } finally {
            $this->travelBack();
        }
    }

    public function test_dendy_sees_eight_separate_active_schedule_cards_for_grading(): void
    {
        $response = $this->actingAs($this->dendy(), 'dosen')
            ->get(route('dosen.nilai-dosen.input'))
            ->assertOk();

        $jadwalList = $response->viewData('mataKuliahAktif');

        $this->assertCount(8, $jadwalList);
        $this->assertTrue($jadwalList->contains(fn ($jadwal) => $jadwal->id === $this->tikFarmasiKaryawan()->id));
    }

    public function test_employee_grade_endpoint_only_returns_employee_students(): void
    {
        $jadwal = $this->tikFarmasiKaryawan();

        $response = $this->actingAs($this->dendy(), 'dosen')
            ->getJson(route('dosen.input-nilai-dosen.mahasiswa', $jadwal))
            ->assertOk()
            ->assertJsonPath('konfigurasi.jadwal_id', $jadwal->id)
            ->assertJsonPath('konfigurasi.jenis_kelas', 'karyawan');

        $mahasiswaIds = collect($response->json('mahasiswa'))->pluck('mahasiswa_id');
        $this->assertCount(5, $mahasiswaIds);
        $this->assertDatabaseMissing('mahasiswa', [
            'mahasiswa_id' => $mahasiswaIds->first(),
            'kelas' => 'pagi',
        ]);
    }
}
