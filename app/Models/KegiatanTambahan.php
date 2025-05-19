<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KegiatanTambahan extends Model
{
    /** @use HasFactory<\Database\Factories\KegiatanTambahanFactory> */
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
        return $this->belongsTo(Mahasiswa::class);
    }

}
