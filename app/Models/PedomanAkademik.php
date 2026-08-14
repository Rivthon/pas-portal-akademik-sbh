<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PedomanAkademik extends Model
{
    protected $table = 'pedoman_akademik';

    protected $fillable = [
        'judul',
        'tahun_berlaku',
        'nama_file',
        'path',
        'status',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'uploaded_by' => 'integer',
        ];
    }

    public function pengunggah()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
