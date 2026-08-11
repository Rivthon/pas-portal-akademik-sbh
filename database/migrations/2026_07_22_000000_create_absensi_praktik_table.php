<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_praktik', function (Blueprint $table) {
            $table->id('absensi_praktik_id');
            $table->unsignedBigInteger('jadwal_praktik_id');
            $table->unsignedBigInteger('pertemuan_praktik_id');
            $table->integer('mahasiswa_id');
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'tidak hadir'])->default('tidak hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['pertemuan_praktik_id', 'mahasiswa_id'], 'absen_praktik_pertemuan_mahasiswa_unique');
            $table->index(['jadwal_praktik_id', 'mahasiswa_id'], 'absen_praktik_jadwal_mahasiswa_index');
            $table->foreign('jadwal_praktik_id')->references('id')->on('jadwal_praktik')->cascadeOnDelete();
            $table->foreign('pertemuan_praktik_id')->references('pertemuan_praktik_id')->on('pertemuan_praktik')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_praktik');
    }
};
