<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah index untuk optimasi performa query pada tabel jadwal, jadwal_uts, jadwal_uas, kurikulum, dan matakuliah.
     * Index ini akan mempercepat query filter, join, dan whereHas yang sering digunakan.
     */
    public function up(): void
    {
        // Index untuk tabel jadwal_uts
        Schema::table('jadwal_uts', function (Blueprint $table) {
            $table->index('ta_id', 'idx_jadwal_uts_ta_id');
            $table->index('jurusan_id', 'idx_jadwal_uts_jurusan_id');
            $table->index('matakuliah_id', 'idx_jadwal_uts_matakuliah_id');
            $table->index('ruangan_id', 'idx_jadwal_uts_ruangan_id');
            $table->index('jenis_kelas', 'idx_jadwal_uts_jenis_kelas');
            $table->index(['ta_id', 'jurusan_id', 'jenis_kelas'], 'idx_jadwal_uts_composite');
        });

        // Index untuk tabel jadwal_uas
        Schema::table('jadwal_uas', function (Blueprint $table) {
            $table->index('ta_id', 'idx_jadwal_uas_ta_id');
            $table->index('jurusan_id', 'idx_jadwal_uas_jurusan_id');
            $table->index('matakuliah_id', 'idx_jadwal_uas_matakuliah_id');
            $table->index('ruangan_id', 'idx_jadwal_uas_ruangan_id');
            $table->index('jenis_kelas', 'idx_jadwal_uas_jenis_kelas');
            $table->index(['ta_id', 'jurusan_id', 'jenis_kelas'], 'idx_jadwal_uas_composite');
        });

        // Index untuk tabel jadwal (kuliah)
        Schema::table('jadwal', function (Blueprint $table) {
            $table->index('ta_id', 'idx_jadwal_ta_id');
            $table->index('jurusan_id', 'idx_jadwal_jurusan_id');
            $table->index('kurikulum_id', 'idx_jadwal_kurikulum_id');
            $table->index('ruangan_id', 'idx_jadwal_ruangan_id');
            $table->index('jenis_kelas', 'idx_jadwal_jenis_kelas');
            $table->index(['ta_id', 'jurusan_id', 'jenis_kelas'], 'idx_jadwal_composite');
        });

        // Index untuk tabel kurikulum
        Schema::table('kurikulum', function (Blueprint $table) {
            $table->index('ta_id', 'idx_kurikulum_ta_id');
            $table->index('jurusan_id', 'idx_kurikulum_jurusan_id');
            $table->index('matakuliah_id', 'idx_kurikulum_matakuliah_id');
            $table->index(['ta_id', 'jurusan_id'], 'idx_kurikulum_ta_jurusan');
        });

        // Index untuk tabel matakuliah
        Schema::table('matakuliah', function (Blueprint $table) {
            $table->index('smt', 'idx_matakuliah_smt');
        });

        // Index untuk tabel krs
        Schema::table('krs', function (Blueprint $table) {
            $table->index('mahasiswa_id', 'idx_krs_mahasiswa_id');
            $table->index('kurikulum_id', 'idx_krs_kurikulum_id');
            $table->index('ta_id', 'idx_krs_ta_id');
            $table->index(['mahasiswa_id', 'kurikulum_id', 'ta_id'], 'idx_krs_composite');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_uts', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_uts_ta_id');
            $table->dropIndex('idx_jadwal_uts_jurusan_id');
            $table->dropIndex('idx_jadwal_uts_matakuliah_id');
            $table->dropIndex('idx_jadwal_uts_ruangan_id');
            $table->dropIndex('idx_jadwal_uts_jenis_kelas');
            $table->dropIndex('idx_jadwal_uts_composite');
        });

        Schema::table('jadwal_uas', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_uas_ta_id');
            $table->dropIndex('idx_jadwal_uas_jurusan_id');
            $table->dropIndex('idx_jadwal_uas_matakuliah_id');
            $table->dropIndex('idx_jadwal_uas_ruangan_id');
            $table->dropIndex('idx_jadwal_uas_jenis_kelas');
            $table->dropIndex('idx_jadwal_uas_composite');
        });

        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropIndex('idx_jadwal_ta_id');
            $table->dropIndex('idx_jadwal_jurusan_id');
            $table->dropIndex('idx_jadwal_kurikulum_id');
            $table->dropIndex('idx_jadwal_ruangan_id');
            $table->dropIndex('idx_jadwal_jenis_kelas');
            $table->dropIndex('idx_jadwal_composite');
        });

        Schema::table('kurikulum', function (Blueprint $table) {
            $table->dropIndex('idx_kurikulum_ta_id');
            $table->dropIndex('idx_kurikulum_jurusan_id');
            $table->dropIndex('idx_kurikulum_matakuliah_id');
            $table->dropIndex('idx_kurikulum_ta_jurusan');
        });

        Schema::table('matakuliah', function (Blueprint $table) {
            $table->dropIndex('idx_matakuliah_smt');
        });

        Schema::table('krs', function (Blueprint $table) {
            $table->dropIndex('idx_krs_mahasiswa_id');
            $table->dropIndex('idx_krs_kurikulum_id');
            $table->dropIndex('idx_krs_ta_id');
            $table->dropIndex('idx_krs_composite');
        });
    }
};
