<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Modifikasi Tabel Tenor Pembayaran
        Schema::table('tenor_pembayaran', function (Blueprint $table) {
            // Menambahkan foreign key untuk angkatan (tahun masuk bisa pakai integer)
            if (! Schema::hasColumn('tenor_pembayaran', 'tahun_masuk')) {
                $table->string('tahun_masuk', 4)->nullable()->after('id');
            }
            // Menambahkan foreign key gelombang_id
            if (! Schema::hasColumn('tenor_pembayaran', 'gelombang_id')) {
                $table->unsignedBigInteger('gelombang_id')->nullable()->after('tahun_masuk');
            }
        });

        // 2. Buat Tabel Transaksi Pembayaran
        Schema::create('transaksi_pembayaran', function (Blueprint $table) {
            $table->id('transaksi_id');
            $table->unsignedBigInteger('mahasiswa_id');
            $table->unsignedBigInteger('tagihan_mahasiswa_id');
            $table->integer('nominal_bayar');
            $table->date('tanggal_bayar');
            $table->string('bukti_bayar')->nullable(); // path file resi/bukti tf
            $table->string('keterangan')->nullable();
            $table->enum('status_verifikasi', ['pending', 'diterima', 'ditolak'])->default('diterima'); // Default ke diterima karena diinput TU
            $table->timestamps();

            // Database level foreign keys removed to prevent type mismatch errors (MySQL 1215).
            // Referential integrity will be handled via Eloquent Models.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaksi_pembayaran');

        Schema::table('tenor_pembayaran', function (Blueprint $table) {
            $table->dropColumn(['tahun_masuk', 'gelombang_id']);
        });
    }
};
