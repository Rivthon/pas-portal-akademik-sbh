<?php

namespace App\Models;

use App\Models\Ruangan;
use App\Models\Kurikulum;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Jadwal extends Model
{
    use HasFactory;
    protected $table = 'jadwal'; // Pastikan nama tabel sesuai
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
        return $this->belongsTo(Ruangan::class, 'ruangan_id', 'ruangan_id'); // 'ruangan_id' adalah foreign key
     }

     public function pertemuan()
     {
            return $this->hasMany(Pertemuan::class, 'jadwal_id');
     }

}