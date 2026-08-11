<?php

namespace App\Imports;

use App\Models\TarifPerSemester;
use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TarifImport implements SkipsOnFailure, ToModel, WithHeadingRow, WithValidation
{
    use SkipsFailures;

    private $rowCount = 0;

    /**
     * @return Model|null
     */
    public function model(array $row)
    {
        $this->rowCount++;

        // Handle mapping jurusan_id jika format berbeda
        $jurusanId = $row['jurusan_id'];

        return new TarifPerSemester([
            'jurusan_id' => $jurusanId,
            'semester' => $row['semester'],
            'tahun_masuk' => $row['tahun_masuk'],
            'tarif' => $row['tarif'],
            'gelombang_id' => $row['gelombang_id'],
        ]);
    }

    public function rules(): array
    {
        return [
            'jurusan_id' => 'required|exists:program_studi,jurusan_id',
            'semester' => 'required|numeric|min:1',
            'tahun_masuk' => 'required|numeric|digits:4',
            'tarif' => 'required|numeric|min:0',
            'gelombang_id' => 'required|exists:gelombang,id',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'jurusan_id.required' => 'Jurusan ID wajib diisi.',
            'jurusan_id.exists' => 'Jurusan ID tidak ditemukan di sistem (Pastikan menggunakan ID Internal 1, 2, 3, dsb).',
            'semester.required' => 'Semester wajib diisi.',
            'tahun_masuk.required' => 'Tahun masuk wajib diisi.',
            'tahun_masuk.digits' => 'Tahun masuk harus berupa 4 digit angka (misal: 2023).',
            'tarif.required' => 'Tarif wajib diisi.',
            'tarif.numeric' => 'Tarif harus berupa angka (tanpa titik koma).',
            'gelombang_id.required' => 'Gelombang ID wajib diisi.',
            'gelombang_id.exists' => 'Gelombang ID tidak ditemukan di sistem.',
        ];
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
}
