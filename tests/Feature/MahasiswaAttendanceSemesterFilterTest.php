<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MahasiswaAttendanceSemesterFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_mahasiswa_can_filter_attendance_recap_by_semester(): void
    {
        $mahasiswaId = DB::table('krs')
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->select('krs.mahasiswa_id')
            ->groupBy('krs.mahasiswa_id')
            ->havingRaw('COUNT(DISTINCT matakuliah.smt) >= 2')
            ->value('krs.mahasiswa_id');
        $mahasiswa = Mahasiswa::findOrFail($mahasiswaId);
        $semesters = Krs::query()
            ->join('kurikulum', 'kurikulum.kurikulum_id', '=', 'krs.kurikulum_id')
            ->join('matakuliah', 'matakuliah.matakuliah_id', '=', 'kurikulum.matakuliah_id')
            ->where('krs.mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->distinct()
            ->orderBy('matakuliah.smt')
            ->pluck('matakuliah.smt')
            ->map(fn ($semester) => (int) $semester)
            ->take(2)
            ->values();

        foreach ($semesters as $semester) {
            $response = $this->actingAs($mahasiswa, 'mahasiswa')
                ->get(route('mahasiswa.rekap.absensi', ['semester' => $semester]))
                ->assertOk()
                ->assertSee('Semester '.$semester);

            $this->assertSame($semester, $response->viewData('selectedSemester'));
            foreach ($response->viewData('krs') as $rekap) {
                $this->assertSame($semester, (int) $rekap->kurikulum?->mataKuliah?->smt);
            }
        }

        $this->get(route('mahasiswa.rekap.absensi'))
            ->assertOk()
            ->assertViewHas('selectedSemester', max(1, min(14, (int) $mahasiswa->semester)))
            ->assertSee('Berjalan');
    }
}
