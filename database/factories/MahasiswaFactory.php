<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class MahasiswaFactory extends Factory
{
    protected $model = Mahasiswa::class;

    public function definition(): array
    {
        $randomProdi = ProgramStudi::inRandomOrder()->first();
        $randomDosen = Dosen::inRandomOrder()->first();

        return [
            // Foreign Keys
            'jurusan_id' => $randomProdi ? (string) $randomProdi->jurusan_id : 'TI', // VARCHAR(10)
            'dosen_id' => $randomDosen ? $randomDosen->dosen_id : 1, // INT
            'gelombang_id' => 1, // BIGINT

            // Data Akademik
            'nim' => $this->faker->unique()->numerify('2023######'), // VARCHAR(30)
            'nisn' => $this->faker->unique()->numerify('00########'), // VARCHAR(100)
            'asal_sekolah' => 'SMA '.$this->faker->company(), // VARCHAR(100)
            'jurusan_sekolah' => $this->faker->randomElement(['IPA', 'IPS', 'SMK IT']), // VARCHAR(100)
            'tahun_masuk' => $this->faker->numberBetween(2020, 2023), // INT
            'tahun_lulus' => '', // VARCHAR(50)
            'semester' => (string) $this->faker->numberBetween(1, 8), // VARCHAR(2)

            // Enum Status & Kelas sesuai struktur SQL
            'status_mhs' => $this->faker->randomElement(['aktif', 'cuti', 'nonaktif']),
            'kelas' => $this->faker->randomElement(['pagi', 'reguler', 'karyawan']),

            // Data Pribadi
            'nama' => $this->faker->name(), // VARCHAR(60)
            'jenis_kelamin' => $this->faker->randomElement(['Laki-Laki', 'Perempuan']), // ENUM
            'tempat_lahir' => $this->faker->city(), // VARCHAR(50)
            'tanggal_lahir' => $this->faker->date(), // DATE
            'agama' => $this->faker->randomElement(['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha']), // VARCHAR(191)
            'alamat' => $this->faker->address(), // VARCHAR(225)
            'kota' => $this->faker->city(), // VARCHAR(50)
            'nik' => $this->faker->unique()->numerify('3271##########'), // BIGINT

            // Akun
            'email' => $this->faker->unique()->safeEmail(), // VARCHAR(50)
            'password' => Hash::make('password'), // VARCHAR(150)
            'avatar' => null, // VARCHAR(255)

            // Kontak & Nomor Telepon (Dibatasi numerify agar tidak melanggar limit VARCHAR)
            'no_telp' => $this->faker->numerify('08##########'), // VARCHAR(20)

            // Data Orang Tua
            'nama_ayah' => $this->faker->name('male'), // VARCHAR(60)
            'nama_ibu' => $this->faker->name('female'), // VARCHAR(60)
            'alamat_ortu' => $this->faker->address(), // VARCHAR(225)
            'no_telp_ortu' => $this->faker->numerify('08##########'), // VARCHAR(15)
            'no_telp_ibu' => $this->faker->numerify('08##########'), // VARCHAR(15)
            'pendapatan_ortu' => $this->faker->randomElement(['< 1 Juta', '1-3 Juta', '3-5 Juta', '> 5 Juta']), // VARCHAR(255)

            // Status Akademik (INT)
            'status_krs' => $this->faker->randomElement([0, 1]),
            'status_uts' => $this->faker->randomElement([0, 1]),
            'status_uas' => $this->faker->randomElement([0, 1]),
            'status_nilai_uts' => $this->faker->randomElement([0, 1]),
            'status_nilai_uas' => $this->faker->randomElement([0, 1]),
            'status_nilai_khs' => $this->faker->randomElement([0, 1]),
            'status_uap' => $this->faker->randomElement([0, 1]),
            'status_pra_uap' => $this->faker->randomElement([0, 1]),
            'status_edom' => $this->faker->randomElement([0, 1]),
            'status_akhir' => $this->faker->randomElement([0, 1]),
        ];
    }
}
