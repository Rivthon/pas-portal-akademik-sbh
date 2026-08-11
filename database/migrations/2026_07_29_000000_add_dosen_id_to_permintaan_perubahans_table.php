<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaan_perubahans', function (Blueprint $table) {
            if (! Schema::hasColumn('permintaan_perubahans', 'dosen_id')) {
                $table->unsignedInteger('dosen_id')->nullable()->after('mahasiswa_id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaan_perubahans', function (Blueprint $table) {
            if (Schema::hasColumn('permintaan_perubahans', 'dosen_id')) {
                $table->dropIndex(['dosen_id']);
                $table->dropColumn('dosen_id');
            }
        });
    }
};
