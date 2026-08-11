<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DosenMatakuliah extends Model
{
    use HasFactory;

    protected $table = 'dosen_mata_kuliah'; // Nama tabel di database

    protected $primaryKey = 'id'; // Kolom primary key

    public $incrementing = true; // Set true jika primary key auto increment

    protected $fillable = ['dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'];

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function matakuliah()
    {
        return $this->belongsTo(Matakuliah::class, 'matakuliah_id');
    }

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }
}
