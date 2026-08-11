<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TenorPembayaranSeeder extends Seeder
{
    /**
     * Seed tenor pembayaran untuk setiap kombinasi:
     * semester (1-8) × gelombang × tahun angkatan mahasiswa aktif.
     *
     * Skema cicilan per semester:
     *  Tenor 1 = 30%  (+1 bulan)
     *  Tenor 2 = 60%  (+2 bulan)
     *  Tenor 3 = 80%  (+3 bulan)
     *  Tenor 4 = 100% (+4 bulan) — lunas
     */
    public function run(): void
    {
        // Ambil semua gelombang yang ada
        $gelombangList = DB::table('gelombang')->pluck('id')->toArray();

        if (empty($gelombangList)) {
            $this->command->warn('Tabel gelombang kosong. Silakan seed gelombang terlebih dahulu.');

            return;
        }

        // Ambil tahun masuk dari mahasiswa aktif (unik)
        $tahunMasukList = DB::table('mahasiswa')
            ->where('status_mhs', 'aktif')
            ->select('tahun_masuk')
            ->distinct()
            ->pluck('tahun_masuk')
            ->toArray();

        if (empty($tahunMasukList)) {
            // Fallback: gunakan 3 tahun terakhir
            $currentYear = (int) date('Y');
            $tahunMasukList = [$currentYear - 2, $currentYear - 1, $currentYear];
            $this->command->info('Tidak ada mahasiswa aktif. Menggunakan fallback tahun: '.implode(', ', $tahunMasukList));
        }

        // Definisi skema tenor
        $skemaTenor = [
            ['tenor' => 'Cicilan 1', 'persentase' => 30, 'bulan_offset' => 1],
            ['tenor' => 'Cicilan 2', 'persentase' => 60, 'bulan_offset' => 2],
            ['tenor' => 'Cicilan 3', 'persentase' => 80, 'bulan_offset' => 3],
            ['tenor' => 'Lunas',     'persentase' => 100, 'bulan_offset' => 4],
        ];

        $data = [];
        $now = Carbon::now();

        foreach ($tahunMasukList as $tahunMasuk) {
            foreach ($gelombangList as $gelombangId) {
                for ($semester = 1; $semester <= 8; $semester++) {
                    foreach ($skemaTenor as $skema) {
                        // Cek apakah sudah ada agar safe untuk re-run
                        $exists = DB::table('tenor_pembayaran')
                            ->where('semester', $semester)
                            ->where('tenor', $skema['tenor'])
                            ->where('tahun_masuk', $tahunMasuk)
                            ->where('gelombang_id', $gelombangId)
                            ->exists();

                        if ($exists) {
                            continue;
                        }

                        $data[] = [
                            'semester' => $semester,
                            'tenor' => $skema['tenor'],
                            'persentase' => $skema['persentase'],
                            'batas_waktu' => $now->copy()->addMonths($skema['bulan_offset'])->format('Y-m-d'),
                            'tahun_masuk' => $tahunMasuk,
                            'gelombang_id' => $gelombangId,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }
        }

        if (! empty($data)) {
            // Insert per batch 100
            foreach (array_chunk($data, 100) as $chunk) {
                DB::table('tenor_pembayaran')->insert($chunk);
            }
            $this->command->info('Berhasil seed '.count($data).' record tenor pembayaran.');
        } else {
            $this->command->info('Semua tenor sudah ada. Tidak ada data baru yang di-seed.');
        }
    }
}
