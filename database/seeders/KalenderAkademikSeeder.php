<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class KalenderAkademikSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
        public function run(): void
    {
        $data = [
            [
                'jurusan_id' => 13211,
                'path' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jurusan_id' => 48201,
                'path' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'jurusan_id' => 15401,
                'path' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('calender_akademik')->insert($data);
    }

    }