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
        Schema::create('penilaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mahasiswa_id'); // ID Mahasiswa
            $table->unsignedBigInteger('dosen_id'); // ID Dosen
            $table->unsignedBigInteger('evaluasi_id'); // ID Evaluasi
            $table->unsignedTinyInteger('nilai'); // Skor penilaian, misalnya 1-5
            $table->timestamps();

            // Optional: Add indexes for performance
            $table->index(['mahasiswa_id', 'dosen_id', 'evaluasi_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penilaian');
    }
};
