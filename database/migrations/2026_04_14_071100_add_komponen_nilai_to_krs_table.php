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
        Schema::table('krs', function (Blueprint $table) {
            if (! Schema::hasColumn('krs', 'tugas')) {
                $table->decimal('tugas', 5, 2)->nullable()->after('uas');
            }
            if (! Schema::hasColumn('krs', 'absen')) {
                $table->decimal('absen', 5, 2)->nullable()->after('tugas');
            }
            if (! Schema::hasColumn('krs', 'praktik')) {
                $table->decimal('praktik', 5, 2)->nullable()->after('absen');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            if (Schema::hasColumn('krs', 'tugas')) {
                $table->dropColumn('tugas');
            }
            if (Schema::hasColumn('krs', 'absen')) {
                $table->dropColumn('absen');
            }
            if (Schema::hasColumn('krs', 'praktik')) {
                $table->dropColumn('praktik');
            }
        });
    }
};
