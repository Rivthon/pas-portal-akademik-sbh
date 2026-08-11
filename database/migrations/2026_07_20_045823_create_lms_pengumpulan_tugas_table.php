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
        Schema::create('lms_pengumpulan_tugas', function (Blueprint $table) {

            $table->bigIncrements('pengumpulan_id');

            $table->unsignedBigInteger('tugas_id');

            $table->integer('mahasiswa_id');

            $table->string('file');

            $table->text('catatan')->nullable();

            $table->timestamp('waktu_upload');

            $table->integer('nilai')->nullable();

            $table->text('feedback')->nullable();

            $table->timestamps();

            $table->foreign('tugas_id')
                ->references('tugas_id')
                ->on('lms_tugas')
                ->cascadeOnDelete();

            $table->foreign('mahasiswa_id')
                ->references('mahasiswa_id')
                ->on('mahasiswa')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lms_pengumpulan_tugas');
    }
};
