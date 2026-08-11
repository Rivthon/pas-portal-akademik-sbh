<?php

namespace Database\Seeders;

use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;

class ProgramStudiSeeder extends Seeder
{
    public function run(): void
    {
        $prodi = [
            [
                'jurusan_id' => 1, // Ditulis manual sesuai keinginanmu
                'nama' => 'Teknik Informatika',
                'singkat' => 'TI',
                'jenjang' => 'S1',
                'kaprod' => 'Dr. Budi Santoso, M.Kom',
                'ttd' => null,
                'header_baak' => null,
                'header_kapro' => null,
                'header_dospem' => null,
                'header_mhs' => null,
                'persen_tugas' => 20,
                'persen_uts' => 30,
                'persen_uas' => 40,
                'persen_absen' => 10,
                'persen_praktik' => 0,
            ],
            [
                'jurusan_id' => 2, // Ditulis manual
                'nama' => 'Sistem Informasi',
                'singkat' => 'SI',
                'jenjang' => 'S1',
                'kaprod' => 'Siti Aminah, S.Kom., M.T.',
                'ttd' => null,
                'header_baak' => null,
                'header_kapro' => null,
                'header_dospem' => null,
                'header_mhs' => null,
                'persen_tugas' => 20,
                'persen_uts' => 30,
                'persen_uas' => 40,
                'persen_absen' => 10,
                'persen_praktik' => 0,
            ],
            [
                'jurusan_id' => 3, // Ditulis manual
                'nama' => 'Manajemen Informatika',
                'singkat' => 'MI',
                'jenjang' => 'D3',
                'kaprod' => 'Andi Wijaya, M.Kom',
                'ttd' => null,
                'header_baak' => null,
                'header_kapro' => null,
                'header_dospem' => null,
                'header_mhs' => null,
                'persen_tugas' => 15,
                'persen_uts' => 25,
                'persen_uas' => 30,
                'persen_absen' => 10,
                'persen_praktik' => 20,
            ],
        ];

        foreach ($prodi as $data) {
            ProgramStudi::create($data);
        }

        $this->command->info('Seeder Program Studi dengan ID manual berhasil dijalankan!');
    }
}
