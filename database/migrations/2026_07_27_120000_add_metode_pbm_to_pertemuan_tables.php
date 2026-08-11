<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->string('metode_pbm', 10)->default('offline')->after('jam_selesai');
        });

        Schema::table('pertemuan_praktik', function (Blueprint $table) {
            $table->string('metode_pbm', 10)->default('offline')->after('jam_selesai');
        });
    }

    public function down(): void
    {
        Schema::table('pertemuan', function (Blueprint $table) {
            $table->dropColumn('metode_pbm');
        });

        Schema::table('pertemuan_praktik', function (Blueprint $table) {
            $table->dropColumn('metode_pbm');
        });
    }
};
