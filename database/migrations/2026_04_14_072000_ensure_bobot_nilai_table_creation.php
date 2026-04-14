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
        if (!Schema::hasTable('bobot_nilai')) {
            Schema::create('bobot_nilai', function (Blueprint $table) {
                $table->id();

                // ID dari program studi dan mata kuliah tanpa foreign key
                $table->unsignedBigInteger('program_studi_id');
                $table->unsignedBigInteger('matakuliah_id')->nullable(); // nullable = default untuk prodi

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bobot_nilai');
    }
};
