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
        Schema::create('kegiatan_tambahan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id');
            $table->enum('kategori', ['Organisasi', 'Kepanitiaan', 'LKMM', 'Lainnya']);
            $table->string('nama_kegiatan');
            $table->enum('jenis_partisipasi', [
                'Pengurus Harian',
                'Anggota',
                'Kepanitiaan',
                'LKMM-Pra Dasar',
                'LKMM-Dasar',
                'LKMM-Menengah',
                'LKMM-Lanjut',
                'Lainnya',
            ])->nullable();
            $table->string('tingkat')->nullable(); // Internasional / Nasional / Regional / Lokal / Prodi / Universitas
            $table->integer('bobot')->default(0); // Diisi oleh validator
            $table->string('penyelenggara')->nullable();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('file_pendukung')->nullable(); // Sertifikat / SK Pengangkatan
            $table->enum('status_validasi', ['Menunggu', 'Disetujui', 'Ditolak', 'Ditinjau'])->default('Menunggu');
            $table->text('catatan_validator')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kegiatan_tambahan');
    }
};
