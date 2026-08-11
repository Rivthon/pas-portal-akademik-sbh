<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAkademik extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran'; // Nama tabel di database

    protected $primaryKey = 'ta_id'; // Kolom primary key

    public $incrementing = true; // Set true jika primary key auto increment

    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'ta_id',
        'nama',
        'semester',
        'status_ta',
    ];

    public function kurikulum()
    {
        return $this->hasMany(Kurikulum::class, 'ta_id');
    }

    public function krs()
    {
        return $this->hasMany(Krs::class, 'krs_id');
    }
}
