<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuizAttempt extends Model
{
    protected $table = 'lms_quiz_attempts';

    protected $primaryKey = 'attempt_id';

    protected $fillable = [
        'quiz_id', 'mahasiswa_id', 'status', 'started_at', 'urutan_soal', 'submitted_at',
        'nilai_pg', 'nilai_essay', 'nilai_total', 'feedback', 'dinilai_at',
        'dinilai_oleh', 'izinkan_ulang',
    ];

    protected $casts = [
        'started_at' => 'datetime', 'urutan_soal' => 'array', 'submitted_at' => 'datetime',
        'dinilai_at' => 'datetime', 'izinkan_ulang' => 'boolean',
        'nilai_pg' => 'decimal:2', 'nilai_essay' => 'decimal:2', 'nilai_total' => 'decimal:2',
    ];

    public function quiz()
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function jawaban()
    {
        return $this->hasMany(LmsQuizJawaban::class, 'attempt_id');
    }

    public function penilai()
    {
        return $this->belongsTo(Dosen::class, 'dinilai_oleh', 'dosen_id');
    }
}
