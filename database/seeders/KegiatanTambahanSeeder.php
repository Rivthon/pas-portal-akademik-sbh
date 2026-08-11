<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KegiatanTambahanSeeder extends Seeder
{
    public function run()
    {
        $mahasiswaIds = [
            151, 152, 153, 155, 157, 158, 163, 164, 165,
            170, 171, 172, 173, 174, 175, 176, 177, 182,
        ];

        foreach ($mahasiswaIds as $id) {
            DB::table('kegiatan_tambahan')->insert([
                'mahasiswa_id' => $id,
                'kategori' => 'Organisasi Mahasiswa',
                'nama_kegiatan' => 'Pelatihan Kepemimpinan Mahasiswa',
                'bentuk_kegiatan' => 'Pelatihan',
                'tingkat' => 'Kampus',
                'penyelenggara' => 'BEM STIKes',
                'peran' => 'Peserta',
                'tanggal' => Carbon::parse('2024-10-15'),
                'file_sertifikat' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'file_lampiran' => 'https://drive.google.com/file/d/EXAMPLE_ID/view',
                'status_validasi' => 'menunggu',
                'catatan_validator' => null,
                'bobot' => 2,
            ]);
        }
    }
}
