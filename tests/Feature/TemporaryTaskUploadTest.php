<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugas;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TemporaryTaskUploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_stage_then_submit_a_task_file(): void
    {
        [$tugas, $mahasiswa] = $this->eligibleTaskAndStudent();

        Storage::fake('private');
        $tugas->update([
            'aktif' => true,
            'deadline' => now()->addDay(),
            'izinkan_upload_ulang' => true,
        ]);
        LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->delete();

        $upload = $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.lms.tugas.upload-sementara', $tugas), [
                'file' => UploadedFile::fake()->create('jawaban.pdf', 100, 'application/pdf'),
            ]);

        $upload->assertOk()
            ->assertJsonStructure(['token', 'name', 'size', 'expires_at', 'message']);

        $token = $upload->json('token');
        $temporary = session('lms_temporary_task_uploads.'.$token);
        $this->assertIsArray($temporary);
        Storage::disk('private')->assertExists($temporary['path']);

        $submit = $this->post(route('mahasiswa.lms.tugas.kumpulkan', $tugas), [
            'temporary_upload_token' => $token,
            'catatan' => 'Uji upload sementara.',
        ]);

        $submit->assertRedirect(route('mahasiswa.lms.tugas.show', $tugas));

        $pengumpulan = LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->firstOrFail();

        Storage::disk('private')->assertExists($pengumpulan->file);
        Storage::disk('private')->assertMissing($temporary['path']);
        $this->assertNull(session('lms_temporary_task_uploads.'.$token));
    }

    public function test_temporary_task_upload_rejects_files_larger_than_ten_megabytes(): void
    {
        [$tugas, $mahasiswa] = $this->eligibleTaskAndStudent();

        Storage::fake('private');
        $tugas->update([
            'aktif' => true,
            'deadline' => now()->addDay(),
            'izinkan_upload_ulang' => true,
        ]);
        LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->delete();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->postJson(route('mahasiswa.lms.tugas.upload-sementara', $tugas), [
                'file' => UploadedFile::fake()->create('terlalu-besar.pdf', 10241, 'application/pdf'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file');

        $this->assertSame([], Storage::disk('private')->allFiles('lms/tmp-pengumpulan'));
    }

    private function eligibleTaskAndStudent(): array
    {
        $tasks = LmsTugas::with('jadwal')
            ->where('tipe', '!=', 'pilihan_ganda')
            ->get();

        foreach ($tasks as $tugas) {
            if (! $tugas->jadwal) {
                continue;
            }

            $krs = Krs::with('mahasiswa')
                ->where('kurikulum_id', $tugas->jadwal->kurikulum_id)
                ->where('ta_id', $tugas->jadwal->ta_id)
                ->get()
                ->first(fn (Krs $item) => $item->mahasiswa
                    && KrsClassResolver::matches($item, $tugas->jadwal, $item->mahasiswa));

            if ($krs?->mahasiswa) {
                return [$tugas, $krs->mahasiswa];
            }
        }

        $this->fail('Tidak ditemukan pasangan tugas dan mahasiswa yang valid untuk pengujian.');
    }
}
