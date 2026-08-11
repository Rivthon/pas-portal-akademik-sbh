<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $primaryKey = 'absensi_id';

    public $timestamps = true; // Jika tabel absensi menggunakan kolom created_at dan updated_at

    protected $fillable = [
        'jadwal_id',
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
        return $this->belongsTo(Pertemuan::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id');
    }
}
