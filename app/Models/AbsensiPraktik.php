<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiPraktik extends Model
{
    use HasFactory;

    protected $table = 'absensi_praktik';
    protected $primaryKey = 'absensi_praktik_id';
    public $timestamps = true; // Jika tabel absensi_praktik menggunakan kolom created_at dan updated_at

    protected $fillable = [
        'jadwal_praktik_id',
        'mahasiswa_id',
        'tanggal',
        'status',
        'keterangan',
        'pertemuan_id',
    ];

    // Relasi ke model Mahasiswa
    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }
    public function pertemuan()
    {
        return $this->belongsTo(PertemuanPraktik::class, 'pertemuan_praktik_id', 'pertemuan_praktik_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(JadwalPraktik::class, 'jadwal_praktik_id', 'id');
    }
}
