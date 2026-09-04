<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class KhsPublication extends Model
{
    protected $fillable = [
        'ta_id',
        'program_studi_id',
        'scope_type',
        'scope_key',
        'semester',
        'jadwal_id',
        'published_by_user_id',
        'published_at',
    ];

    protected $casts = [
        'semester' => 'integer',
        'jadwal_id' => 'integer',
        'published_at' => 'datetime',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAkademik::class, 'ta_id');
    }

    public function programStudi()
    {
        return $this->belongsTo(ProgramStudi::class, 'program_studi_id', 'jurusan_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class);
    }

    public static function scopeKey(string $scopeType, ?int $semester = null, ?int $jadwalId = null): string
    {
        return match ($scopeType) {
            'semester' => 'semester:'.$semester,
            'course' => 'jadwal:'.$jadwalId,
            default => 'all',
        };
    }

    public static function coversJadwal(Jadwal $jadwal): bool
    {
        $semester = (int) ($jadwal->kurikulum?->mataKuliah?->smt ?? 0);

        return static::query()
            ->where('ta_id', $jadwal->ta_id)
            ->where('program_studi_id', $jadwal->jurusan_id)
            ->where(function ($query) use ($jadwal, $semester) {
                $query->where('scope_type', 'all')
                    ->orWhere(fn ($scope) => $scope->where('scope_type', 'semester')->where('semester', $semester))
                    ->orWhere(fn ($scope) => $scope->where('scope_type', 'course')->where('jadwal_id', $jadwal->id));
            })
            ->exists();
    }

    /**
     * Sisakan hanya KRS yang sudah dicakup oleh penerbitan BAAK.
     */
    public static function filterPublishedKrs(Collection $krsItems, Mahasiswa $mahasiswa): Collection
    {
        if ($krsItems->isEmpty()) {
            return $krsItems;
        }

        $publications = static::with('jadwal:id,kurikulum_id,jenis_kelas')
            ->where('program_studi_id', $mahasiswa->jurusan_id)
            ->whereIn('ta_id', $krsItems->pluck('ta_id')->filter()->unique())
            ->get()
            ->groupBy('ta_id');
        $kelasMahasiswa = strtolower((string) $mahasiswa->kelas) === 'karyawan' ? 'karyawan' : 'reguler';

        return $krsItems->filter(function (Krs $krs) use ($publications, $kelasMahasiswa) {
            $items = $publications->get($krs->ta_id, collect());
            $semester = (int) ($krs->kurikulum?->mataKuliah?->smt ?? 0);

            return $items->contains(function (KhsPublication $publication) use ($krs, $semester, $kelasMahasiswa) {
                if ($publication->scope_type === 'all') {
                    return true;
                }

                if ($publication->scope_type === 'semester') {
                    return (int) $publication->semester === $semester;
                }

                if ($publication->scope_type !== 'course' || ! $publication->jadwal) {
                    return false;
                }

                return (int) $publication->jadwal->kurikulum_id === (int) $krs->kurikulum_id
                    && strtolower((string) $publication->jadwal->jenis_kelas) === $kelasMahasiswa;
            });
        })->values();
    }
}
