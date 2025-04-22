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
        Schema::create('jadwalss', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ta_id');
            $table->unsignedBigInteger('jurusan_id');
            $table->unsignedBigInteger('matakuliah_id');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_akhir');
            $table->unsignedBigInteger('ruangan_id');
            $table->enum('jenis_kelas', ['Reguler', 'Karyawan']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal');
    }
};