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
        Schema::create('sertifikasi_profesi_kompetensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('nama_kegiatan');
            $table->string('penyelenggara');
            $table->enum('tingkat_kegiatan', ['Lokal', 'Regional', 'Nasional', 'Internasional']);
            $table->string('prestasi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users');
            $table->string('file_sertifikat');
            $table->string('file_sk');
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
        Schema::dropIfExists('sertifikasi_profesi_kompetensi');
    }
};
