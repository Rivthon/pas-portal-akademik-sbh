<?php

namespace App\Services;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Support\KrsClassResolver;
use Illuminate\Support\Collection;

class ApprovedGradeVisibilityService
{
    /**
     * Mengembalikan ID KRS yang nilai jadwal dan kelasnya sudah di-ACC Kaprodi.
     */
    public function approvedKrsIds(Mahasiswa $mahasiswa, Collection $krsRecords): Collection
    {
        $krsRecords = $krsRecords
            ->filter(fn ($item) => $item instanceof Krs)
            ->values();

        if ($krsRecords->isEmpty()) {
            return collect();
        }

        $approvedSchedules = Jadwal::query()
            ->whereIn('ta_id', $krsRecords->pluck('ta_id')->unique())
            ->whereIn('kurikulum_id', $krsRecords->pluck('kurikulum_id')->unique())
            ->whereHas('nilaiSubmission', fn ($query) => $query->where('status', 'approved'))
            ->get(['id', 'ta_id', 'kurikulum_id', 'jenis_kelas']);

        return $krsRecords
            ->filter(fn (Krs $krs) => $approvedSchedules->contains(
                fn (Jadwal $jadwal) => KrsClassResolver::matches($krs, $jadwal, $mahasiswa)
            ))
            ->pluck('krs_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
    }
}
