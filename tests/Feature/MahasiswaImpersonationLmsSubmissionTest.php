<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\User;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MahasiswaImpersonationLmsSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_impersonated_student_sees_their_existing_task_submission(): void
    {
        [$tugas, $mahasiswa] = $this->eligibleSubmittedTaskAndStudent();

        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findByName('mahasiswa-impersonate', 'web'));

        $this->actingAs($admin, 'web')
            ->post(route('admin.mahasiswa.impersonate', $mahasiswa))
            ->assertRedirect(route('mahasiswa.dashboard'));

        $this->assertSame(
            (string) $mahasiswa->mahasiswa_id,
            (string) Auth::guard('mahasiswa')->id()
        );
        $this->assertDatabaseHas('lms_pengumpulan_tugas', [
            'tugas_id' => $tugas->tugas_id,
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
        ]);

        $response = $this->get(route('mahasiswa.lms.tugas.show', $tugas));

        $this->assertSame(
            (string) $mahasiswa->mahasiswa_id,
            (string) Auth::guard('mahasiswa')->id()
        );
        $response->assertOk()
            ->assertSee($mahasiswa->nama)
            ->assertSee('Sudah Mengumpulkan Jawaban')
            ->assertDontSee('Anda belum mengumpulkan jawaban untuk tugas ini.');
    }

    private function eligibleSubmittedTaskAndStudent(): array
    {
        $submissions = LmsPengumpulanTugas::with(['tugas.jadwal', 'mahasiswa'])
            ->latest('pengumpulan_id')
            ->get();

        foreach ($submissions as $submission) {
            $tugas = $submission->tugas;
            $mahasiswa = $submission->mahasiswa;

            if (! $tugas?->aktif || ! $tugas->jadwal || ! $mahasiswa || $mahasiswa->status === 'cuti') {
                continue;
            }

            $krs = Krs::query()
                ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
                ->where('ta_id', $tugas->jadwal->ta_id)
                ->get()
                ->first(fn (Krs $item) => KrsClassResolver::matches($item, $tugas->jadwal, $mahasiswa));

            if ($krs) {
                return [$tugas, $mahasiswa];
            }
        }

        $this->fail('Tidak ditemukan pengumpulan tugas dengan akses LMS mahasiswa yang valid.');
    }
}
