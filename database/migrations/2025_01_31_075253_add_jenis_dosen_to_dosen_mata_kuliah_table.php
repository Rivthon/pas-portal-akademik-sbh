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
         Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
            $table->enum('jenis_dosen', ['teori', 'praktik'])->after('kurikulum_id')->default('teori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
            //
        });
    }
};