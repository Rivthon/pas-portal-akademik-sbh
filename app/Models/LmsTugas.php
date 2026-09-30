<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LmsTugas extends Model
{
    protected $table = 'lms_tugas';

    protected $primaryKey = 'tugas_id';

    protected $fillable = [
        'jadwal_id',
        'pertemuan_id',
        'dosen_id',
        'judul',
        'deskripsi',
        'tipe',
        'cakupan',
        'deadline',
        'nilai_maksimal',
        'lampiran',
        'aktif',
        'izinkan_terlambat',
        'izinkan_upload_ulang',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'aktif' => 'boolean',
        'izinkan_terlambat' => 'boolean',
        'izinkan_upload_ulang' => 'boolean',
    ];

    public function pengumpulan()
    {
        return $this->hasMany(
            LmsPengumpulanTugas::class,
            'tugas_id',
            'tugas_id'
        );
    }

    public function soal()
    {
        return $this->hasMany(LmsTugasSoal::class, 'tugas_id', 'tugas_id')
            ->orderBy('urutan')
            ->orderBy('soal_id');
    }

    public function pertemuan()
    {
        return $this->belongsTo(
            Pertemuan::class,
            'pertemuan_id',
            'pertemuan_id'
        );
    }

    public function jadwal()
    {
        return $this->belongsTo(
            Jadwal::class,
            'jadwal_id',
            'id'
        );
    }

    public function targetMahasiswa()
    {
        return $this->belongsToMany(
            Mahasiswa::class,
            'lms_tugas_mahasiswa',
            'tugas_id',
            'mahasiswa_id',
            'tugas_id',
            'mahasiswa_id'
        )->withTimestamps();
    }

    public function scopeVisibleForMahasiswa(Builder $query, int $mahasiswaId): Builder
    {
        return $query->where(function (Builder $scope) use ($mahasiswaId) {
            $scope->where('cakupan', 'semua')
                ->orWhere(function (Builder $individual) use ($mahasiswaId) {
                    $individual->where('cakupan', 'individu')
                        ->whereHas('targetMahasiswa', fn (Builder $target) => $target
                            ->where('mahasiswa.mahasiswa_id', $mahasiswaId));
                });
        });
    }

    public function ditujukanKepada(int $mahasiswaId): bool
    {
        if ($this->cakupan !== 'individu') {
            return true;
        }

        if ($this->relationLoaded('targetMahasiswa')) {
            return $this->targetMahasiswa->contains(
                fn (Mahasiswa $mahasiswa) => (int) $mahasiswa->mahasiswa_id === $mahasiswaId
            );
        }

        return $this->targetMahasiswa()->where('mahasiswa.mahasiswa_id', $mahasiswaId)->exists();
    }
}
