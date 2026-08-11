<?php

namespace Database\Factories;

use App\Models\Dosen;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

class DosenFactory extends Factory
{
    protected $model = Dosen::class;

    public function definition(): array
    {
        $randomProdi = ProgramStudi::inRandomOrder()->first();

        return [
            // Membuat dosen_id unik bertipe string, misal: DSN1234
            'dosen_id' => 'DSN'.$this->faker->unique()->numerify('####'),
            'kd_dosen' => strtoupper($this->faker->unique()->lexify('???')), // Kode 3 huruf kapital
            'nama' => $this->faker->name(),
            'jenis_kelamin' => $this->faker->randomElement(['L', 'P']),
            'nidn' => $this->faker->unique()->numerify('00########'),
            'tempat' => $this->faker->city(),
            'tanggal_lahir' => $this->faker->dateTimeBetween('-55 years', '-30 years')->format('Y-m-d'),
            'alamat' => $this->faker->address(),
            // 'no_telp' => $this->faker->phoneNumber(),
            'status_dosen' => 'Dosen Tetap',
            'email' => $this->faker->unique()->safeEmail(),
            'avatar' => null,
            'jurusan_id' => $randomProdi ? $randomProdi->jurusan_id : 1,
            'password' => Hash::make('password'), // Password default: password
        ];
    }
}
