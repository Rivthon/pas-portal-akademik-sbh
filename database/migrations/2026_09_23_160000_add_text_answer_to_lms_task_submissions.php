<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->longText('jawaban_teks')->nullable()->after('jawaban_pg');
        });
    }

    public function down(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->dropColumn('jawaban_teks');
        });
    }
};
