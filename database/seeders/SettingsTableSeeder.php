<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('settings')->insert([
            'name' => 'Aplikasi Absensi',
            'logo' => 'default-logo.png', // Simpan logo default Anda di folder storage/app/public/logos
            'favicon' => 'default-favicon.png', // Simpan favicon default di folder storage/app/public/favicons
            'footer_name' => 'Aplikasi Absensi Footer',
            'copyright' => '© 2024 Aplikasi Absensi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
