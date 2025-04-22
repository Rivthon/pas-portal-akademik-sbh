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
        Schema::create('tenor_pembayaran', function (Blueprint $table) {
            $table->id();
            $table->integer('semester');
            $table->string('tenor'); // Misal: "20%", "50%", "100%"
            $table->decimal('persentase', 5, 2); // Simpan dalam format persen (misal: 20.00)
            $table->date('batas_waktu')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenor_pembayaran');
    }
};
