<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasDuplicateNim = DB::table('mahasiswa')
            ->selectRaw('UPPER(TRIM(nim)) AS nim_key')
            ->groupByRaw('UPPER(TRIM(nim))')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasDuplicateNim) {
            throw new RuntimeException(
                'NIM mahasiswa masih duplikat. Jalankan database/sql/cleanup_duplicate_mahasiswa_by_nim.sql sebelum migrate.'
            );
        }

        $this->withLegacyTimestampCompatibleSqlMode(function (): void {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->unique('nim', 'mahasiswa_nim_unique');
            });
        });
    }

    public function down(): void
    {
        $this->withLegacyTimestampCompatibleSqlMode(function (): void {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropUnique('mahasiswa_nim_unique');
            });
        });
    }

    private function withLegacyTimestampCompatibleSqlMode(callable $callback): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $callback();

            return;
        }

        $originalSqlMode = DB::scalar('SELECT @@SESSION.sql_mode');

        try {
            DB::statement("SET SESSION sql_mode = 'NO_AUTO_VALUE_ON_ZERO'");
            $callback();
        } finally {
            DB::statement('SET SESSION sql_mode = ?', [$originalSqlMode]);
        }
    }
};
