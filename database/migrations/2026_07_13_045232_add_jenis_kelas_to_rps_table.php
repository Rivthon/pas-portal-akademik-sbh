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
        Schema::table('rps', function (Blueprint $table) {
            $table->enum('jenis_kelas', ['reguler', 'karyawan'])
                ->after('dosen_id');
        });
    }

    public function down()
    {
        Schema::table('rps', function (Blueprint $table) {
            $table->dropColumn('jenis_kelas');
        });
    }
};
