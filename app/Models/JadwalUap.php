<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUap extends Model
{
    use HasFactory;

    protected $table = 'jadwal_uap'; // Pastikan nama tabel sesuai

    protected $primaryKey = 'id';   // Pastikan primary key sesuai

    public $timestamps = true;      //

    protected $fillable = [
        'ta_id',
        'jurusan_id',
        'nama',
        'jam_mulai',
        'jam_selesai',
        'tanggal',

    ];

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    // public function matakuliah()
    // {
    //     return $this->belongsTo(Matakuliah::class, 'matakuliah_id');
    // }

    //     public function ruangan()
    // {
    //     return $this->belongsTo(Ruangan::class, 'ruangan_id');
    // }
    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }
}
