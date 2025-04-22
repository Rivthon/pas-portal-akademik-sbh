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
        Schema::table('absensi', function (Blueprint $table) {
            $table->unsignedBigInteger('pertemuan_id')->nullable()->after('mahasiswa_id'); // Tambahkan kolom di setelah mahasiswa_id
            $table->foreign('pertemuan_id')->references('pertemuan_id')->on('pertemuan')->onDelete('cascade'); // Foreign Key
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('absensi', function (Blueprint $table) {
            $table->dropForeign(['pertemuan_id']); // Hapus foreign key
            $table->dropColumn('pertemuan_id');   // Hapus kolom
        });
    }
};
