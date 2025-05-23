<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ppsm extends Model
{
    /** @use HasFactory<\Database\Factories\PpsmFactory> */
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