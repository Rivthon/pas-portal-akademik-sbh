<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class P2mwProgramSeeder extends Seeder
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
            DB::table('p2mw')->insert([
                'mahasiswa_id' => $id,
                'nama_usaha' => 'Gabin Rogout',
                'jenis_usaha' => 'Kuliner',
                'penyelenggara' => 'Kampus STIKes',
                'status_pendanaan' => 'Didanai',
                'tanggal' => Carbon::parse('2024-08-10'),
                'file_lampiran' => 'https://drive.google.com/file/d/EXAMPLE_LAMPIRAN_ID/view',
                'file_sertifikat' => 'https://drive.google.com/file/d/EXAMPLE_SERTIFIKAT_ID/view',
                'status_validasi' => 'menunggu',
                'catatan_validator' => null,
                'bobot' => 3,
            ]);
        }
    }
}
