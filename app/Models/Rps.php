<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rps extends Model
{
    protected $table = 'rps';

    protected $primaryKey = 'rps_id';

    protected $fillable = [
        'kurikulum_id',
        'dosen_id',
        'jenis_kelas',
        'nama_file',
        'file',
        'status',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(
            Kurikulum::class,
            'kurikulum_id',
            'kurikulum_id'
        );
    }

    public function dosen()
    {
        return $this->belongsTo(
            Dosen::class,
            'dosen_id',
            'dosen_id'
        );
    }
}
