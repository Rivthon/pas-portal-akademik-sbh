<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class JenisKelasLabelTest extends TestCase
{
    public function test_internal_class_values_are_mapped_to_new_display_labels(): void
    {
        $this->assertSame('Reguler A', jenis_kelas_label('reguler'));
        $this->assertSame('Reguler A', jenis_kelas_label('pagi'));
        $this->assertSame('Reguler B', jenis_kelas_label('karyawan'));
        $this->assertSame('-', jenis_kelas_label(null));
    }
}
