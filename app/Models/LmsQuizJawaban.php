<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuizJawaban extends Model
{
    protected $table = 'lms_quiz_jawaban';

    protected $primaryKey = 'jawaban_id';

    protected $fillable = [
        'attempt_id', 'soal_id', 'pilihan_jawaban', 'jawaban_text',
        'file', 'nilai', 'feedback',
    ];

    protected $casts = ['nilai' => 'decimal:2'];

    public function attempt()
    {
        return $this->belongsTo(LmsQuizAttempt::class, 'attempt_id');
    }

    public function soal()
    {
        return $this->belongsTo(LmsQuizSoal::class, 'soal_id');
    }
}
