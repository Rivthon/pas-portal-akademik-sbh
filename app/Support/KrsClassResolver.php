<?php

namespace App\Support;

use App\Models\Jadwal;
use App\Models\Krs;
use Illuminate\Support\Collection;

final class KrsClassResolver
{
    public static function normalize(?string $kelas): string
    {
        return in_array(strtolower(trim((string) $kelas)), ['karyawan', 'reguler b'], true)
            ? 'karyawan'
            : 'reguler';
    }

    public static function forKrs(Krs $krs, $mahasiswa = null): string
    {
        if ($krs->jenis_kelas) {
            return self::normalize($krs->jenis_kelas);
        }

        return self::normalize($mahasiswa?->kelas ?? $krs->mahasiswa?->kelas);
    }

    public static function matches(Krs $krs, Jadwal $jadwal, $mahasiswa = null): bool
    {
        return (int) $krs->kurikulum_id === (int) $jadwal->kurikulum_id
            && (int) $krs->ta_id === (int) $jadwal->ta_id
            && self::forKrs($krs, $mahasiswa) === self::normalize($jadwal->jenis_kelas);
    }

    public static function jadwalIdsForMahasiswa($mahasiswa, int $taId): Collection
    {
        $krsByCourse = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId)
            ->get()
            ->keyBy('kurikulum_id');

        if ($krsByCourse->isEmpty()) {
            return collect();
        }

        return Jadwal::query()
            ->where('ta_id', $taId)
            ->whereIn('kurikulum_id', $krsByCourse->keys())
            ->get(['id', 'ta_id', 'kurikulum_id', 'jenis_kelas'])
            ->filter(function (Jadwal $jadwal) use ($krsByCourse, $mahasiswa) {
                $krs = $krsByCourse->get($jadwal->kurikulum_id);

                return $krs && self::matches($krs, $jadwal, $mahasiswa);
            })
            ->pluck('id')
            ->values();
    }
}
