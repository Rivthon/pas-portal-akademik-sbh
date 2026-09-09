<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MahasiswaTemplateCepatExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return ['nama', 'email', 'nim', 'jurusan_id', 'kelas', 'dosen'];
    }

    public function array(): array
    {
        return [
            ['Nama Mahasiswa', '', 'NIM', '13211', 'Reguler A', 'Nama Dosen Pembimbing'],
        ];
    }
}
