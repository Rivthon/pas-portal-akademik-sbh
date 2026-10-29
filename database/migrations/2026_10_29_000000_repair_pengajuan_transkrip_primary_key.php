<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pengajuan_transkrip') || ! Schema::hasColumn('pengajuan_transkrip', 'id')) {
            return;
        }

        $database = DB::getDatabaseName();
        $primaryKeyExists = DB::table('information_schema.table_constraints')
            ->where('constraint_schema', $database)
            ->where('table_name', 'pengajuan_transkrip')
            ->where('constraint_type', 'PRIMARY KEY')
            ->exists();

        if (! $primaryKeyExists) {
            DB::statement('ALTER TABLE `pengajuan_transkrip` ADD PRIMARY KEY (`id`)');
        }

        $idColumn = DB::table('information_schema.columns')
            ->select('extra')
            ->where('table_schema', $database)
            ->where('table_name', 'pengajuan_transkrip')
            ->where('column_name', 'id')
            ->first();

        if (! str_contains(strtolower((string) ($idColumn->extra ?? '')), 'auto_increment')) {
            DB::statement(
                'ALTER TABLE `pengajuan_transkrip` MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
            );
        }
    }

    public function down(): void
    {
        // Primary key dan AUTO_INCREMENT dipertahankan untuk menjaga integritas data lama.
    }
};
