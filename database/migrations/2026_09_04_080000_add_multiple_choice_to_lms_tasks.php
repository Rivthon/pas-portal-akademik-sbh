<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_tugas', function (Blueprint $table) {
            $table->string('tipe', 30)->default('file')->after('deskripsi');
        });

        Schema::create('lms_tugas_soal', function (Blueprint $table) {
            $table->bigIncrements('soal_id');
            $table->unsignedBigInteger('tugas_id');
            $table->text('pertanyaan');
            $table->json('opsi');
            $table->unsignedTinyInteger('kunci_jawaban');
            $table->decimal('bobot', 8, 2)->default(1);
            $table->unsignedInteger('urutan')->default(1);
            $table->timestamps();

            $table->foreign('tugas_id')->references('tugas_id')->on('lms_tugas')->cascadeOnDelete();
            $table->index(['tugas_id', 'urutan'], 'lms_tugas_soal_order_index');
        });

        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->string('file')->nullable()->change();
            $table->json('jawaban_pg')->nullable()->after('catatan');
            $table->boolean('dinilai_otomatis')->default(false)->after('nilai');
        });
    }

    public function down(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->dropColumn(['jawaban_pg', 'dinilai_otomatis']);
        });
        Schema::dropIfExists('lms_tugas_soal');
        Schema::table('lms_tugas', function (Blueprint $table) {
            $table->dropColumn('tipe');
        });
    }
};
