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
    Schema::create('permintaan_perubahans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('jenis_permintaan', ['bug', 'fitur_baru', 'update_data']);
    $table->string('judul');
    $table->text('deskripsi');
    $table->enum('prioritas', ['low', 'medium', 'high'])->nullable();
    $table->string('file_lampiran')->nullable();
    $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'revisi', 'selesai'])->default('menunggu');
    $table->text('komentar_admin')->nullable();
    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permintapermintaan_perubahansans');
    }
};
