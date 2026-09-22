<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('absensi')) {
            DB::statement("ALTER TABLE absensi MODIFY status ENUM('belum diabsen','hadir','izin','sakit','tidak hadir') NOT NULL DEFAULT 'belum diabsen'");
        }

        if (Schema::hasTable('absensi_praktik')) {
            DB::statement("ALTER TABLE absensi_praktik MODIFY status ENUM('belum diabsen','hadir','izin','sakit','tidak hadir') NOT NULL DEFAULT 'belum diabsen'");
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('absensi')) {
            DB::table('absensi')->where('status', 'belum diabsen')->update(['status' => 'tidak hadir']);
            DB::statement("ALTER TABLE absensi MODIFY status ENUM('hadir','izin','sakit','tidak hadir') NOT NULL DEFAULT 'tidak hadir'");
        }

        if (Schema::hasTable('absensi_praktik')) {
            DB::table('absensi_praktik')->where('status', 'belum diabsen')->update(['status' => 'tidak hadir']);
            DB::statement("ALTER TABLE absensi_praktik MODIFY status ENUM('hadir','izin','sakit','tidak hadir') NOT NULL DEFAULT 'tidak hadir'");
        }
    }
};
