<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        'metode_pbm',
        'status',
    ];

    /**
     * Relasi dengan model Jadwal.
     */
    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }

    public function isOpen()
    {
        return $this->status === 1;
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'pertemuan_id', 'pertemuan_id');
    }

    public function materi()
    {
        return $this->hasMany(
            LmsMateri::class,
            'pertemuan_id',
            'pertemuan_id'
        );
    }

    public function tugas()
    {
        return $this->hasMany(
            LmsTugas::class,
            'pertemuan_id',
            'pertemuan_id'
        );
    }

    public function quiz()
    {
        return $this->hasMany(LmsQuiz::class, 'pertemuan_id', 'pertemuan_id');
    }
}
