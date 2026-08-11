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
        Schema::table('saran', function (Blueprint $table) {
            $table->string('jenis_dosen')->nullable()->after('saran'); // Menambahkan kolom jenis_dosen
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('saran', function (Blueprint $table) {
            //
        });
    }
};
