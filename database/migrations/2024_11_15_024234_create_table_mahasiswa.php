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
        Schema::create('mahasiswa', function (Blueprint $table) {
            // Primary key
            $table->id('mahasiswa_id');

            // Basic information
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');

            // Foreign key reference to the programs table
            $table->foreignId('program_studi_id')
            ->constrained('program_studi', 'program_studi_id') // Use the constrained method for better readability
                ->onDelete('cascade');

            // Student identification
            $table->string('nim')->unique();

            // Timestamps for created_at and updated_at
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_mahasiswa');
    }
};