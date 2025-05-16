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
        Schema::create('pkm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('judul_kegiatan');
            $table->enum('jenis_pkm', ['PKM-R', 'PKM-K', 'PKM-M', 'PKM-T', 'PKM-GT', 'PKM-AI', 'Lainnya'])->nullable();
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
        Schema::dropIfExists('pkm_programs');
    }
};
