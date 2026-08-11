<?php

namespace App\Models;

use Database\Factories\PpsmFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ppsm extends Model
{
    /** @use HasFactory<PpsmFactory> */
    use HasFactory;

    protected $table = 'ppsm';

    protected $fillable = [
        'mahasiswa_id',
        'nama_kegiatan',
        'tahun_kegiatan',
        'keterangan',
        'file_sertifikat',
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
