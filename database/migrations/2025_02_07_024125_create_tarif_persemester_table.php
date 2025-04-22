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
         Schema::create('tarif_persemester', function (Blueprint $table) {
            $table->id();
            $table->string('jurusan_id');
            $table->integer('semester');
            $table->integer('ta_id');
            $table->decimal('tarif', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tarif_persemester');
    }
};
