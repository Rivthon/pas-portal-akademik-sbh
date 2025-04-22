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
        Schema::table('jadwal', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('jadwal_id'); // Kolom user_id
            $table->unsignedBigInteger('program_studi_id')->nullable()->after('user_id'); // Kolom program_studi_id

            // Tambahkan foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('program_studi_id')->references('program_studi_id')->on('program_studi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['program_studi_id']);
            $table->dropColumn(['user_id', 'program_studi_id']);
        });
    }
};
