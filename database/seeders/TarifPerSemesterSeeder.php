<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TarifPerSemesterSeeder extends Seeder
{
    /**
     * Seed tarif per semester untuk angkatan 2025.
     * Mengambil semua program studi & gelombang dari database,
     * lalu generate tarif untuk semester 1-8.
     */
    public function run(): void
    {
        $tahunMasuk = '2025';

        // Ambil semua program studi
        $prodiList = DB::table('program_studi')->pluck('jurusan_id')->toArray();
        if (empty($prodiList)) {
            $this->command->warn('Tabel program_studi kosong. Seed program studi terlebih dahulu.');

            return;
        }

        // Ambil semua gelombang
        $gelombangList = DB::table('gelombang')->pluck('id')->toArray();
        if (empty($gelombangList)) {
            $this->command->warn('Tabel gelombang kosong. Seed gelombang terlebih dahulu.');

            return;
        }

        /**
         * Tarif dummy per prodi (sesuaikan dengan tarif riil jika perlu).
         * Format: jurusan_id => tarif per semester (Rp)
         */
        $tarifPerProdi = [];
        foreach ($prodiList as $prodiId) {
            // Default tarif — sesuaikan angka ini sesuai kebutuhan
            $tarifPerProdi[$prodiId] = match ((string) $prodiId) {
                '13211' => 5500000,  // Contoh: Kebidanan
                '48201' => 6000000,  // Contoh: Farmasi
                '15401' => 5000000,  // Contoh: Keperawatan
                default => 5500000,  // Default
            };
        }

        $data = [];
        $now = now();

        foreach ($prodiList as $prodiId) {
            foreach ($gelombangList as $gelombangId) {
                for ($semester = 1; $semester <= 8; $semester++) {
                    // Cek duplikat agar safe untuk re-run
                    $exists = DB::table('tarif_persemester')
                        ->where('jurusan_id', $prodiId)
                        ->where('semester', $semester)
                        ->where('tahun_masuk', $tahunMasuk)
                        ->where('gelombang_id', $gelombangId)
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    $data[] = [
                        'jurusan_id' => $prodiId,
                        'semester' => $semester,
                        'tahun_masuk' => $tahunMasuk,
                        'tarif' => $tarifPerProdi[$prodiId],
                        'gelombang_id' => $gelombangId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($data)) {
            foreach (array_chunk($data, 100) as $chunk) {
                DB::table('tarif_persemester')->insert($chunk);
            }
            $this->command->info('Berhasil seed '.count($data)." record tarif per semester (angkatan {$tahunMasuk}).");
        } else {
            $this->command->info("Semua tarif angkatan {$tahunMasuk} sudah ada. Tidak ada data baru.");
        }
    }
}
