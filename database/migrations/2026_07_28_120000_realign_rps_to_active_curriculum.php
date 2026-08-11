<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $activeTaIds = DB::table('tahun_ajaran')
            ->where('status_ta', 1)
            ->pluck('ta_id');

        if ($activeTaIds->isEmpty()) {
            return;
        }

        $activeCurricula = DB::table('kurikulum')
            ->whereIn('ta_id', $activeTaIds)
            ->get()
            ->groupBy(fn ($item) => $item->matakuliah_id.'|'.$item->jurusan_id);

        $misalignedRps = DB::table('rps as r')
            ->join('kurikulum as k', 'k.kurikulum_id', '=', 'r.kurikulum_id')
            ->whereNotIn('k.ta_id', $activeTaIds)
            ->orderByDesc('r.updated_at')
            ->select([
                'r.rps_id',
                'r.dosen_id',
                'r.jenis_kelas',
                'k.matakuliah_id',
                'k.jurusan_id',
            ])
            ->get();

        foreach ($misalignedRps as $rps) {
            $key = $rps->matakuliah_id.'|'.$rps->jurusan_id;

            foreach ($activeCurricula->get($key, collect()) as $activeCurriculum) {
                $isAssigned = DB::table('dosen_mata_kuliah')
                    ->where('kurikulum_id', $activeCurriculum->kurikulum_id)
                    ->where('dosen_id', $rps->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $rps->jenis_kelas)])
                    ->exists();

                $alreadyHasRps = DB::table('rps')
                    ->where('kurikulum_id', $activeCurriculum->kurikulum_id)
                    ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower((string) $rps->jenis_kelas)])
                    ->exists();

                if ($isAssigned && ! $alreadyHasRps) {
                    DB::table('rps')
                        ->where('rps_id', $rps->rps_id)
                        ->update(['kurikulum_id' => $activeCurriculum->kurikulum_id]);

                    break;
                }
            }
        }
    }

    public function down(): void
    {
        // Koreksi data tidak dibalik agar referensi file tidak kembali ke semester yang salah.
    }
};
