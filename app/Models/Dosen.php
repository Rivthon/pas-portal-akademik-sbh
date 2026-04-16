<?php

namespace App\Models;

use App\Models\Mahasiswa;
use App\Models\DosenMatakuliah;
use App\Models\Penilaian;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

     public function getProfileImageURL()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }
   public function mahasiswa()
   {
       return $this->hasMany(Mahasiswa::class, 'dosen_id');
   }

   public function dosenMatakuliah()
   {
       return $this->hasMany(DosenMatakuliah::class, 'dosen_id');
   }

   public function penilaian()
   {
       return $this->hasMany(Penilaian::class, 'dosen_id');
   }
}