<?php

namespace App\Models;

use App\Models\Krs;
use App\Models\Jadwal;
use App\Models\ProgramStudi;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Matakuliah extends Model
{
    use HasFactory;

    protected $table = 'matakuliah';

    // Specify the primary key
    protected $primaryKey = 'matakuliah_id';

    // Disable auto-incrementing, since `matakuliah_id` is not an integer
    public $incrementing = false;

    // Specify the primary key data type
    protected $keyType = 'string';

    protected $fillable = [
        'matakuliah_id',
        'jurusan_id',
        'nama',
        'kategori_mk',
        'sks',
        'smt',
        'semester'
    ];

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function kurikulum()
    {
        return $this->hasMany(Kurikulum::class, 'matakuliah_id');
    }
      public function krs()
    {
        return $this->hasMany(Krs::class, 'matakuliah_id');
    }
    public function penilaian()
    {
        return $this->hasManyThrough(Penilaian::class, Kurikulum::class, 'matakuliah_id', 'kurikulum_id', 'matakuliah_id', 'kurikulum_id');
    }
    public function getSemesterLabelAttribute()
        {
            if (!$this->smt) {
                return null;
            }
            return "Semester {$this->smt} (" . ($this->smt % 2 == 1 ? 'Ganjil' : 'Genap') . ")";
        }
}
