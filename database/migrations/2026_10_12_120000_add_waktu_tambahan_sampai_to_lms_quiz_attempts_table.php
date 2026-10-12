<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('lms_quiz_attempts', 'waktu_tambahan_sampai')) {
            Schema::table('lms_quiz_attempts', function (Blueprint $table) {
                $table->timestamp('waktu_tambahan_sampai')->nullable()->after('izinkan_ulang');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('lms_quiz_attempts', 'waktu_tambahan_sampai')) {
            Schema::table('lms_quiz_attempts', function (Blueprint $table) {
                $table->dropColumn('waktu_tambahan_sampai');
            });
        }
    }
};
