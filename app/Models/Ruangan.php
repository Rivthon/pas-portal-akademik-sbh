<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $primaryKey = 'ruangan_id';

    public $incrementing = true; // Set true jika primary key auto increment
    protected $keyType = 'int'; // Tipe data primary key

    protected $fillable = [
        'nama',
    ];
}
