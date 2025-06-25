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
        Schema::create('uap_nilai', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('program_studi_id');
            $table->unsignedBigInteger('tahun_ajaran_id');
            $table->float('uap_tulis')->nullable();
            $table->float('uap_praktik')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_input')->nullable();
            $table->timestamps();
            $table->unique(['mahasiswa_id', 'tahun_ajaran_id'], 'unique_mahasiswa_tahun');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('uap_nilai');
    }
};