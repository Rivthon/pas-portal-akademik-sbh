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
        Schema::create('p2mw', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('nama_usaha');
            $table->string('bidang_usaha')->nullable();
            $table->string('penyelenggara')->nullable();
            $table->string('tingkat')->nullable(); // Lokal / Nasional / Internasional
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_proposal');
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
        Schema::dropIfExists('p2mw');
    }
};
