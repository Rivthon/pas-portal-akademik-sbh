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
        Schema::create('ppsm', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->string('nama_kegiatan');
            $table->string('penyelenggara');
            $table->enum('tingkat_kegiatan', ['Lokal', 'Regional', 'Nasional', 'Internasional'])->nullable();
            $table->string('prestasi')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('file_sertifikat'); // path file bukti kegiatan (sertifikat)
            $table->string('file_sk');         // path file bukti SK pengangkatan
            $table->enum('status_validasi', ['Menunggu', 'Disetujui', 'Ditolak', 'Ditinjau'])->default('Menunggu');
            $table->text('catatan_validator')->nullable();
            $table->integer('bobot')->default(0); // bobot nilai kegiatan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppsm');
    }
};
