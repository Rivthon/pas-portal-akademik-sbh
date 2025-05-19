<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PenguasaanBahasa extends Model
{
    /** @use HasFactory<\Database\Factories\PenguasaanBahasaFactory> */
    use HasFactory;
    protected $table = 'penguasaan_bahasa'; // nama tabel migration
    protected $fillable = [
        'mahasiswa_id',
        'nama_bahasa',
        'level',
        'penyelenggara',
        'tanggal_tes',
        'skor',
        'file_sertifikat',
        'status_validasi',
        'catatan_validator',
        'bobot',
    ];
    /* ──────── Relasi ──────── */
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function getStatusValidasiAttribute($value)
    {
        return $value === 'Menunggu' ? 'Menunggu' : ($value === 'Disetujui' ? 'Disetujui' : ($value === 'Ditolak' ? 'Ditolak' : 'Ditinjau'));
    }
}
