<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql'
            && Schema::hasColumns('mahasiswa', ['created_at', 'updated_at'])) {
            // Tabel legacy memakai zero-date pada updated_at. MySQL strict menolak
            // ALTER TABLE apa pun sebelum kedua timestamp dinormalisasi.
            DB::statement(
                'ALTER TABLE `mahasiswa` '.
                'MODIFY `created_at` TIMESTAMP NULL DEFAULT NULL, '.
                'MODIFY `updated_at` TIMESTAMP NULL DEFAULT NULL'
            );
        }

        if (! Schema::hasColumn('mahasiswa', 'remember_token')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->rememberToken();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('mahasiswa', 'remember_token')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropColumn('remember_token');
            });
        }
    }
};
