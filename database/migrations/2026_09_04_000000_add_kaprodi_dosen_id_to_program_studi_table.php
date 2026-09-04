<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            $table->integer('kaprodi_dosen_id')
                ->nullable()
                ->after('kaprod');

            $table->foreign('kaprodi_dosen_id', 'program_studi_kaprodi_dosen_fk')
                ->references('dosen_id')
                ->on('dosen')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('program_studi', function (Blueprint $table) {
            $table->dropForeign('program_studi_kaprodi_dosen_fk');
            $table->dropColumn('kaprodi_dosen_id');
        });
    }
};
