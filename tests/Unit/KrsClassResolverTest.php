<?php

namespace Tests\Unit;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Support\KrsClassResolver;
use PHPUnit\Framework\TestCase;

class KrsClassResolverTest extends TestCase
{
    public function test_krs_override_takes_priority_over_student_main_class(): void
    {
        $mahasiswa = new Mahasiswa(['kelas' => 'pagi']);
        $krs = new Krs([
            'kurikulum_id' => 1042,
            'ta_id' => 20,
            'jenis_kelas' => 'karyawan',
        ]);
        $jadwalB = new Jadwal([
            'kurikulum_id' => 1042,
            'ta_id' => 20,
            'jenis_kelas' => 'karyawan',
        ]);
        $jadwalA = new Jadwal([
            'kurikulum_id' => 1042,
            'ta_id' => 20,
            'jenis_kelas' => 'reguler',
        ]);

        $this->assertSame('karyawan', KrsClassResolver::forKrs($krs, $mahasiswa));
        $this->assertTrue(KrsClassResolver::matches($krs, $jadwalB, $mahasiswa));
        $this->assertFalse(KrsClassResolver::matches($krs, $jadwalA, $mahasiswa));
    }

    public function test_legacy_krs_without_override_follows_student_main_class(): void
    {
        $mahasiswa = new Mahasiswa(['kelas' => 'pagi']);
        $krs = new Krs(['kurikulum_id' => 1042, 'ta_id' => 20]);
        $jadwal = new Jadwal([
            'kurikulum_id' => 1042,
            'ta_id' => 20,
            'jenis_kelas' => 'reguler',
        ]);

        $this->assertSame('reguler', KrsClassResolver::forKrs($krs, $mahasiswa));
        $this->assertTrue(KrsClassResolver::matches($krs, $jadwal, $mahasiswa));
    }
}
