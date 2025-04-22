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
        Schema::create('dosen_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dosen_id'); // ID dosen (tanpa foreign key)
            $table->unsignedBigInteger('kurikulum_id'); // ID kurikulum (tanpa foreign key)
            $table->timestamps();

            // Menambahkan unique constraint untuk mencegah duplikasi data
            $table->unique(['dosen_id', 'kurikulum_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen_mata_kuliah');
    }
};
