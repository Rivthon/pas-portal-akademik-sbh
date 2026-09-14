<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->string('jenis_kelas', 20)->nullable()->after('jenis_dosen');
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->string('jenis_kelas', 20)->nullable()->after('jenis_dosen');
        });

        $this->backfillJenisKelas('penilaian');
        $this->backfillJenisKelas('saran');

        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropUnique('penilaian_unique_entry');
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas', 'evaluasi_id'],
                'penilaian_unique_entry'
            );
            $table->index(['dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'], 'penilaian_edom_report_index');
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->dropUnique('saran_unique_entry');
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'],
                'saran_unique_entry'
            );
            $table->index(['dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'], 'saran_edom_report_index');
        });
    }

    public function down(): void
    {
        Schema::table('penilaian', function (Blueprint $table) {
            $table->dropIndex('penilaian_edom_report_index');
            $table->dropUnique('penilaian_unique_entry');
            $table->dropColumn('jenis_kelas');
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id', 'evaluasi_id'],
                'penilaian_unique_entry'
            );
        });

        Schema::table('saran', function (Blueprint $table) {
            $table->dropIndex('saran_edom_report_index');
            $table->dropUnique('saran_unique_entry');
            $table->dropColumn('jenis_kelas');
            $table->unique(
                ['mahasiswa_id', 'dosen_id', 'kurikulum_id'],
                'saran_unique_entry'
            );
        });
    }

    private function backfillJenisKelas(string $table): void
    {
        DB::table($table)
            ->select('id', 'mahasiswa_id')
            ->orderBy('id')
            ->chunkById(500, function ($rows) use ($table) {
                $mahasiswaKelas = DB::table('mahasiswa')
                    ->whereIn('mahasiswa_id', $rows->pluck('mahasiswa_id')->unique())
                    ->pluck('kelas', 'mahasiswa_id');

                $rows->groupBy(function ($row) use ($mahasiswaKelas) {
                    return $this->normalizeJenisKelas($mahasiswaKelas[$row->mahasiswa_id] ?? null);
                })->each(function ($group, $jenisKelas) use ($table) {
                    DB::table($table)->whereIn('id', $group->pluck('id'))->update([
                        'jenis_kelas' => $jenisKelas,
                    ]);
                });
            });
    }

    private function normalizeJenisKelas(?string $kelas): string
    {
        return match (strtolower(trim((string) $kelas))) {
            'karyawan', 'reguler b' => 'karyawan',
            default => 'reguler',
        };
    }
};
