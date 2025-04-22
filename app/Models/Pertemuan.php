<?php

namespace App\Models;

use App\Models\Jadwal;
use App\Models\Absensi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pertemuan extends Model
{
    use HasFactory;

    protected $table = 'pertemuan';
    protected $primaryKey = 'pertemuan_id'; // Sesuaikan jika primary key berbeda
    public $incrementing = true;
    protected $keyType = 'int';

    /**
     * Kolom yang diizinkan untuk mass assignment.
     */
    protected $fillable = [
        'jadwal_id', // Tambahkan ini
        'tanggal_pertemuan',
        'topik',
        'dosen_id',
        'sub_topik',
        'jam_mulai',
        'jam_selesai',
        'status',
    ];

    /**
     * Relasi dengan model Jadwal.
     */
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
    public function isOpen()
    {
        return $this->status === 1;
    }
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'pertemuan_id', 'pertemuan_id');
    }
}