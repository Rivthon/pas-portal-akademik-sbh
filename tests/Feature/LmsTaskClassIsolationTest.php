<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LmsTaskClassIsolationTest extends TestCase
{
    public function test_employee_task_only_lists_employee_students(): void
    {
        $tugas = LmsTugas::with('jadwal')->findOrFail(4);
        $this->assertSame('karyawan', strtolower((string) $tugas->jadwal->jenis_kelas));

        $karyawan = Krs::with('mahasiswa')
            ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
            ->where('ta_id', $tugas->jadwal->ta_id)
            ->whereHas('mahasiswa', fn ($query) => $query
                ->whereRaw('LOWER(kelas) = ?', ['karyawan']))
            ->firstOrFail()
            ->mahasiswa;

        $reguler = Krs::with('mahasiswa')
            ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
            ->where('ta_id', $tugas->jadwal->ta_id)
            ->whereHas('mahasiswa', fn ($query) => $query
                ->whereRaw('LOWER(kelas) != ?', ['karyawan']))
            ->firstOrFail()
            ->mahasiswa;

        $this->actingAs(Dosen::findOrFail(22), 'dosen')
            ->get(route('dosen.lms.tugas.pengumpulan', $tugas))
            ->assertOk()
            ->assertSee($karyawan->nim)
            ->assertDontSee($reguler->nim);
    }

    public function test_regular_student_cannot_open_or_submit_an_employee_task(): void
    {
        $tugas = LmsTugas::with('jadwal')->findOrFail(4);
        $reguler = Krs::with('mahasiswa')
            ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
            ->where('ta_id', $tugas->jadwal->ta_id)
            ->whereHas('mahasiswa', fn ($query) => $query
                ->whereRaw('LOWER(kelas) != ?', ['karyawan']))
            ->firstOrFail()
            ->mahasiswa;

        Auth::guard('dosen')->logout();
        $this->actingAs($reguler, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $tugas))
            ->assertForbidden();

        $this->post(route('mahasiswa.lms.tugas.kumpulkan', $tugas), [])
            ->assertForbidden();
    }

    public function test_dosen_can_preview_a_submission_before_downloading_it(): void
    {
        $pengumpulan = LmsPengumpulanTugas::with('tugas')
            ->whereNotNull('file')
            ->get()
            ->first(fn ($item) => Storage::disk('public')->exists($item->file));

        $this->assertNotNull($pengumpulan);

        $dosen = Dosen::findOrFail($pengumpulan->tugas->dosen_id);
        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.lms.pengumpulan.preview', $pengumpulan))
            ->assertOk()
            ->assertHeader(
                'content-disposition',
                'inline; filename="'.basename($pengumpulan->file).'"'
            );
    }
}
