<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('audit_duplicate_dosen_mata_kuliah_20260928')) {
            Schema::create('audit_duplicate_dosen_mata_kuliah_20260928', function (Blueprint $table) {
                $table->unsignedBigInteger('source_id')->primary();
                $table->unsignedBigInteger('dosen_id');
                $table->unsignedBigInteger('kurikulum_id');
                $table->string('jenis_dosen')->nullable();
                $table->string('jenis_kelas')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('archived_at')->useCurrent();
            });
        }

        // NULL dan string kosong selama ini diperlakukan sama oleh resolver kelas.
        // Normalisasi diperlukan agar unique index juga efektif pada MySQL.
        DB::table('dosen_mata_kuliah')->whereNull('jenis_dosen')->update(['jenis_dosen' => '']);
        DB::table('dosen_mata_kuliah')->whereNull('jenis_kelas')->update(['jenis_kelas' => '']);

        $duplicates = DB::table('dosen_mata_kuliah')
            ->selectRaw('MIN(id) AS keep_id, dosen_id, kurikulum_id, jenis_dosen, jenis_kelas')
            ->groupBy('dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $removedRows = DB::table('dosen_mata_kuliah')
                ->where('dosen_id', $duplicate->dosen_id)
                ->where('kurikulum_id', $duplicate->kurikulum_id)
                ->where('jenis_dosen', $duplicate->jenis_dosen)
                ->where('jenis_kelas', $duplicate->jenis_kelas)
                ->where('id', '!=', $duplicate->keep_id)
                ->get();

            foreach ($removedRows as $row) {
                DB::table('audit_duplicate_dosen_mata_kuliah_20260928')->updateOrInsert(
                    ['source_id' => $row->id],
                    [
                        'dosen_id' => $row->dosen_id,
                        'kurikulum_id' => $row->kurikulum_id,
                        'jenis_dosen' => $row->jenis_dosen,
                        'jenis_kelas' => $row->jenis_kelas,
                        'created_at' => $row->created_at,
                        'updated_at' => $row->updated_at,
                        'archived_at' => now(),
                    ]
                );
            }

            DB::table('dosen_mata_kuliah')
                ->whereIn('id', $removedRows->pluck('id'))
                ->delete();
        }

        if (! $this->indexExists('dosen_mata_kuliah', 'dmk_assignment_unique')) {
            Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
                $table->unique(
                    ['dosen_id', 'kurikulum_id', 'jenis_dosen', 'jenis_kelas'],
                    'dmk_assignment_unique'
                );
            });
        }

        DB::table('mahasiswa')
            ->whereNotNull('dosen_id')
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('dosen')
                    ->whereColumn('dosen.dosen_id', 'mahasiswa.dosen_id');
            })
            ->update(['dosen_id' => null]);

        // Struktur legacy memakai default zero timestamp yang ditolak MySQL saat
        // ALTER TABLE dalam strict mode. Normalisasi dahulu sebelum menambah FK.
        // Bandingkan sebagai teks. Pada MySQL 8 dengan NO_ZERO_DATE aktif,
        // literal tanggal '0000-00-00' sendiri ditolak sebelum UPDATE berjalan.
        DB::statement("UPDATE mahasiswa SET tanggal_lahir = NULL WHERE CAST(tanggal_lahir AS CHAR) = '0000-00-00'");
        DB::statement('ALTER TABLE mahasiswa MODIFY updated_at TIMESTAMP NULL DEFAULT NULL');

        if (! $this->foreignKeyExists('mahasiswa', 'mahasiswa_dosen_pembimbing_fk')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->foreign('dosen_id', 'mahasiswa_dosen_pembimbing_fk')
                    ->references('dosen_id')
                    ->on('dosen')
                    ->nullOnDelete();
            });
        }

        DB::statement('ALTER TABLE evaluasi MODIFY nama VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL');
        DB::statement('ALTER TABLE evaluasi CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    }

    public function down(): void
    {
        if ($this->foreignKeyExists('mahasiswa', 'mahasiswa_dosen_pembimbing_fk')) {
            Schema::table('mahasiswa', function (Blueprint $table) {
                $table->dropForeign('mahasiswa_dosen_pembimbing_fk');
            });
        }

        if ($this->indexExists('dosen_mata_kuliah', 'dmk_assignment_unique')) {
            Schema::table('dosen_mata_kuliah', function (Blueprint $table) {
                $table->dropUnique('dmk_assignment_unique');
            });
        }

        if (Schema::hasTable('audit_duplicate_dosen_mata_kuliah_20260928')) {
            foreach (DB::table('audit_duplicate_dosen_mata_kuliah_20260928')->get() as $row) {
                DB::table('dosen_mata_kuliah')->insertOrIgnore([
                    'id' => $row->source_id,
                    'dosen_id' => $row->dosen_id,
                    'kurikulum_id' => $row->kurikulum_id,
                    'jenis_dosen' => $row->jenis_dosen,
                    'jenis_kelas' => $row->jenis_kelas,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }

            Schema::drop('audit_duplicate_dosen_mata_kuliah_20260928');
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        return (int) DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->count() > 0;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        return (int) DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $constraint)
            ->where('constraint_type', 'FOREIGN KEY')
            ->count() > 0;
    }
};
