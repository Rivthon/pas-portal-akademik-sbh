<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertemuanPraktik extends Model
{
    use HasFactory;

    protected $table = 'pertemuan_praktik';
    protected $primaryKey = 'pertemuan_praktik_id'; // Sesuaikan jika primary key berbeda
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Kolom yang diizinkan untuk mass assignment.
     */
    protected $fillable = [
        'jadwal_praktik_id', // Tambahkan ini
        'tanggal_pertemuan',
        'topik',
        'dosen_id',
        'sub_topik',
        'jam_mulai',
        'jam_selesai',
        'status',
    ];
    public function jadwal()
    {
        return $this->belongsTo(JadwalPraktik::class, 'jadwal_praktik_id');
    }
    public function isOpen()
    {
        return $this->status === 1;
    }
    public function absensi()
    {
        return $this->hasMany(AbsensiPraktik::class, 'pertemuan_praktik_id', 'pertemuan_praktik_id');
    }
}
