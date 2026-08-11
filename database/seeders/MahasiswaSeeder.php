<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MahasiswaSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new \RuntimeException('Demo student accounts must not be seeded in production.');
        }

        $jurusanIds = ProgramStudi::pluck('jurusan_id')->toArray();
        $dosenIds = Dosen::pluck('dosen_id')->toArray();

        // 1. Buat 1 Akun Mahasiswa Statis
        Mahasiswa::create([
            'nama' => 'Mahasiswa Demo',
            'email' => 'mahasiswa@demo.com',
            'password' => Hash::make('password123'),
            'nim' => '1234567890',
            'jurusan_id' => ! empty($jurusanIds) ? (string) collect($jurusanIds)->random() : 'TI',
            'dosen_id' => ! empty($dosenIds) ? collect($dosenIds)->random() : 1,
            'jenis_kelamin' => 'Laki-Laki', // Sesuai ENUM baru
            'semester' => '3',
            'status_mhs' => 'aktif', // Sesuai ENUM baru
            'kelas' => 'reguler', // Sesuai ENUM baru
            'nama_ayah' => 'Ayah Demo',
            'nama_ibu' => 'Ibu Demo',
            'agama' => 'Islam',
            'alamat' => 'Jl. Demo No. 1',
            'alamat_ortu' => 'Jl. Demo No. 1',
            'no_telp' => '081234567890',
            'no_telp_ortu' => '081234567890',
            'no_telp_ibu' => '081234567890',
            'asal_sekolah' => 'SMA Demo',
            'jurusan_sekolah' => 'IPA',
            'tahun_lulus' => '',
        ]);

        // 2. Buat 50 Akun Mahasiswa Random via Factory
        Mahasiswa::factory()->count(250)->create();

        $this->command->info('Berhasil membuat 1 Akun Demo dan 250 Mahasiswa Dummy sesuai struktur tabel baru!');
    }
}
