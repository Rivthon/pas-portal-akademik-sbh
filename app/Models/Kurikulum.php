<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    protected $table = 'kurikulum';

    // Specify the primary key
    protected $primaryKey = 'kurikulum_id';

    // Disable auto-incrementing, since `matakuliah_id` is not an integer
    public $incrementing = false;

    // Specify the primary key data type
    protected $keyType = 'string';

    protected $fillable = [
        'matakuliah_id',
        'ta_id',
        'jurusan_id',
    ];

    public function mataKuliah()
    {
        return $this->belongsTo(Matakuliah::class, 'matakuliah_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    public function krs()
    {
        return $this->hasMany(Krs::class, 'kurikulum_id');
    }

    public function dosenToMatakuliah()
    {
        return $this->hasMany(DosenMatakuliah::class, 'kurikulum_id', 'kurikulum_id');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'kurikulum_id');
    }

    public function rps()
    {
        return $this->hasOne(
            Rps::class,
            'kurikulum_id',
            'kurikulum_id'
        );
    }
}
