<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TenorPembayaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
     public function run(): void
    {
        $data = [];

        // Loop untuk semester 1 sampai 8
        for ($semester = 1; $semester <= 8; $semester++) {
            // Loop untuk tenor 1 sampai 5
            for ($tenor = 1; $tenor <= 5; $tenor++) {
                $persentase = ($tenor == 5) ? 100 : (100 / 5) * $tenor; // Tenor ke-5 = 100%

                $data[] = [
                    'semester' => $semester,
                    'tenor' => $tenor,
                    'persentase' => $persentase,
                    'batas_waktu' => Carbon::now()->addMonths($tenor)->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert data ke database
        DB::table('tenor_pembayaran')->insert($data);
    }
}