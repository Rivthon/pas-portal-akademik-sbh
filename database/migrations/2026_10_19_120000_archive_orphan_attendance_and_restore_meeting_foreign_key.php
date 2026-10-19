<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ARCHIVE_TABLE = 'absensi_yatim_arsip';

    private const FOREIGN_KEY = 'absensi_pertemuan_cascade_20261019_foreign';

    public function up(): void
    {
        if (! Schema::hasTable('absensi') || ! Schema::hasTable('pertemuan')) {
            return;
        }

        $this->restoreMeetingPrimaryKey();

        if (! Schema::hasTable(self::ARCHIVE_TABLE)) {
            Schema::create(self::ARCHIVE_TABLE, function (Blueprint $table) {
                $table->unsignedBigInteger('original_absensi_id')->primary();
                $table->unsignedBigInteger('jadwal_id')->nullable();
                $table->unsignedBigInteger('mahasiswa_id')->nullable();
                $table->unsignedBigInteger('pertemuan_id')->nullable();
                $table->string('status', 30);
                $table->text('keterangan')->nullable();
                $table->date('tanggal')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('diarsipkan_at')->useCurrent();
                $table->string('alasan')->default('Pertemuan induk sudah tidak tersedia');
            });
        }

        // Arsipkan dahulu agar pembersihan data lama tetap dapat diaudit dan dipulihkan.
        DB::statement('INSERT IGNORE INTO `'.self::ARCHIVE_TABLE.'`
            (`original_absensi_id`, `jadwal_id`, `mahasiswa_id`, `pertemuan_id`, `status`, `keterangan`, `tanggal`, `created_at`, `updated_at`)
            SELECT a.`absensi_id`, a.`jadwal_id`, a.`mahasiswa_id`, a.`pertemuan_id`, a.`status`, a.`keterangan`, a.`tanggal`, a.`created_at`, a.`updated_at`
            FROM `absensi` a
            LEFT JOIN `pertemuan` p ON p.`pertemuan_id` = a.`pertemuan_id`
            WHERE a.`pertemuan_id` IS NOT NULL AND p.`pertemuan_id` IS NULL');

        DB::table('absensi')
            ->whereNotNull('pertemuan_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('pertemuan')
                    ->whereColumn('pertemuan.pertemuan_id', 'absensi.pertemuan_id');
            })
            ->whereIn('absensi_id', DB::table(self::ARCHIVE_TABLE)->select('original_absensi_id'))
            ->delete();

        if (! $this->hasMeetingForeignKey()) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->foreign('pertemuan_id', self::FOREIGN_KEY)
                    ->references('pertemuan_id')
                    ->on('pertemuan')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('absensi')) {
            return;
        }

        if ($this->hasForeignKeyNamed(self::FOREIGN_KEY)) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropForeign(self::FOREIGN_KEY);
            });
        }

        if (Schema::hasTable(self::ARCHIVE_TABLE) && ! $this->hasMeetingForeignKey()) {
            DB::statement('INSERT IGNORE INTO `absensi`
                (`absensi_id`, `jadwal_id`, `mahasiswa_id`, `pertemuan_id`, `status`, `keterangan`, `tanggal`, `created_at`, `updated_at`)
                SELECT `original_absensi_id`, `jadwal_id`, `mahasiswa_id`, `pertemuan_id`, `status`, `keterangan`, `tanggal`, `created_at`, `updated_at`
                FROM `'.self::ARCHIVE_TABLE.'`');

            Schema::drop(self::ARCHIVE_TABLE);
        }
    }

    private function hasMeetingForeignKey(): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'absensi')
            ->where('COLUMN_NAME', 'pertemuan_id')
            ->where('REFERENCED_TABLE_NAME', 'pertemuan')
            ->where('REFERENCED_COLUMN_NAME', 'pertemuan_id')
            ->exists();
    }

    private function hasForeignKeyNamed(string $constraint): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'absensi')
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function restoreMeetingPrimaryKey(): void
    {
        $hasPrimaryKey = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'pertemuan')
            ->where('CONSTRAINT_TYPE', 'PRIMARY KEY')
            ->exists();

        if (! $hasPrimaryKey) {
            $hasDuplicate = DB::table('pertemuan')
                ->select('pertemuan_id')
                ->groupBy('pertemuan_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($hasDuplicate) {
                throw new RuntimeException(
                    'Primary key pertemuan tidak dapat dipulihkan karena terdapat pertemuan_id duplikat.'
                );
            }

            DB::statement('ALTER TABLE `pertemuan` ADD PRIMARY KEY (`pertemuan_id`)');
        }

        $isAutoIncrement = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'pertemuan')
            ->where('COLUMN_NAME', 'pertemuan_id')
            ->where('EXTRA', 'like', '%auto_increment%')
            ->exists();

        if (! $isAutoIncrement) {
            DB::statement(
                'ALTER TABLE `pertemuan` MODIFY `pertemuan_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT'
            );
        }
    }
};
