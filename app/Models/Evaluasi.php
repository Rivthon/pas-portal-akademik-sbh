<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluasi extends Model
{
    use HasFactory;

    protected $table = 'evaluasi'; // Nama tabel di database

    protected $primaryKey = 'eval_id'; // Kolom primary key

    public $incrementing = true; // Set true jika primary key auto increment

    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'eval_id',
        'nama',
    ];

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'evaluasi_id');
    }
}
