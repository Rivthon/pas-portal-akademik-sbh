<?php

namespace App\Imports;

use App\Models\TarifPerSemester;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class TarifImport implements ToModel, WithHeadingRow
{
    private $rowCount = 0;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $this->rowCount++;
        return new TarifPerSemester([
            'jurusan_id'   => $row['jurusan_id'],
            'semester'     => $row['semester'],
            'tahun_masuk'  => $row['tahun_masuk'],
            'tarif'        => $row['tarif'],
            'gelombang_id' => $row['gelombang_id'],
        ]);
    }

    public function getRowCount()
    {
        return $this->rowCount;
    }
}