<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DosenSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo lecturer accounts must not be seeded in production.');
        }

        $jurusanIds = ProgramStudi::pluck('jurusan_id')->toArray();

        // 1. Buat 1 Dosen Statis untuk testing login Dosen
        Dosen::create([
            'dosen_id' => 'DSN0001',
            'kd_dosen' => 'BDI',
            'nama' => 'Budi Dosen Demo, S.Kom., M.Kom.',
            'email' => 'dosen@demo.com',
            'password' => Hash::make('password123'),
            'jenis_kelamin' => 'L',
            'nidn' => '0012345678',
            'tempat' => 'Jakarta',
            'tanggal_lahir' => '1980-01-01',
            'status_dosen' => 'Dosen Tetap',
            'alamat' => 'Jakarta',
            'jurusan_id' => ! empty($jurusanIds) ? collect($jurusanIds)->random() : 1,
        ]);

        // 2. Buat 15 Dosen Acak
        Dosen::factory()->count(15)->create();
    }
}
