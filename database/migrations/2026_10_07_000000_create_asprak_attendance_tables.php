<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asprak_penugasan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jadwal_praktik_id');
            $table->integer('mahasiswa_id');
            $table->integer('ditugaskan_oleh_dosen_id');
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->unique(['jadwal_praktik_id', 'mahasiswa_id'], 'asprak_penugasan_jadwal_mahasiswa_unique');
            $table->index(['mahasiswa_id', 'aktif']);
            $table->foreign('jadwal_praktik_id')->references('id')->on('jadwal_praktik')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->restrictOnDelete();
            $table->foreign('ditugaskan_oleh_dosen_id')->references('dosen_id')->on('dosen')->restrictOnDelete();
        });

        Schema::create('asprak_absensi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pertemuan_praktik_id');
            $table->unsignedBigInteger('asprak_penugasan_id');
            $table->string('status', 30)->default('belum diabsen');
            $table->string('keterangan', 500)->nullable();
            $table->integer('diabsen_oleh_dosen_id')->nullable();
            $table->timestamps();

            $table->unique(['pertemuan_praktik_id', 'asprak_penugasan_id'], 'asprak_absensi_pertemuan_penugasan_unique');
            $table->index(['asprak_penugasan_id', 'status']);
            $table->foreign('pertemuan_praktik_id')->references('pertemuan_praktik_id')->on('pertemuan_praktik')->cascadeOnDelete();
            $table->foreign('asprak_penugasan_id')->references('id')->on('asprak_penugasan')->cascadeOnDelete();
            $table->foreign('diabsen_oleh_dosen_id')->references('dosen_id')->on('dosen')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asprak_absensi');
        Schema::dropIfExists('asprak_penugasan');
    }
};
