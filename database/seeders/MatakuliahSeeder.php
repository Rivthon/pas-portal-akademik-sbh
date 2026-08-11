<?php

namespace Database\Seeders;

use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class MatakuliahSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // 1. Ambil maksimal 3 ID jurusan yang ada di tabel program_studi
        $jurusanIds = ProgramStudi::pluck('jurusan_id')->take(3)->toArray();

        // Jika tabel prodi masih kosong, kita siapkan fallback dummy ID
        if (empty($jurusanIds)) {
            $jurusanIds = ['TI', 'SI', 'MI']; // Disesuaikan dengan VARCHAR(10)
        }

        // Kumpulan kosa kata agar nama matakuliah acaknya tetap bernuansa akademik
        $kataDepan = ['Pengantar', 'Sistem', 'Manajemen', 'Rekayasa', 'Algoritma', 'Praktikum', 'Konsep', 'Desain', 'Arsitektur', 'Analisis', 'Keamanan'];
        $kataBelakang = ['Informasi', 'Komputer', 'Basis Data', 'Lanjut', 'Dasar', 'Web', 'Jaringan', 'Perangkat Lunak', 'Multimedia', 'Bisnis', 'Siber'];

        $matakuliahData = [];

        // 2. Looping untuk setiap jurusan (Maksimal 3 jurusan)
        foreach ($jurusanIds as $jurusanId) {

            // 3. Looping untuk Semester 1 sampai 8
            for ($smt = 1; $smt <= 8; $smt++) {

                // Tentukan semester Ganjil atau Genap secara matematis
                $semesterText = ($smt % 2 != 0) ? 'Ganjil' : 'Genap';

                // Bikin acak jumlah matakuliah per semesternya (misal: 4 sampai 6 matakuliah)
                $jumlahMk = rand(4, 6);

                // Jika semester 8, biasanya matakuliahnya sedikit (Skripsi/Tugas Akhir)
                if ($smt == 8) {
                    $jumlahMk = rand(1, 2);
                }

                for ($i = 1; $i <= $jumlahMk; $i++) {
                    // Membuat ID Matakuliah yang unik dan tidak lebih dari 10 karakter
                    // Contoh hasil: TI101, SI802
                    $urutan = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $rawId = strtoupper(substr($jurusanId, 0, 3)).$smt.$urutan;
                    $mkId = substr($rawId, 0, 10);

                    // Merangkai nama matakuliah acak
                    $namaMkAcak = $faker->randomElement($kataDepan).' '.$faker->randomElement($kataBelakang);

                    // Jika semester 8, paksa namanya jadi Skripsi/Tugas Akhir
                    if ($smt == 8 && $i == 1) {
                        $namaMkAcak = 'Tugas Akhir / Skripsi';
                    }

                    $matakuliahData[] = [
                        'matakuliah_id' => $mkId,
                        'jurusan_id' => $jurusanId,
                        'nama' => substr($namaMkAcak, 0, 50), // Pastikan max 50 karakter
                        'kategori_mk' => rand(0, 1), // 0 misal untuk Wajib, 1 untuk Pilihan
                        'sks' => ($smt == 8) ? 6 : rand(2, 4), // Skripsi 6 SKS, lainnya 2-4 SKS
                        'smt' => (string) $smt, // ENUM '1'-'9'
                        'semester' => $semesterText, // VARCHAR(25)
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        // 4. Masukkan semua data ke database sekaligus
        // insertOrIgnore digunakan agar jika ada ID yang kebetulan sama, tidak menyebabkan error
        Matakuliah::insertOrIgnore($matakuliahData);

        $this->command->info('Berhasil membuat data Matakuliah acak untuk '.count($jurusanIds).' Prodi (Semester 1-8)!');
    }
}
