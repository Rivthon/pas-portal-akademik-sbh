<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KrsGuidanceMessage extends Model
{
    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'ta_id',
        'sender_type',
        'message',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id', 'ta_id');
    }
}
