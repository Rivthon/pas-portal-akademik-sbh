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
        Schema::create('pertemuan_praktik', function (Blueprint $table) {
            $table->id('pertemuan_praktik_id'); // Primary Key
            $table->unsignedBigInteger('dosen_id');
            $table->unsignedBigInteger('jadwal_praktik_id'); // Foreign Key ke tabel jadwal
            $table->date('tanggal_pertemuan'); // Tanggal pertemuan
            $table->string('topik')->nullable(); // Topik/materi pertemuan
            $table->string('sub_topik')->nullable(); // Topik/materi pertemuan
            $table->time('jam_mulai'); // Waktu mulai pertemuan
            $table->time('jam_selesai'); // Waktu selesai pertemuan
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan_praktik');
    }
};
