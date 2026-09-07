<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const SCOPE_KEY = 'historical-2025-2026-even';

    public function up(): void
    {
        $taId = DB::table('tahun_ajaran')
            ->whereIn('nama', ['2025/2026', '2025-2026'])
            ->whereRaw("LOWER(TRIM(semester)) = 'genap'")
            ->value('ta_id');

        if (! $taId) {
            return;
        }

        $programStudiIds = DB::table('krs')
            ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
            ->where('krs.ta_id', $taId)
            ->whereNotNull('mahasiswa.jurusan_id')
            ->distinct()
            ->pluck('mahasiswa.jurusan_id');

        foreach ($programStudiIds as $programStudiId) {
            DB::table('khs_publications')->updateOrInsert(
                [
                    'ta_id' => $taId,
                    'program_studi_id' => $programStudiId,
                    'scope_key' => self::SCOPE_KEY,
                ],
                [
                    'scope_type' => 'all',
                    'semester' => null,
                    'jadwal_id' => null,
                    'published_by_user_id' => null,
                    'published_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        DB::table('khs_publications')
            ->where('scope_key', self::SCOPE_KEY)
            ->delete();
    }
};
