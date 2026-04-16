<?php

namespace App\Models;

use App\Models\Krs;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\Absensi;
use App\Models\Permintaan;
use App\Models\ProgramStudi;
use App\Models\TagihanMahasiswa;
use App\Models\PengajuanTranskrip;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Mahasiswa extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'mahasiswa'; // Nama tabel di database
    protected $primaryKey = 'mahasiswa_id'; // Primary key yang digunakan
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true; // Pastikan timestamps aktif jika tabel menggunakan kolom 'created_at' dan 'updated_at'

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'nama',
        'avatar',
        'email',
        'password',
        'jurusan_id',
        'nim',
        'nisn',
        'nik',
        'nama_ibu',
        'jenis_kelamin',
        'tanggal_lahir',
        'tempat_lahir',
        'agama',
        'alamat',
        'kota',
        'no_telp',
        'nama_ayah',
        'no_telp_ortu',
        'no_telp_ibu',
        'alamat_ortu',
        'semester',
        'asal_sekolah',
        'tahun_masuk',
        'tahun_lulus',
        'jurusan_sekolah',
        'status',
        'gelombang_id',
        'kelas',
        'status_mhs',
        'pendapatan_ortu',
        'status_krs',
        'status_uts',
        'status_uas',
        'status_akhir',
        'status_nilai_uts',
        'status_nilai_uas',
        'status_nilai_akhir',
        'status_nilai_khs',
        'status_uap',
        'status_pra_uap',
        'status_edom',
        'login_time',
        'ip_address',
        'verfikasi',
        'dosen_id',
        'updated_at',
        'created_at',

    ];

    /**
     * Kolom yang disembunyikan saat data dikembalikan.
     */
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relasi ke model ProgramStudi
    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id', 'jurusan_id');
    }

    // Relasi ke model Absensi
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'mahasiswa_id', 'mahasiswa_id');
    }
    public function tagihanMahasiswa()
    {
        return $this->hasMany(TagihanMahasiswa::class, 'mahasiswa_id', 'mahasiswa_id');
    }

    // Relasi ke model Jadwal melalui tabel pivot Absensi
    public function jadwal()
    {
        return $this->belongsToMany(
            Jadwal::class,
            'absensi',         // Tabel pivot
            'mahasiswa_id',    // Foreign key di tabel pivot (mengarah ke tabel ini)
            'jadwal_id',       // Foreign key di tabel pivot (mengarah ke tabel jadwal)
            'mahasiswa_id',    // Primary key tabel mahasiswa
            'jadwal_id'        // Primary key tabel jadwal
        )->withPivot('tanggal', 'status', 'keterangan'); // Tambahkan kolom tambahan dari tabel pivot jika diperlukan
    }
    public function krs()
    {
        return $this->hasMany(Krs::class, 'mahasiswa_id', 'mahasiswa_id');
    }
    public function getProfileImageURL()
    {
        return $this->avatar ? asset('storage/' . $this->avatar) : null;
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id', 'dosen_id');
    }
    public function gelombang()
    {
        return $this->belongsTo(Gelombang::class, 'gelombang_id', 'id');
    }

    public function permintaan()
    {
        return $this->hasMany(Permintaan::class);
    }
    public function pengajuanTranskrip()
    {
        return $this->hasMany(PengajuanTranskrip::class);
    }
}
