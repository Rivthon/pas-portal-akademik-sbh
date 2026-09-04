<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $legacyScopes = DB::table('krs')
            ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
            ->join('tahun_ajaran', 'tahun_ajaran.ta_id', '=', 'krs.ta_id')
            ->where('tahun_ajaran.status_ta', '!=', 1)
            ->whereNotNull('krs.khs')
            ->whereRaw("TRIM(krs.khs) != ''")
            ->select('krs.ta_id', 'mahasiswa.jurusan_id')
            ->distinct()
            ->get();

        foreach ($legacyScopes as $scope) {
            $hasPublication = DB::table('khs_publications')
                ->where('ta_id', $scope->ta_id)
                ->where('program_studi_id', $scope->jurusan_id)
                ->exists();

            if (! $hasPublication) {
                DB::table('khs_publications')->insert([
                    'ta_id' => $scope->ta_id,
                    'program_studi_id' => $scope->jurusan_id,
                    'scope_type' => 'all',
                    'scope_key' => 'legacy-backfill',
                    'semester' => null,
                    'jadwal_id' => null,
                    'published_by_user_id' => null,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('khs_publications')->where('scope_key', 'legacy-backfill')->delete();
    }
};
