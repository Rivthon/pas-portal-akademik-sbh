<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_tugas', function (Blueprint $table) {
            $table->boolean('izinkan_terlambat')
                ->default(false)
                ->after('aktif');

            $table->boolean('izinkan_upload_ulang')
                ->default(true)
                ->after('izinkan_terlambat');
        });
    }

    public function down(): void
    {
        Schema::table('lms_tugas', function (Blueprint $table) {
            $table->dropColumn([
                'izinkan_terlambat',
                'izinkan_upload_ulang',
            ]);
        });
    }
};
