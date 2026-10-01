<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BerkasProgramStudi extends Model
{
    protected $table = 'berkas_program_studi';

    protected $fillable = [
        'jurusan_id',
        'uploaded_by_dosen_id',
        'judul',
        'deskripsi',
        'target',
        'nama_file',
        'path',
        'mime_type',
        'ukuran',
        'aktif',
    ];

    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
            'aktif' => 'boolean',
        ];
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id', 'jurusan_id');
    }

    public function pengunggah()
    {
        return $this->belongsTo(Dosen::class, 'uploaded_by_dosen_id', 'dosen_id');
    }
}
