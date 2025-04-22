<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;

class Dosen extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'dosen';

    protected $primaryKey = 'dosen_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'dosen_id',
        'kd_dosen',
        'nama',
        'jenis_kelamin',
        'nidn',
        'tempat',
        'tanggal_lahir',
        'alamat',
        'no_telp',
        'status_dosen',
        'email',
        'avatar',
        'jurusan_id',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

     public function getProfileImageURL()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
}
