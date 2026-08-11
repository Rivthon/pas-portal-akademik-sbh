<?php

namespace App\Imports;

use App\Models\JadwalUts;
use App\Models\Matakuliah;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JadwalUtsImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Ambil jurusan_id berdasarkan matakuliah_id
            $matakuliah = Matakuliah::find($row['matakuliah_id']);
            $jurusan_id = $matakuliah ? $matakuliah->jurusan_id : null;

            // Buat data
            JadwalUts::create([
                'ta_id' => $row['ta_id'],
                'matakuliah_id' => $row['matakuliah_id'],
                'hari' => $row['hari'],
                'jam_mulai' => $row['jam_mulai'],
                'jam_selesai' => $row['jam_selesai'],
                'ruangan_id' => $row['ruangan_id'],
                'jurusan_id' => $jurusan_id,
            ]);
        }
    }
}
