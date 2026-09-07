<?php

namespace Tests\Unit;

use App\Models\Mahasiswa;
use PHPUnit\Framework\TestCase;

class MahasiswaClassManagementTest extends TestCase
{
    public function test_student_class_values_use_legacy_compatible_storage(): void
    {
        $this->assertSame('pagi', Mahasiswa::normalisasiKelasUntukPenyimpanan('Reguler A'));
        $this->assertSame('pagi', Mahasiswa::normalisasiKelasUntukPenyimpanan('reguler'));
        $this->assertSame('karyawan', Mahasiswa::normalisasiKelasUntukPenyimpanan('Reguler B'));
        $this->assertNull(Mahasiswa::normalisasiKelasUntukPenyimpanan(null));
    }

    public function test_student_class_label_is_displayed_as_reguler_a_or_b(): void
    {
        $regulerA = new Mahasiswa(['kelas' => 'pagi']);
        $regulerB = new Mahasiswa(['kelas' => 'karyawan']);

        $this->assertSame('Reguler A', $regulerA->label_kelas);
        $this->assertSame('Reguler B', $regulerB->label_kelas);
    }
}
