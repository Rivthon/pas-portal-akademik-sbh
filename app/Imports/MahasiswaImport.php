<?php

namespace App\Imports;

use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MahasiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $firstCell = trim((string) ($row['nama'] ?? ''));
        if (count(array_filter($row, static fn ($value) => trim((string) $value) !== '')) === 0
            || str_starts_with($firstCell, '*')
            || (trim((string) ($row['nim'] ?? '')) === ''
                && trim((string) ($row['jurusan_id'] ?? '')) === ''
                && trim((string) ($row['kelas'] ?? '')) === '')) {
            return null;
        }

        // Izinkan Template Cepat diproses melalui tombol import lama juga.
        if (array_key_exists('dosen', $row) && ! array_key_exists('password', $row)) {
            return (new MahasiswaImportCepat)->model($row);
        }

        $nim = trim((string) ($row['nim'] ?? ''));
        $nama = trim((string) ($row['nama'] ?? ''));
        if ($nim === '' || $nama === '' || empty($row['jurusan_id'])) {
            throw new \InvalidArgumentException('Kolom nama, nim, jurusan_id, dan kelas wajib diisi.');
        }

        $kelas = Mahasiswa::normalisasiKelasUntukPenyimpanan($row['kelas'] ?? null);
        $kelas ??= 'pagi';
        if ($kelas === null) {
            throw new \InvalidArgumentException('Kelas mahasiswa '.$nim.' harus Reguler A atau Reguler B.');
        }

        $attributes = [
            'nim' => $nim,
            'email' => trim((string) ($row['email'] ?? '')) ?: $nim.'@mahasiswa.sbh.ac.id',
            'nama' => $nama,
            'jurusan_id' => $row['jurusan_id'],
            'tempat_lahir' => $row['tempat_lahir'] ?? null,
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $row['jenis_kelamin'] ?? '',
            'agama' => $row['agama'] ?? '',
            'nik' => $row['nik'] ?? null,
            'nisn' => $row['nisn'] ?? null,
            'alamat' => $row['alamat'] ?? '',
            'asal_sekolah' => $row['asal_sekolah'] ?? '',
            'jurusan_sekolah' => $row['jurusan_sekolah'] ?? '',
            'tahun_lulus' => $row['tahun_lulus'] ?? '',
            'no_telp' => $row['no_telp'] ?? '',
            'nama_ayah' => $row['nama_ayah'] ?? '',
            'no_telp_ortu' => $row['no_telp_ortu'] ?? '',
            'nama_ibu' => $row['nama_ibu'] ?? '',
            'no_telp_ibu' => $row['no_telp_ibu'] ?? '',
            'alamat_ortu' => $row['alamat_ortu'] ?? '',
            'pendapatan_ortu' => $row['pendapatan_ortu'] ?? '',
            'gelombang_id' => $row['gelombang_id'] ?? 0,
            'tahun_masuk' => $row['tahun_masuk'] ?? 0,
            'kelas' => $kelas,
        ];

        $mahasiswa = Mahasiswa::query()
            ->whereRaw('UPPER(TRIM(nim)) = ?', [strtoupper($nim)])
            ->orderBy('mahasiswa_id')
            ->first();

        if ($mahasiswa) {
            // Password serta status akademik tidak direset oleh import ulang.
            $mahasiswa->fill($attributes);

            return $mahasiswa;
        }

        return new Mahasiswa($attributes + [
            'password' => Hash::make($nim),
            'semester' => 1,
            'status_mhs' => 'aktif',
        ]);
    }
}
