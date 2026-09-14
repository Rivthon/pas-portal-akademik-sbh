<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\Penilaian\PenilaianController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminEdomClassSeparationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_registry_and_detail_separate_edom_by_class(): void
    {
        $controller = app(PenilaianController::class);
        $registry = $controller->index(Request::create('/admin/penilaian', 'GET', [
            'ta_id' => 18,
            'jurusan_id' => 48201,
            'dosen_id' => 22,
        ]));

        $rows = $registry->getData()['assignments']
            ->whereIn('kurikulum_id', [906, 924])
            ->keyBy(fn ($row) => $row->kurikulum_id.'|'.$row->jenis_kelas);

        $this->assertCount(4, $rows);
        $this->assertTrue((bool) $rows['906|reguler']->status_edom);
        $this->assertTrue((bool) $rows['906|karyawan']->status_edom);
        $this->assertTrue((bool) $rows['924|reguler']->status_edom);
        $this->assertFalse((bool) $rows['924|karyawan']->status_edom);
        $this->assertSame('2', (string) $rows['906|reguler']->kurikulum->mataKuliah->smt);

        $regular = $controller->detail(
            Request::create('/', 'GET', ['jenis_kelas' => 'reguler']),
            22,
            906,
            'teori'
        );
        $employee = $controller->detail(
            Request::create('/', 'GET', ['jenis_kelas' => 'karyawan']),
            22,
            906,
            'teori'
        );

        $this->assertSame(3, $regular->getData()['penilaian']->unique('mahasiswa_id')->count());
        $this->assertSame(1, $employee->getData()['penilaian']->unique('mahasiswa_id')->count());
    }
}
