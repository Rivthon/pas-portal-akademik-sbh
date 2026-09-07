<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const APPROVED_AT = '2026-09-07 12:30:00';

    public function up(): void
    {
        $taId = DB::table('tahun_ajaran')
            ->whereIn('nama', ['2025/2026', '2025-2026'])
            ->whereRaw("LOWER(TRIM(semester)) = 'genap'")
            ->value('ta_id');

        if (! $taId) {
            return;
        }

        $mahasiswaDenganDospem = DB::table('mahasiswa')
            ->join('krs', 'krs.mahasiswa_id', '=', 'mahasiswa.mahasiswa_id')
            ->where('krs.ta_id', $taId)
            ->whereNotNull('mahasiswa.dosen_id')
            ->select('mahasiswa.mahasiswa_id', 'mahasiswa.dosen_id')
            ->distinct()
            ->get();

        foreach ($mahasiswaDenganDospem as $mahasiswa) {
            DB::table('krs')
                ->where('ta_id', $taId)
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->whereNull('disetujui_pada')
                ->update([
                    'disetujui_oleh' => $mahasiswa->dosen_id,
                    'disetujui_pada' => self::APPROVED_AT,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        $taId = DB::table('tahun_ajaran')
            ->whereIn('nama', ['2025/2026', '2025-2026'])
            ->whereRaw("LOWER(TRIM(semester)) = 'genap'")
            ->value('ta_id');

        if (! $taId) {
            return;
        }

        DB::table('krs')
            ->where('ta_id', $taId)
            ->where('disetujui_pada', self::APPROVED_AT)
            ->update([
                'disetujui_oleh' => null,
                'disetujui_pada' => null,
                'updated_at' => now(),
            ]);
    }
};
