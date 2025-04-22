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
        Schema::create('pertemuan', function (Blueprint $table) {
            $table->id('pertemuan_id'); // Primary Key
            $table->unsignedBigInteger('jadwal_id'); // Foreign Key ke tabel jadwal
            $table->date('tanggal_pertemuan'); // Tanggal pertemuan
            $table->string('topik')->nullable(); // Topik/materi pertemuan
            $table->timestamps(); // Kolom created_at dan updated_at

            // Foreign Key Constraint
            $table->foreign('jadwal_id')->references('jadwal_id')->on('jadwal')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertemuan');
    }
};
