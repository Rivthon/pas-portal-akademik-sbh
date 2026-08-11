<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsQuizSoal extends Model
{
    protected $table = 'lms_quiz_soal';

    protected $primaryKey = 'soal_id';

    protected $fillable = ['quiz_id', 'tipe', 'pertanyaan', 'opsi', 'kunci_jawaban', 'bobot', 'urutan'];

    protected $casts = ['opsi' => 'array', 'bobot' => 'decimal:2'];

    public function quiz()
    {
        return $this->belongsTo(LmsQuiz::class, 'quiz_id');
    }

    public function jawaban()
    {
        return $this->hasMany(LmsQuizJawaban::class, 'soal_id');
    }
}
