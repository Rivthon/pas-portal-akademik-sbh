<?php

namespace App\Models;

use App\Models\Kurikulum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Krs extends Model
{
   use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'krs'; // Pastikan nama tabel sesuai
    protected $primaryKey = 'krs_id';   // Pastikan primary key sesuai
    public $timestamps = true;
    protected $fillable = [
        'kurikulum_id',
        'ta_id',
        'mahasiswa_id',
        'khs',
        'uts',
        'uas',
        'akhir',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'krs_id' => 'integer',
        'kurikulum_id' => 'integer',
        'ta_id' => 'integer',
        'mahasiswa_id' => 'integer',
        'khs' => 'string',
        'uts' => 'string',
        'uas' => 'string',
        'akhir' => 'string',
    ];
     public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'matakuliah_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }


       public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id', 'kurikulum_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }
     public function dosenToMatakuliah()
    {
        return $this->hasMany(DosenMatakuliah::class, 'kurikulum_id', 'kurikulum_id');
    }
}