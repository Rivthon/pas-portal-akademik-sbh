<?php

namespace App\Models;

use App\Models\Mahasiswa;
use App\Models\Matakuliah;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProgramStudi extends Model
{
    use HasFactory;

    protected $table = 'program_studi'; // Nama tabel di database

    protected $primaryKey = 'jurusan_id'; // Kolom primary key

    public $incrementing = true; // Set true jika primary key auto increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'jurusan_id', // Tambahkan ini
        'nama',
        'singkat',
        'jenjang',
        'kaprod',
        'ttd',
        'header_baak',
        'header_kapro',
        'header_dospem',
    ];

     public function mahasiswa()
    {
    return $this->hasMany(Mahasiswa::class, 'jurusan_id');
    }

}