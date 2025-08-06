<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BobotNilai extends Model
{
    protected $table = 'bobot_nilai';

    protected $fillable = [
        'program_studi_id',
        'matakuliah_id',
        'persen_tugas',
        'persen_uts',
        'persen_uas',
        'persen_absen',
        'persen_praktik',
    ];
}
