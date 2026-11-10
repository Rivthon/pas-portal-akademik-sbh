<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['jadwal_uts', 'jadwal_uas'] as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
                continue;
            }

            $primaryColumns = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'"))
                ->pluck('Column_name')
                ->all();

            if ($primaryColumns !== [] && $primaryColumns !== ['id']) {
                throw new RuntimeException("Primary key tabel {$table} bukan kolom id.");
            }

            if ($primaryColumns === []) {
                $hasDuplicateId = DB::table($table)
                    ->select('id')
                    ->groupBy('id')
                    ->havingRaw('COUNT(*) > 1')
                    ->exists();

                if ($hasDuplicateId || DB::table($table)->whereNull('id')->exists()) {
                    throw new RuntimeException("ID tabel {$table} masih kosong atau duplikat.");
                }

                DB::statement("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
            }

            $idColumn = collect(DB::select("SHOW COLUMNS FROM `{$table}` LIKE 'id'"))->first();
            $columnType = $idColumn?->Type;

            if (! is_string($columnType)
                || ! preg_match('/^(tinyint|smallint|mediumint|int|bigint)(\(\d+\))?( unsigned)?$/i', $columnType)) {
                throw new RuntimeException("Tipe kolom {$table}.id tidak didukung: ".($columnType ?? 'tidak ditemukan'));
            }

            if (! str_contains(strtolower((string) ($idColumn->Extra ?? '')), 'auto_increment')) {
                DB::statement("ALTER TABLE `{$table}` MODIFY `id` {$columnType} NOT NULL AUTO_INCREMENT");
            }
        }
    }

    public function down(): void
    {
        // Perbaikan integritas primary key tidak dibatalkan agar insert jadwal tetap aman.
    }
};
