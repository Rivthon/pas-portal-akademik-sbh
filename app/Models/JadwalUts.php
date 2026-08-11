<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalUts extends Model
{
    use HasFactory;

    protected $table = 'jadwal_uts'; // Pastikan nama tabel sesuai

    protected $primaryKey = 'id';   // Pastikan primary key sesuai

    public $timestamps = true;      //

    protected $fillable = [
        'ta_id',
        'jurusan_id',
        'matakuliah_id',
        'jam_mulai',
        'jam_selesai',
        'tanggal',
        'ruangan_id',
        'jenis_kelas',
    ];

    public function matakuliah()
    {
        return $this->belongsTo(Matakuliah::class, 'matakuliah_id');
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
}
