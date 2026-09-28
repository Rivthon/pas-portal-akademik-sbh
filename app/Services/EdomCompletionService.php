<?php

namespace App\Services;

use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Support\KrsClassResolver;
use Illuminate\Support\Facades\DB;

class EdomCompletionService
{
    public function status(Mahasiswa $mahasiswa, int $taId): array
    {
        $krsRecords = Krs::query()
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $taId)
            ->whereNotNull('disetujui_pada')
            ->get(['krs_id', 'kurikulum_id', 'jenis_kelas']);

        if ($krsRecords->isEmpty()) {
            return ['required' => 0, 'filled' => 0, 'remaining' => 0, 'complete' => false];
        }

        $requiredPairs = $krsRecords->flatMap(function (Krs $krs) use ($mahasiswa) {
            $kelas = KrsClassResolver::forKrs($krs, $mahasiswa);

            return DB::table('dosen_mata_kuliah')
                ->where('kurikulum_id', $krs->kurikulum_id)
                ->where(function ($query) use ($kelas) {
                    $query->whereRaw('LOWER(COALESCE(jenis_kelas, "")) = ?', [$kelas]);
                    if ($kelas === 'reguler') {
                        $query->orWhere(function ($praktik) {
                            $praktik->whereRaw('LOWER(COALESCE(jenis_dosen, "")) = ?', ['praktik'])
                                ->where(function ($query) {
                                    $query->whereNull('jenis_kelas')->orWhere('jenis_kelas', '');
                                });
                        });
                    }
                })
                ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
                ->distinct()
                ->get();
        })->map(fn ($row) => $this->key(
            $row->dosen_id,
            $row->kurikulum_id,
            $row->jenis_dosen,
            $row->jenis_kelas
        ))->unique()->values();

        $filledPairs = DB::table('penilaian')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->whereIn('krs_id', $krsRecords->pluck('krs_id'))
            ->select('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->distinct()
            ->get()
            ->map(fn ($row) => $this->key(
                $row->dosen_id,
                $row->kurikulum_id,
                $row->jenis_dosen,
                $row->jenis_kelas
            ))->unique()->values();

        $remaining = $requiredPairs->diff($filledPairs)->count();
        $required = $requiredPairs->count();

        return [
            'required' => $required,
            'filled' => $required - $remaining,
            'remaining' => $remaining,
            'complete' => $required > 0 && $remaining === 0,
        ];
    }

    public function isComplete(Mahasiswa $mahasiswa, int $taId): bool
    {
        return $this->status($mahasiswa, $taId)['complete'];
    }

    private function key($dosenId, $kurikulumId, ?string $jenisDosen, ?string $jenisKelas): string
    {
        return implode('|', [
            (int) $dosenId,
            (int) $kurikulumId,
            strtolower(trim((string) $jenisDosen)),
            KrsClassResolver::normalize($jenisKelas),
        ]);
    }
}
