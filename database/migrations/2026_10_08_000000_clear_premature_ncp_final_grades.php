<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('krs') || ! Schema::hasTable('tahun_ajaran')) {
            return;
        }

        $activeAcademicYearIds = DB::table('tahun_ajaran')
            ->where('status_ta', 1)
            ->pluck('ta_id');

        if ($activeAcademicYearIds->isEmpty()) {
            return;
        }

        $query = DB::table('krs')
            ->join('mahasiswa', 'mahasiswa.mahasiswa_id', '=', 'krs.mahasiswa_id')
            ->whereIn('krs.ta_id', $activeAcademicYearIds)
            ->whereIn('krs.matakuliah_id', ['GZ.-313', 'GZ.313'])
            ->where('krs.khs', 'E')
            ->whereNotNull('krs.tugas')
            ->where(function (Builder $query) {
                $query->whereNull('krs.uts')->orWhereNull('krs.uas');
            });

        // Nilai yang sudah masuk cakupan penerbitan resmi tidak disentuh.
        if (Schema::hasTable('khs_publications')) {
            $query->whereNotExists(function (Builder $publication) {
                $publication->selectRaw('1')
                    ->from('khs_publications')
                    ->whereColumn('khs_publications.ta_id', 'krs.ta_id')
                    ->whereColumn('khs_publications.program_studi_id', 'mahasiswa.jurusan_id');
            });
        }

        $query->update([
            'krs.akhir' => null,
            'krs.khs' => null,
            'krs.updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Nilai tugas LMS tetap ada. Nilai akhir harus dihitung kembali setelah
        // dosen melengkapi seluruh komponen melalui modul Input Nilai.
    }
};
