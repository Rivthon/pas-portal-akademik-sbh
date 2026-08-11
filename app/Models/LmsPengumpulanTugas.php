<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsPengumpulanTugas extends Model
{
    protected $table = 'lms_pengumpulan_tugas';

    protected $primaryKey = 'pengumpulan_id';

    protected $fillable = [
        'tugas_id',
        'mahasiswa_id',
        'file',
        'catatan',
        'waktu_upload',
        'nilai',
        'feedback',
        'dinilai_pada',
        'dinilai_oleh',
    ];

    protected $casts = [
        'waktu_upload' => 'datetime',
        'dinilai_pada' => 'datetime',
    ];

    public function tugas()
    {
        return $this->belongsTo(
            LmsTugas::class,
            'tugas_id',
            'tugas_id'
        );
    }

    public function mahasiswa()
    {
        return $this->belongsTo(
            Mahasiswa::class,
            'mahasiswa_id',
            'mahasiswa_id'
        );
    }

    public function penilai()
    {
        return $this->belongsTo(
            Dosen::class,
            'dinilai_oleh',
            'dosen_id'
        );
    }
}
