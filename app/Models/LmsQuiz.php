<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuiz extends Model
{
    protected $table = 'lms_quiz';

    protected $primaryKey = 'quiz_id';

    protected $fillable = [
        'jadwal_id', 'pertemuan_id', 'dosen_id', 'judul', 'deskripsi',
        'mulai_at', 'deadline', 'durasi_menit', 'aktif',
    ];

    protected $casts = [
        'mulai_at' => 'datetime', 'deadline' => 'datetime', 'aktif' => 'boolean',
    ];

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }

    public function soal()
    {
        return $this->hasMany(LmsQuizSoal::class, 'quiz_id')->orderBy('urutan');
    }

    public function attempts()
    {
        return $this->hasMany(LmsQuizAttempt::class, 'quiz_id');
    }
}
