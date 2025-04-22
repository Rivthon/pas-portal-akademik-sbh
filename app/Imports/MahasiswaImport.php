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
        return new Mahasiswa([
            'nim' => $row['nim'],
            'email' => $row['email'],
            'nama' => $row['nama'],
            'password' => Hash::make($row['password']),
            'jurusan_id' => $row['jurusan_id'],
            'tempat_lahir' => $row['tempat_lahir'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'agama' => $row['agama'],
            'nik' => $row['nik'],
            'nisn' => $row['nisn'],
            'alamat' => $row['alamat'],
            'asal_sekolah' => $row['asal_sekolah'],
            'jurusan_sekolah' => $row['jurusan_sekolah'], //baru
            'tahun_lulus' => $row['tahun_lulus'], //baru
            'no_telp' => $row['no_telp'],
            'nama_ayah' => $row['nama_ayah'],
            'no_telp_ortu' => $row['no_telp_ortu'],
            'nama_ibu' => $row['nama_ibu'],
            'no_telp_ibu' => $row['no_telp_ibu'], //baru
            'semester' => 1,
            'status_mhs' => 'aktif',
            'pekerjaan_ayah' => $row['pekerjaan_ayah'],
            'pekerjaan_ibu' => $row['pekerjaan_ibu'],
            'alamat_ortu' => $row['alamat_ortu'],
            'pendapatan_ortu' => $row['pendapatan_ortu'], //new
            'status_kip' => $row['status_kip'],
            'gelombang_id' => $row['gelombang_id'], //baru
            'tahun_masuk' => $row['tahun_masuk'],
            'kelas' => $row['kelas'],
        ]);
    }
}