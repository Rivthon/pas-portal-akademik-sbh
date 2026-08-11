<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('krs', 'matakuliah_id')) {
            Schema::table('krs', function (Blueprint $table) {
                $table->string('matakuliah_id', 15)->nullable()->after('kurikulum_id');
            });
        }

        DB::statement(
            'UPDATE krs
             INNER JOIN kurikulum ON kurikulum.kurikulum_id = krs.kurikulum_id
             SET krs.matakuliah_id = kurikulum.matakuliah_id
             WHERE krs.matakuliah_id IS NULL'
        );

        $this->mergeDuplicateEnrollments();

        Schema::table('krs', function (Blueprint $table) {
            $table->unique(
                ['mahasiswa_id', 'kurikulum_id', 'ta_id'],
                'krs_unique_curriculum_enrollment'
            );
            $table->unique(
                ['mahasiswa_id', 'matakuliah_id', 'ta_id'],
                'krs_unique_course_enrollment'
            );
        });
    }

    public function down(): void
    {
        Schema::table('krs', function (Blueprint $table) {
            $table->dropUnique('krs_unique_curriculum_enrollment');
            $table->dropUnique('krs_unique_course_enrollment');
            $table->dropColumn('matakuliah_id');
        });
    }

    private function mergeDuplicateEnrollments(): void
    {
        $groups = DB::table('krs')
            ->whereNotNull('matakuliah_id')
            ->select('mahasiswa_id', 'ta_id', 'matakuliah_id')
            ->groupBy('mahasiswa_id', 'ta_id', 'matakuliah_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $gradeColumns = ['khs', 'uts', 'uas', 'akhir', 'tugas', 'absen', 'praktik'];

        foreach ($groups as $group) {
            $rows = DB::table('krs')
                ->where('mahasiswa_id', $group->mahasiswa_id)
                ->where('ta_id', $group->ta_id)
                ->where('matakuliah_id', $group->matakuliah_id)
                ->orderBy('krs_id')
                ->get();

            $keeper = $rows->sortByDesc(function ($row) use ($gradeColumns) {
                return collect($gradeColumns)->filter(
                    fn ($column) => $row->{$column} !== null && $row->{$column} !== ''
                )->count();
            })->first();

            $duplicateIds = $rows->pluck('krs_id')
                ->reject(fn ($id) => (int) $id === (int) $keeper->krs_id)
                ->values();

            $mergedGrades = [];
            foreach ($gradeColumns as $column) {
                $value = $keeper->{$column};
                if ($value === null || $value === '') {
                    $value = $rows->pluck($column)
                        ->first(fn ($candidate) => $candidate !== null && $candidate !== '');
                }
                $mergedGrades[$column] = $value;
            }

            DB::table('krs')->where('krs_id', $keeper->krs_id)->update($mergedGrades);

            foreach (['penilaian', 'saran'] as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'krs_id')) {
                    DB::table($table)
                        ->whereIn('krs_id', $duplicateIds)
                        ->update(['krs_id' => $keeper->krs_id]);
                }
            }

            DB::table('krs')->whereIn('krs_id', $duplicateIds)->delete();
        }
    }
};
