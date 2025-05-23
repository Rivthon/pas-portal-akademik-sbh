<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class P2mw_program extends Model
{
    /** @use HasFactory<\Database\Factories\P2mwProgramFactory> */
    use HasFactory;
    protected $table = 'p2mw';

    protected $fillable = [
        'mahasiswa_id',
        'nama_usaha',
        'jenis_usaha',
        'penyelenggara',
        'status_pendanaan',
        'tanggal',
        'file_lampiran',
        'file_sertifikat',
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
