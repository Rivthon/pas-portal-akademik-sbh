<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->integer('disetujui_oleh')->nullable()->after('mahasiswa_id');
            $table->timestamp('disetujui_pada')->nullable()->after('disetujui_oleh');

            $table->foreign('disetujui_oleh')
                ->references('dosen_id')
                ->on('dosen')
                ->nullOnDelete();
            $table->index(['ta_id', 'mahasiswa_id', 'disetujui_pada'], 'krs_approval_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropForeign(['disetujui_oleh']);
            $table->dropIndex('krs_approval_lookup_index');
            $table->dropColumn(['disetujui_oleh', 'disetujui_pada']);
        });
    }
};
