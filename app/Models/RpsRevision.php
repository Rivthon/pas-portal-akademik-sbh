<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RpsRevision extends Model
{
    protected $fillable = [
        'rps_id',
        'kurikulum_id',
        'jenis_kelas',
        'previous_dosen_id',
        'uploaded_by_dosen_id',
        'previous_file_name',
        'new_file_name',
    ];

    public function rps()
    {
        return $this->belongsTo(Rps::class, 'rps_id', 'rps_id');
    }

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id', 'kurikulum_id');
    }

    public function previousDosen()
    {
        return $this->belongsTo(Dosen::class, 'previous_dosen_id', 'dosen_id');
    }

    public function uploader()
    {
        return $this->belongsTo(Dosen::class, 'uploaded_by_dosen_id', 'dosen_id');
    }
}
