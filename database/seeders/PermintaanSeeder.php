<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermintaanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['pending', 'disetujui', 'diproses', 'selesai', 'ditolak'];
        $jenisList = ['sementara', 'akhir'];

        for ($i = 0; $i < 50; $i++) {
            DB::table('pengajuan_transkrip')->insert([
                'mahasiswa_id' => rand(10, 30), // mahasiswa_id dari 10 s.d. 30
                'jenis' => $jenisList[array_rand($jenisList)],
                'keperluan' => 'Keperluan dummy ke-'.$i,
                'bukti' => 'bukti_'.$i.'.jpg',
                'status' => $statuses[array_rand($statuses)],
                'catatan' => 'Catatan untuk permintaan ke-'.$i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
