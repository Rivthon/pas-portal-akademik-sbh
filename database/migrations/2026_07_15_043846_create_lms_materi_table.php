<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_materi', function (Blueprint $table) {
            $table->bigIncrements('materi_id');

            // 1. JADWAL (Tipe data: bigint unsigned)
            $table->unsignedBigInteger('jadwal_id');

            // 2. PERTEMUAN (DIPERBAIKI: Disamakan menjadi bigint unsigned)
            $table->unsignedBigInteger('pertemuan_id');

            // 3. DOSEN (Tipe data: int)
            $table->integer('dosen_id');

            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('tipe', ['pdf', 'ppt', 'doc', 'video', 'youtube']);
            $table->string('file')->nullable();
            $table->string('url')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();

            // HUBUNGAN FOREIGN KEY
            $table->foreign('jadwal_id')->references('id')->on('jadwal')->cascadeOnDelete();
            $table->foreign('pertemuan_id')->references('pertemuan_id')->on('pertemuan')->cascadeOnDelete();
            $table->foreign('dosen_id')->references('dosen_id')->on('dosen')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_materi');
    }
};
