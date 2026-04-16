<?php

namespace App\Models;

use App\Models\Ruangan;
use App\Models\Matakuliah;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
