<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Menambahkan UNIQUE constraint untuk mencegah duplikasi data
     * pada tabel penilaian dan saran.
     *
     * Sebelum menambahkan constraint, hapus data duplikat yang sudah ada
     * agar migration tidak gagal.
     */
    public function up(): void
    {
        // ========================================
        // 1. Ubah dosen_id menjadi VARCHAR di tabel penilaian dan saran
        //    (menyesuaikan dengan tipe dosen_id di tabel dosen)
        // ========================================

        // Cek apakah kolom dosen_id perlu diubah ke varchar di tabel penilaian
        Schema::table('penilaian', function (Blueprint $table) {
            $table->string('dosen_id', 50)->change();
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->string('dosen_id', 50)->change();
        });

        // ========================================
        // 2. Hapus data duplikat di tabel penilaian (simpan yang paling lama/pertama)
        // ========================================
        $duplicatePenilaian = DB::table('penilaian as p1')
            ->join(DB::raw('(SELECT MIN(id) as min_id, mahasiswa_id, dosen_id, kurikulum_id, evaluasi_id FROM penilaian GROUP BY mahasiswa_id, dosen_id, kurikulum_id, evaluasi_id) as p2'), function ($join) {
                $join->on('p1.mahasiswa_id', '=', 'p2.mahasiswa_id')
                    ->on('p1.dosen_id', '=', 'p2.dosen_id')
                    ->on('p1.kurikulum_id', '=', 'p2.kurikulum_id')
                    ->on('p1.evaluasi_id', '=', 'p2.evaluasi_id');
            })
            ->where('p1.id', '!=', DB::raw('p2.min_id'))
            ->pluck('p1.id');

        if ($duplicatePenilaian->isNotEmpty()) {
            DB::table('penilaian')->whereIn('id', $duplicatePenilaian)->delete();
        }

        // ========================================
        // 3. Hapus data duplikat di tabel saran (simpan yang paling lama/pertama)
        // ========================================
        $duplicateSaran = DB::table('saran as s1')
            ->join(DB::raw('(SELECT MIN(id) as min_id, mahasiswa_id, dosen_id, kurikulum_id FROM saran GROUP BY mahasiswa_id, dosen_id, kurikulum_id) as s2'), function ($join) {
                $join->on('s1.mahasiswa_id', '=', 's2.mahasiswa_id')
                    ->on('s1.dosen_id', '=', 's2.dosen_id')
                    ->on('s1.kurikulum_id', '=', 's2.kurikulum_id');
            })
            ->where('s1.id', '!=', DB::raw('s2.min_id'))
            ->pluck('s1.id');

        if ($duplicateSaran->isNotEmpty()) {
            DB::table('saran')->whereIn('id', $duplicateSaran)->delete();
        }

        // ========================================
        // 4. Tambahkan UNIQUE constraint
        // ========================================
        Schema::table('penilaian', function (Blueprint $table) {
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id', 'evaluasi_id'],
                'penilaian_unique_entry'
            );
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id'],
                'saran_unique_entry'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropUnique('penilaian_unique_entry');
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->dropUnique('saran_unique_entry');
        });
    }
};
