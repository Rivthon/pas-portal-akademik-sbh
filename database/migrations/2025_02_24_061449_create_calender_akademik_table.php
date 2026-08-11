<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('calender_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jurusan_id');
            $table->string('nama_file');
            $table->string('path');
            // $table->string('tahun_ajaran');
            $table->boolean('status')->default(0); // 0: Tidak aktif, 1: Aktif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calender_akademik');
    }
};
