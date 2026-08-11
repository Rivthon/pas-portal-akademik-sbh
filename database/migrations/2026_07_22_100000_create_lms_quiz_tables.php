<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lms_quiz', function (Blueprint $table) {
            $table->bigIncrements('quiz_id');
            $table->unsignedBigInteger('jadwal_id');
            $table->unsignedBigInteger('pertemuan_id')->nullable();
            $table->integer('dosen_id');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->dateTime('mulai_at')->nullable();
            $table->dateTime('deadline')->nullable();
            $table->unsignedInteger('durasi_menit')->nullable();
            $table->boolean('aktif')->default(true);
            $table->timestamps();

            $table->foreign('jadwal_id')->references('id')->on('jadwal')->cascadeOnDelete();
            $table->foreign('pertemuan_id')->references('pertemuan_id')->on('pertemuan')->nullOnDelete();
            $table->foreign('dosen_id')->references('dosen_id')->on('dosen')->cascadeOnDelete();
        });

        Schema::create('lms_quiz_soal', function (Blueprint $table) {
            $table->bigIncrements('soal_id');
            $table->unsignedBigInteger('quiz_id');
            $table->enum('tipe', ['pilihan_ganda', 'essay']);
            $table->text('pertanyaan');
            $table->json('opsi')->nullable();
            $table->string('kunci_jawaban')->nullable();
            $table->decimal('bobot', 8, 2)->default(1);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->foreign('quiz_id')->references('quiz_id')->on('lms_quiz')->cascadeOnDelete();
        });

        Schema::create('lms_quiz_attempts', function (Blueprint $table) {
            $table->bigIncrements('attempt_id');
            $table->unsignedBigInteger('quiz_id');
            $table->integer('mahasiswa_id');
            $table->enum('status', ['draft', 'submitted', 'graded'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('nilai_pg', 8, 2)->default(0);
            $table->decimal('nilai_essay', 8, 2)->nullable();
            $table->decimal('nilai_total', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->integer('dinilai_oleh')->nullable();
            $table->boolean('izinkan_ulang')->default(false);
            $table->timestamps();

            $table->unique(['quiz_id', 'mahasiswa_id'], 'lms_quiz_attempt_unique');
            $table->foreign('quiz_id')->references('quiz_id')->on('lms_quiz')->cascadeOnDelete();
            $table->foreign('mahasiswa_id')->references('mahasiswa_id')->on('mahasiswa')->cascadeOnDelete();
            $table->foreign('dinilai_oleh')->references('dosen_id')->on('dosen')->nullOnDelete();
        });

        Schema::create('lms_quiz_jawaban', function (Blueprint $table) {
            $table->bigIncrements('jawaban_id');
            $table->unsignedBigInteger('attempt_id');
            $table->unsignedBigInteger('soal_id');
            $table->string('pilihan_jawaban')->nullable();
            $table->longText('jawaban_text')->nullable();
            $table->string('file')->nullable();
            $table->decimal('nilai', 8, 2)->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();

            $table->unique(['attempt_id', 'soal_id'], 'lms_quiz_jawaban_unique');
            $table->foreign('attempt_id')->references('attempt_id')->on('lms_quiz_attempts')->cascadeOnDelete();
            $table->foreign('soal_id')->references('soal_id')->on('lms_quiz_soal')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lms_quiz_jawaban');
        Schema::dropIfExists('lms_quiz_attempts');
        Schema::dropIfExists('lms_quiz_soal');
        Schema::dropIfExists('lms_quiz');
    }
};
