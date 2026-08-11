<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->timestamp('dinilai_pada')
                ->nullable()
                ->after('feedback');

            $table->integer('dinilai_oleh')
                ->nullable()
                ->after('dinilai_pada');

            $table->foreign('dinilai_oleh')
                ->references('dosen_id')
                ->on('dosen')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('lms_pengumpulan_tugas', function (Blueprint $table) {
            $table->dropForeign(
                ['dinilai_oleh']
            );

            $table->dropColumn([
                'dinilai_pada',
                'dinilai_oleh',
            ]);
        });
    }
};
