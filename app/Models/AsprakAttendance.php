<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsprakAttendance extends Model
{
    protected $table = 'asprak_absensi';

    protected $fillable = [
        'pertemuan_praktik_id',
        'asprak_penugasan_id',
        'status',
        'keterangan',
        'diabsen_oleh_dosen_id',
    ];

    public function pertemuan()
    {
        return $this->belongsTo(PertemuanPraktik::class, 'pertemuan_praktik_id', 'pertemuan_praktik_id');
    }

    public function assignment()
    {
        return $this->belongsTo(AsprakAssignment::class, 'asprak_penugasan_id');
    }

    public function diabsenOleh()
    {
        return $this->belongsTo(Dosen::class, 'diabsen_oleh_dosen_id', 'dosen_id');
    }
}
