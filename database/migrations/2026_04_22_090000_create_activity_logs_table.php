<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Tabel activity_logs untuk menyimpan log aktivitas semua jenis user.
     * Mendukung multi-auth: admin (guard: web), mahasiswa, dosen.
     */
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 50)->comment('ID user (string karena Dosen PK string)');
            $table->string('user_type', 20)->comment('admin / mahasiswa / dosen');
            $table->string('aktivitas', 100)->comment('Nama aktivitas: login, logout, akses_krs, dll');
            $table->text('deskripsi')->nullable()->comment('Keterangan tambahan');
            $table->string('ip_address', 45)->nullable()->comment('IPv4/IPv6 address');
            $table->text('user_agent')->nullable()->comment('Browser user agent');
            $table->timestamp('created_at')->useCurrent();

            // Indexes untuk performa query
            $table->index(['user_type', 'user_id'], 'idx_activity_user');
            $table->index('aktivitas', 'idx_activity_aktivitas');
            $table->index('created_at', 'idx_activity_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
