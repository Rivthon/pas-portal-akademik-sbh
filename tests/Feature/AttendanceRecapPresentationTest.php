<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Akademik\AbsensiController as AdminAbsensiController;
use App\Http\Controllers\Admin\LaporanController as AdminLaporanController;
use App\Http\Controllers\Dosen\LaporanAbsensiController;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceRecapPresentationTest extends TestCase
{
    public function test_admin_and_dosen_use_a_for_alfa(): void
    {
        $dosenMapper = new ReflectionMethod(LaporanAbsensiController::class, 'mapStatus');
        $adminMapper = new ReflectionMethod(AdminAbsensiController::class, 'mapAbsensiStatus');
        $adminReportMapper = new ReflectionMethod(AdminLaporanController::class, 'mapAbsensiStatus');

        foreach (['tidak hadir', 'alpha', 'alpa', 'alfa'] as $status) {
            $this->assertSame('A', $dosenMapper->invoke(new LaporanAbsensiController, $status));
            $this->assertSame('A', $adminMapper->invoke(new AdminAbsensiController, $status));
            $this->assertSame('A', $adminReportMapper->invoke(new AdminLaporanController, $status));
        }
    }

    public function test_recap_header_contains_meeting_date_and_alfa_cell_is_red(): void
    {
        $mahasiswa = collect([(object) [
            'mahasiswa_id' => 99,
            'nama' => 'Mahasiswa Uji',
        ]]);
        $rekapAbsensi = collect([[
            'tanggal' => '2026-08-04',
            'absensi' => collect([99 => 'A']),
        ]]);

        $html = view('dosen.absensi.partials.rekap-table', [
            'mahasiswa' => $mahasiswa,
            'rekapAbsensi' => $rekapAbsensi,
            'totalPertemuan' => 1,
        ])->render();

        $this->assertStringContainsString('P1', $html);
        $this->assertStringContainsString('04/08/2026', $html);
        $this->assertStringContainsString('background-color: #f8d7da', $html);
        $this->assertStringContainsString('A = Alfa', $html);
        $this->assertStringNotContainsString('T = Tidak', $html);
    }

    public function test_recap_contains_lecturer_and_head_of_program_signatures(): void
    {
        $jadwal = (object) [
            'kurikulum' => (object) [
                'programStudi' => (object) [
                    'nama' => 'Program Studi Uji',
                    'kaprod' => 'Dr. Kaprodi Uji',
                ],
            ],
        ];
        $dosenMatakuliah = collect([(object) [
            'dosen_id' => 22,
            'nama' => 'Dosen Pengampu Uji',
            'nidn' => '1234567890',
        ]]);

        $html = view('dosen.absensi.partials.signatures', [
            'jadwal' => $jadwal,
            'dosenMatakuliah' => $dosenMatakuliah,
            'kaprodiSignature' => null,
        ])->render();

        $this->assertStringContainsString('Dosen Pengampu/Pengajar', $html);
        $this->assertStringContainsString('Dosen Pengampu Uji', $html);
        $this->assertStringContainsString('NIDN. 1234567890', $html);
        $this->assertStringContainsString('Ketua Program Studi Program Studi Uji', $html);
        $this->assertStringContainsString('Dr. Kaprodi Uji', $html);
    }
}
