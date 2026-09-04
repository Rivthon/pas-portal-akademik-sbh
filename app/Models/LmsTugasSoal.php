<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LmsTugasSoal extends Model
{
    protected $table = 'lms_tugas_soal';

    protected $primaryKey = 'soal_id';

    protected $fillable = [
        'tugas_id',
        'pertanyaan',
        'opsi',
        'kunci_jawaban',
        'bobot',
        'urutan',
    ];

    protected $casts = [
        'opsi' => 'array',
        'kunci_jawaban' => 'integer',
        'bobot' => 'decimal:2',
    ];

    public function tugas()
    {
        return $this->belongsTo(LmsTugas::class, 'tugas_id', 'tugas_id');
    }
}
