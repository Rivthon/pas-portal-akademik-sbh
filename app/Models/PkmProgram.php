<?php

namespace App\Models;

use Database\Factories\PkmProgramFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkmProgram extends Model
{
    /** @use HasFactory<PkmProgramFactory> */
    use HasFactory;

    protected $table = 'pkm';

    protected $fillable = [
        'mahasiswa_id',
        'judul_kegiatan',
        'jenis_pkm',
        'penyelenggara',
        'tanggal',
        'prestasi',
        'file_laporan',
        'file_lampiran',
        'status_validasi',
        'catatan_validator',
        'bobot',
    ];

    /* ──────── Relasi ──────── */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }
}
