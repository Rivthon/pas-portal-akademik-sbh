<?php

namespace App\Models;

use Database\Factories\KegiatanTambahanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KegiatanTambahan extends Model
{
    /** @use HasFactory<KegiatanTambahanFactory> */
    use HasFactory;

    protected $table = 'kegiatan_tambahan';

    protected $fillable = [
        'mahasiswa_id',
        'kategori',
        'nama_kegiatan',
        'bentuk_kegiatan',
        'tingkat',
        'penyelenggara',
        'peran',
        'tanggal',
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
