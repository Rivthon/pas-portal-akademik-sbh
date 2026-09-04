<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'jadwal'; // Pastikan nama tabel sesuai

    protected $primaryKey = 'id';   // Pastikan primary key sesuai

    public $timestamps = true;      //

    protected $fillable = [
        'ta_id',
        'jurusan_id',
        'kurikulum_id',
        'jam_mulai',
        'jam_selesai',
        'hari',
        'ruangan_id',
        'jenis_kelas',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class, 'kurikulum_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'jurusan_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id', 'ruangan_id'); // 'ruangan_id' adalah foreign key
    }

    public function pertemuan()
    {
        return $this->hasMany(Pertemuan::class, 'jadwal_id');
    }

    public function dosen()
    {
        return $this->belongsTo(Dosen::class, 'dosen_id');
    }

    public function mataKuliah()
    {
        return $this->hasOneThrough(
            Matakuliah::class,
            Kurikulum::class,
            'kurikulum_id',   // foreign key di tabel kurikulum
            'matakuliah_id',  // foreign key di tabel matakuliah
            'kurikulum_id',   // local key di tabel jadwal
            'matakuliah_id'   // local key di tabel kurikulum
        );
    }

    public function materi()
    {
        return $this->hasMany(
            LmsMateri::class,
            'jadwal_id',
            'id'
        );
    }

    public function tugas()
    {
        return $this->hasMany(LmsTugas::class, 'jadwal_id', 'id');
    }

    public function quiz()
    {
        return $this->hasMany(LmsQuiz::class, 'jadwal_id', 'id');
    }

    public function nilaiSubmission()
    {
        return $this->hasOne(NilaiSubmission::class, 'jadwal_id');
    }

    public function scopeAccessibleInLmsByDosen($query, $dosenId)
    {
        return $query->where(function ($access) use ($dosenId) {
            $access->whereHas('kurikulum.dosenToMatakuliah', function ($assignment) use ($dosenId) {
                $assignment->where('dosen_id', $dosenId)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)');
            })->orWhereHas('pertemuan', fn ($pertemuan) => $pertemuan->where('dosen_id', $dosenId));
        });
    }
}
