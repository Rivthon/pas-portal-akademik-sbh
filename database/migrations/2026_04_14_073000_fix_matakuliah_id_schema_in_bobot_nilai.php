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
        // Hapus tabel dengan format integer yang salah
        Schema::dropIfExists('bobot_nilai');

        // Recreate dengan format string
        Schema::create('bobot_nilai', function (Blueprint $table) {
            $table->id();

            // ID dari program studi dan mata kuliah menggunakan string karena format aslinya seperti 'Far.510' dan '48201'
            $table->string('program_studi_id', 50);
            $table->string('matakuliah_id', 50)->nullable(); // nullable = default untuk prodi

            // Persentase komponen nilai
            $table->decimal('persen_tugas', 5, 2)->default(0);
            $table->decimal('persen_uts', 5, 2)->default(0);
            $table->decimal('persen_uas', 5, 2)->default(0);
            $table->decimal('persen_absen', 5, 2)->default(0);
            $table->decimal('persen_praktik', 5, 2)->default(0);

            $table->timestamps();

            // Hindari duplikat kombinasi prodi + mata kuliah
            $table->unique(['program_studi_id', 'matakuliah_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_nilai');
    }
};
