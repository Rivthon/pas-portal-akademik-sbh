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
        Schema::create('penguasaan_bahasa', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('nama_bahasa');
            $table->string('level')->nullable();
            $table->string('penyelenggara')->nullable();
            $table->date('tanggal_tes');
            $table->integer('skor')->nullable();
            $table->string('file_sertifikat')->nullable();
            $table->enum('status_validasi', ['Menunggu', 'Disetujui', 'Ditolak', 'Ditinjau'])->default('Menunggu');
            $table->text('catatan_validator')->nullable();
            $table->integer('bobot')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penguasaan_bahasa');
    }
};
