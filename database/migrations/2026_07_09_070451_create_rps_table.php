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
        Schema::create('rps', function (Blueprint $table) {
            $table->id('rps_id'); // Primary key untuk rps tetap aman pakai BigInt

            // 1. Menyelaraskan kurikulum_id ke int(11)
            $table->integer('kurikulum_id');
            $table->foreign('kurikulum_id')
                ->references('kurikulum_id')
                ->on('kurikulum')
                ->onDelete('cascade');

            // 2. Menyelaraskan dosen_id ke int(11) sesuai gambar struktur barumu
            $table->integer('dosen_id');
            $table->foreign('dosen_id')
                ->references('dosen_id')
                ->on('dosen')
                ->onDelete('cascade');

            // Kolom pendukung
            $table->string('nama_file')->nullable();
            $table->string('file')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rps');
    }
};
