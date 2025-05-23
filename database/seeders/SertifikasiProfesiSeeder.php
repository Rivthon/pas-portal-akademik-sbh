<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SertifikasiProfesiSeeder extends Seeder
{
    public function run()
    {
        $mahasiswaIds = [
            151, 152, 153, 155, 157, 158, 163, 164, 165,
            170, 171, 172, 173, 174, 175, 176, 177, 182
        ];

        foreach ($mahasiswaIds as $id) {
            DB::table('sertifikasi_profesi_kompetensi')->insert([
                'mahasiswa_id'     => $id,
                'nama_kegiatan'    => 'Pelatihan Kompetensi Dasar',
                'penyelenggara'    => 'STIKes Bogor Husada',
                'tingkat_kegiatan' => 'Nasional',
                'prestasi'         => 'Peserta',
                'tanggal'          => Carbon::parse('2024-10-01'),
                'dokumen_pendukung'=> 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'jenis_sertifikat' => 'PDF',
                'file_sertifikat'  => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'status_validasi'  => 'menunggu',
                'catatan_validator'=> null,
                'bobot'            => 2,
            ]);
        }
    }
}