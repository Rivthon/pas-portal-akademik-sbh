<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gelombang extends Model
{
     use HasFactory;

    protected $table = 'gelombang'; // Nama tabel di database

    protected $primaryKey = 'id'; // Kolom primary key

    public $incrementing = true; // Set true jika primary key auto increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'nama',
    ];
        public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'gelombang_id');
    }
    public function tarifPerSemester()
    {
        return $this->hasMany(TarifPerSemester::class, 'gelombang_id');
    }

}