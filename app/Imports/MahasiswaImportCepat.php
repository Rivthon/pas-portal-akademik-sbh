<?php

namespace App\Imports;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MahasiswaImportCepat implements ToModel, WithHeadingRow
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

        $nim = trim((string) ($row['nim'] ?? ''));
        $nama = trim((string) ($row['nama'] ?? ''));
        $kelas = Mahasiswa::normalisasiKelasUntukPenyimpanan($row['kelas'] ?? null);
        // Jika kolom kelas dikosongkan pada Template Cepat, gunakan Reguler A.
        $kelas ??= 'pagi';

        if ($nim === '' || $nama === '' || empty($row['jurusan_id']) || $kelas === null) {
            throw new \InvalidArgumentException('Kolom nama, nim, jurusan_id, dan kelas wajib diisi.');
        }

        $dosenId = null;
        if (trim((string) ($row['dosen'] ?? '')) !== '') {
            $dosenId = Dosen::whereRaw('LOWER(TRIM(nama)) = ?', [strtolower(trim($row['dosen']))])->value('dosen_id');
            if (! $dosenId) {
                throw new \InvalidArgumentException('Dosen pembimbing tidak ditemukan: '.$row['dosen']);
            }
        }

        $attributes = [
            'nama' => $nama,
            'nim' => $nim,
            'email' => trim((string) ($row['email'] ?? '')) ?: $nim.'@mahasiswa.sbh.ac.id',
            'jurusan_id' => $row['jurusan_id'],
            'kelas' => $kelas,
            'dosen_id' => $dosenId,
        ];

        $mahasiswa = Mahasiswa::query()
            ->whereRaw('UPPER(TRIM(nim)) = ?', [strtoupper($nim)])
            ->orderBy('mahasiswa_id')
            ->first();

        if ($mahasiswa) {
            // Import ulang memperbarui data, tetapi tidak mereset password dan
            // tidak mengosongkan dospem jika kolom dosen pada Excel dikosongkan.
            if ($dosenId === null) {
                unset($attributes['dosen_id']);
            }

            $mahasiswa->fill($attributes);

            return $mahasiswa;
        }

        return new Mahasiswa($attributes + [
            'password' => Hash::make($nim),
            'nama_ibu' => '', 'nama_ayah' => '', 'jenis_kelamin' => '', 'agama' => '',
            'alamat' => '', 'no_telp' => '', 'no_telp_ortu' => '', 'no_telp_ibu' => '',
            'alamat_ortu' => '', 'asal_sekolah' => '', 'jurusan_sekolah' => '',
            'tahun_lulus' => '', 'tahun_masuk' => 0, 'gelombang_id' => 0,
            'pendapatan_ortu' => '', 'semester' => 1, 'status_mhs' => 'aktif',
        ]);
    }
}
