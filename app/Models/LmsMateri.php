<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsMateri extends Model
{
    protected $table = 'lms_materi';

    protected $primaryKey = 'materi_id';

    protected $fillable = [
        'pertemuan_id',
        'jadwal_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'tipe',
        'file',
        'youtube_url',
        'status',
    ];

    public function jadwal()
    {
        return $this->belongsTo(
            Jadwal::class,
            'jadwal_id',
            'id'
        );
    }

    public function pertemuan()
    {
        return $this->belongsTo(
            Pertemuan::class,
            'pertemuan_id',
            'pertemuan_id'
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
