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
        Schema::create('lms_tugas', function (Blueprint $table) {

            $table->bigIncrements('tugas_id');

            $table->unsignedBigInteger('jadwal_id');
            $table->unsignedBigInteger('pertemuan_id');
            $table->integer('dosen_id');

            $table->string('judul');
            $table->text('deskripsi')->nullable();

            $table->dateTime('deadline');

            $table->integer('nilai_maksimal')->default(100);

            $table->string('lampiran')->nullable();

            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->foreign('jadwal_id')
                ->references('id')
                ->on('jadwal')
                ->cascadeOnDelete();

            $table->foreign('pertemuan_id')
                ->references('pertemuan_id')
                ->on('pertemuan')
                ->cascadeOnDelete();

            $table->foreign('dosen_id')
                ->references('dosen_id')
                ->on('dosen')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_tugas');
    }
};
