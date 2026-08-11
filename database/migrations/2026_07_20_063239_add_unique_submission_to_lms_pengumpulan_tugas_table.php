<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->unique(
                ['tugas_id', 'mahasiswa_id'],
                'unique_tugas_mahasiswa'
            );
        });
    }

    public function down(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->dropUnique('unique_tugas_mahasiswa');
        });
    }
};
