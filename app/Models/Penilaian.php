<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $primaryKey = 'id'; // Sesuaikan jika primary key berbeda

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'mahasiswa_id',
        'dosen_id',
        'kurikulum_id',
        'evaluasi_id',
        'nilai',
        'jenis_dosen',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class, 'mahasiswa_id');
    }

    // Relasi ke model Kurikulum
    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    // Relasi ke model Evaluasi
    public function evaluasi()
    {
        return $this->belongsTo(Evaluasi::class, 'evaluasi_id');
    }

    // Relasi ke model Dosen
    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }
}
