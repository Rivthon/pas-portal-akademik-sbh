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
        Schema::create('matakuliah', function (Blueprint $table) {
            // Primary Key
            $table->id('matakuliah_id');

            // Foreign Key
            $table->foreignId('program_studi_id')
                ->constrained('program_studi', 'program_studi_id') // Use the constrained method for better readability
                ->onDelete('cascade');

            // Other Columns
            $table->string('name');
            $table->string('code')->unique();
            $table->integer('semester');

            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};
