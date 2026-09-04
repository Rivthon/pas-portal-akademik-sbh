<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function tugas()
    {
        return $this->hasMany(
            LmsTugas::class,
            'dosen_id',
            'dosen_id'
        );
    }

    // Relasi ke Program Studi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function getProfileImageURL()
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'dosen_id');
    }

    public function guidanceMessages()
    {
        return $this->hasMany(KrsGuidanceMessage::class, 'dosen_id', 'dosen_id');
    }

    public function programStudiDipimpin()
    {
        return $this->hasMany(ProgramStudi::class, 'kaprodi_dosen_id', 'dosen_id');
    }

    public function rps()
    {
        return $this->hasMany(
            Rps::class,
            'dosen_id',
            'dosen_id'
        );
    }

    public function dosenMatakuliah()
    {
        return $this->hasMany(DosenMatakuliah::class, 'dosen_id');
    }

    public function penilaian()
    {
        return $this->hasMany(Penilaian::class, 'dosen_id');
    }

    public function materiLms()
    {
        return $this->hasMany(
            LmsMateri::class,
            'dosen_id',
            'dosen_id'
        );
    }
}
