<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsTugas extends Model
{
    protected $table = 'lms_tugas';

    protected $primaryKey = 'tugas_id';

    protected $fillable = [
        'jadwal_id',
        'pertemuan_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'deadline',
        'nilai_maksimal',
        'lampiran',
        'aktif',
        'izinkan_terlambat',
        'izinkan_upload_ulang',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'aktif' => 'boolean',
        'izinkan_terlambat' => 'boolean',
        'izinkan_upload_ulang' => 'boolean',
    ];

    public function pengumpulan()
    {
        return $this->hasMany(
            LmsPengumpulanTugas::class,
            'tugas_id',
            'tugas_id'
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

    public function jadwal()
    {
        return $this->belongsTo(
            Jadwal::class,
            'jadwal_id',
            'id'
        );
    }
}
