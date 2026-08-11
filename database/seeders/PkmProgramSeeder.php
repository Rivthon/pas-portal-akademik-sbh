<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PkmProgramSeeder extends Seeder
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
            DB::table('pkm')->insert([
                'mahasiswa_id' => $id,
                'judul_kegiatan' => 'Pengembangan Aplikasi Edukasi Gizi',
                'jenis_pkm' => 'PKM-KC',
                'penyelenggara' => 'Kemdikbud',
                'tanggal' => Carbon::parse('2024-11-01'),
                'prestasi' => 'Finalis Wilayah',
                'file_laporan' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'file_lampiran' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'status_validasi' => 'menunggu',
                'catatan_validator' => null,
                'bobot' => 3,
            ]);
        }
    }
}
