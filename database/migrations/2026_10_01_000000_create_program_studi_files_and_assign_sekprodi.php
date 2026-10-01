<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            $table->integer('sekprodi_dosen_id')
                ->nullable()
                ->after('kaprodi_dosen_id');

            $table->foreign('sekprodi_dosen_id', 'program_studi_sekprodi_dosen_fk')
                ->references('dosen_id')
                ->on('dosen')
                ->nullOnDelete();
        });

        Schema::create('berkas_program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('jurusan_id');
            $table->integer('uploaded_by_dosen_id');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->enum('target', ['mahasiswa', 'dosen', 'semua'])->default('semua');
            $table->string('nama_file');
            $table->string('path');
            $table->string('mime_type', 150)->nullable();
            $table->unsignedBigInteger('ukuran')->default(0);
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('jurusan_id', 'berkas_prodi_program_studi_fk')
                ->references('jurusan_id')
                ->on('program_studi')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('uploaded_by_dosen_id', 'berkas_prodi_uploader_fk')
                ->references('dosen_id')
                ->on('dosen')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->index(['jurusan_id', 'aktif', 'target'], 'berkas_prodi_scope_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_program_studi');

        Schema::table('program_studi', function (Blueprint $table) {
            $table->dropForeign('program_studi_sekprodi_dosen_fk');
            $table->dropColumn('sekprodi_dosen_id');
        });
    }
};
