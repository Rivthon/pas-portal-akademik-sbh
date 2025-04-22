<?php

namespace App\Models;

use App\Models\Mahasiswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permintaan extends Model
{
    use HasFactory;

        protected $table = 'permintaan_perubahans';

        protected $fillable = [
            'mahasiswa_id',
            'jenis_permintaan',
            'judul',
            'deskripsi',
            'prioritas',
            'file_lampiran',
            'status',
            'komentar_admin',
        ];


    public function userMahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }
}
