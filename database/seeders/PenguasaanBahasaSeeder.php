<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenguasaanBahasaSeeder extends Seeder
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
            DB::table('penguasaan_bahasa')->insert([
                'mahasiswa_id' => $id,
                'nama_bahasa' => 'Bahasa Inggris',
                'level' => 'Intermediate',
                'penyelenggara' => 'British Council',
                'tanggal_tes' => Carbon::parse('2024-11-01'),
                'skor' => 550,
                'file_sertifikat' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'status_validasi' => 'menunggu',
                'catatan_validator' => null,
                'bobot' => 2,
            ]);
        }
    }
}
