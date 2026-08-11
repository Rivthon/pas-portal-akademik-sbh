<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->unsignedBigInteger('gelombang_id')->after('tahun_masuk')->nullable();
        });

        Schema::table('tarif_persemester', function (Blueprint $table) {
            $table->unsignedBigInteger('gelombang_id')->after('tahun_masuk')->nullable();
        });
    }

    public function down()
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn('gelombang_id');
        });

        Schema::table('tarif_persemester', function (Blueprint $table) {
            $table->dropColumn('gelombang_id');
        });
    }
};
