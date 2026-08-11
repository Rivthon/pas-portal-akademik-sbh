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
        Schema::create('jadwal_uap', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ta_id'); // ID Tahun Akademik
            $table->unsignedBigInteger('jurusan_id'); // ID Jurusan
            $table->unsignedBigInteger('matakuliah_id'); // ID Mata Kuliah
            $table->string('hari'); // Hari pelaksanaan
            $table->time('jam'); // Jam pelaksanaan
            $table->unsignedBigInteger('ruangan_id'); // ID Ruangan
            $table->timestamps(); // created_at & updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_uap');
    }
};
