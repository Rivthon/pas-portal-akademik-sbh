<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PpsmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $mahasiswaIds = [
            151, 152, 153, 155, 157, 158, 163, 164, 165,
            170, 171, 172, 173, 174, 175, 176, 177, 182,
        ];

        foreach ($mahasiswaIds as $id) {
            DB::table('ppsm')->insert([
                'mahasiswa_id' => $id,
                'nama_kegiatan' => 'PPK Ormawa - Pelatihan Penguatan Softskill Mahasiswa',
                'tahun_kegiatan' => '2024',
                'keterangan' => 'Kegiatan pengembangan karakter mahasiswa',
                'file_sertifikat' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'file_lampiran' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'status_validasi' => 'menunggu',
                'catatan_validator' => null,
                'bobot' => 2,
            ]);
        }
    }
}
