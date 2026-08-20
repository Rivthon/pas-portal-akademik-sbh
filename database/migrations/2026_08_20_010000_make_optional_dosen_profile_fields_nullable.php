<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->string('tempat', 50)->nullable()->change();
            $table->date('tanggal_lahir')->nullable()->change();
            $table->string('alamat', 120)->nullable()->change();
            $table->string('no_telp', 13)->nullable()->change();
        });
    }

    public function down(): void
    {
        // Tidak dikembalikan ke NOT NULL agar record yang sudah kosong tetap aman.
    }
};
