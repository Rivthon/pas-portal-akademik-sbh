<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('krs', 'jenis_kelas')) {
            Schema::table('krs', function (Blueprint $table) {
                $table->string('jenis_kelas', 20)->nullable()->after('mahasiswa_id');
                $table->index(['ta_id', 'kurikulum_id', 'jenis_kelas'], 'krs_course_class_index');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('krs', 'jenis_kelas')) {
            Schema::table('krs', function (Blueprint $table) {
                $table->dropIndex('krs_course_class_index');
                $table->dropColumn('jenis_kelas');
            });
        }
    }
};
