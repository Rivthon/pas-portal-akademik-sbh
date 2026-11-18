<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'nilai_submissions',
            'kaprodi_absensi_verifications',
            'khs_publications',
        ];

        foreach ($tables as $table) {
            $this->repairPrimaryKey($table);
        }

        $this->ensureIndex('nilai_submissions', ['jadwal_id'], true, 'nilai_submissions_jadwal_id_unique');
        $this->ensureIndex('nilai_submissions', ['program_studi_id'], false, 'nilai_submissions_program_studi_id_index');
        $this->ensureIndex('nilai_submissions', ['status'], false, 'nilai_submissions_status_index');

        $this->ensureIndex('kaprodi_absensi_verifications', ['jadwal_id'], true, 'kaprodi_absensi_verifications_jadwal_id_unique');
        $this->ensureIndex('kaprodi_absensi_verifications', ['program_studi_id'], false, 'kaprodi_absensi_verifications_program_studi_id_index');

        $this->ensureIndex('khs_publications', ['ta_id', 'program_studi_id', 'scope_key'], true, 'khs_publications_scope_unique');
        $this->ensureIndex('khs_publications', ['jadwal_id'], false, 'khs_publications_jadwal_index');
    }

    public function down(): void
    {
        // Perbaikan integritas primary key dan indeks tidak dibatalkan.
    }

    private function repairPrimaryKey(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'id')) {
            return;
        }

        $primaryColumns = collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'"))
            ->sortBy('Seq_in_index')
            ->pluck('Column_name')
            ->values()
            ->all();

        if ($primaryColumns !== [] && $primaryColumns !== ['id']) {
            throw new RuntimeException("Primary key tabel {$table} bukan kolom id.");
        }

        if ($primaryColumns === []) {
            $invalidIds = DB::table($table)->whereNull('id')->exists()
                || DB::table($table)->select('id')->groupBy('id')->havingRaw('COUNT(*) > 1')->exists();

            if ($invalidIds) {
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

    private function ensureIndex(string $table, array $columns, bool $unique, string $name): void
    {
        if (! Schema::hasTable($table)
            || collect($columns)->contains(fn (string $column) => ! Schema::hasColumn($table, $column))) {
            return;
        }

        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->groupBy('Key_name')
            ->map(fn ($rows) => [
                'columns' => $rows->sortBy('Seq_in_index')->pluck('Column_name')->values()->all(),
                'unique' => (int) $rows->first()->Non_unique === 0,
            ]);

        $exists = $indexes->contains(fn (array $index) => $index['columns'] === $columns
            && (! $unique || $index['unique']));

        if ($exists) {
            return;
        }

        if ($unique) {
            $duplicates = DB::table($table)
                ->select($columns)
                ->groupBy($columns)
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($duplicates) {
                throw new RuntimeException("Data tabel {$table} masih duplikat untuk indeks {$name}.");
            }
        }

        $columnSql = collect($columns)->map(fn (string $column) => "`{$column}`")->implode(', ');
        $indexType = $unique ? 'UNIQUE INDEX' : 'INDEX';

        DB::statement("ALTER TABLE `{$table}` ADD {$indexType} `{$name}` ({$columnSql})");
    }
};
