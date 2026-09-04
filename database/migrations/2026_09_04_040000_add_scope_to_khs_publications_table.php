<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('khs_publications', function (Blueprint $table) {
            $table->dropUnique('khs_publications_ta_id_program_studi_id_unique');
            $table->string('scope_type', 20)->default('all')->after('program_studi_id');
            $table->string('scope_key', 40)->default('all')->after('scope_type');
            $table->unsignedTinyInteger('semester')->nullable()->after('scope_key');
            $table->unsignedBigInteger('jadwal_id')->nullable()->after('semester');
            $table->unique(['ta_id', 'program_studi_id', 'scope_key'], 'khs_publications_scope_unique');
            $table->index('jadwal_id', 'khs_publications_jadwal_index');
        });
    }

    public function down(): void
    {
        Schema::table('khs_publications', function (Blueprint $table) {
            $table->dropIndex('khs_publications_jadwal_index');
            $table->dropUnique('khs_publications_scope_unique');
            $table->dropColumn(['scope_type', 'scope_key', 'semester', 'jadwal_id']);
            $table->unique(['ta_id', 'program_studi_id']);
        });
    }
};
