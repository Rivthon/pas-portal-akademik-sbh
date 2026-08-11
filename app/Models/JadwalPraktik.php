<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPraktik extends Model
{
    use HasFactory;

    protected $table = 'jadwal_praktik'; // Pastikan nama tabel sesuai

    protected $primaryKey = 'id';   // Pastikan primary key sesuai

    public $timestamps = true;      //

    protected $fillable = [
        'ta_id',
        'jurusan_id',
        'kurikulum_id',
        'jam_mulai',
        'jam_selesai',
        'hari',
        'ruangan_id',
        'jenis_kelas',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id'); // 'ruangan_id' adalah foreign key
    }

    public function pertemuan()
    {
        return $this->hasMany(PertemuanPraktik::class, 'jadwal_praktik_id', 'id');
    }

    public function absensi()
    {
        return $this->hasMany(AbsensiPraktik::class, 'jadwal_praktik_id', 'id');
    }
}
