<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_cuti', function (Blueprint $table) {
            $table->id();
            $table->integer('mahasiswa_id');
            $table->integer('ta_id');
            $table->string('program_studi_id', 10);
            $table->integer('dospem_id');
            $table->integer('kaprodi_id')->nullable();
            $table->unsignedBigInteger('baak_id')->nullable();
            $table->text('alasan');
            $table->string('lampiran')->nullable();
            $table->string('status', 40)->default('menunggu_dospem');
            $table->string('status_mahasiswa_sebelumnya', 20)->default('aktif');
            $table->text('catatan_dospem')->nullable();
            $table->text('catatan_kaprodi')->nullable();
            $table->text('catatan_baak')->nullable();
            $table->timestamp('diajukan_pada')->nullable();
            $table->timestamp('diproses_dospem_pada')->nullable();
            $table->timestamp('diproses_kaprodi_pada')->nullable();
            $table->timestamp('diproses_baak_pada')->nullable();
            $table->timestamps();

            $table->index(['mahasiswa_id', 'ta_id']);
            $table->index(['dospem_id', 'status']);
            $table->index(['program_studi_id', 'status']);
            $table->index(['ta_id', 'status']);

            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('ta_id')->references('ta_id')->on('tahun_ajaran')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('program_studi_id')->references('jurusan_id')->on('program_studi')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('dospem_id')->references('dosen_id')->on('dosen')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreign('kaprodi_id')->references('dosen_id')->on('dosen')->cascadeOnUpdate()->nullOnDelete();
            $table->foreign('baak_id')->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_cuti');
    }
};
