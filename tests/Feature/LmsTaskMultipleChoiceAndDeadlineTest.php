<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugasSoal;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LmsTaskMultipleChoiceAndDeadlineTest extends TestCase
{
    use DatabaseTransactions;

    public function test_existing_submission_cannot_be_changed_after_deadline_even_when_late_and_reupload_are_allowed(): void
    {
        $submission = LmsPengumpulanTugas::with(['tugas', 'mahasiswa'])->whereNotNull('file')->firstOrFail();
        $submission->update(['nilai' => null, 'dinilai_pada' => null, 'dinilai_otomatis' => false]);
        $submission->tugas->update([
            'tipe' => 'file',
            'aktif' => true,
            'deadline' => now()->subMinute(),
            'izinkan_terlambat' => true,
            'izinkan_upload_ulang' => true,
        ]);
        $oldFile = $submission->file;
        $oldUploadTime = DB::table('lms_pengumpulan_tugas')
            ->where('pengumpulan_id', $submission->pengumpulan_id)
            ->value('waktu_upload');

        $this->actingAs($submission->mahasiswa, 'mahasiswa')
            ->post(route('mahasiswa.lms.tugas.kumpulkan', $submission->tugas), [
                'file' => UploadedFile::fake()->create('pengganti.pdf', 20, 'application/pdf'),
            ])
            ->assertSessionHas('error', 'Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.');

        $submission->refresh();
        $this->assertSame($oldFile, $submission->file);
        $this->assertSame($oldUploadTime, DB::table('lms_pengumpulan_tugas')
            ->where('pengumpulan_id', $submission->pengumpulan_id)
            ->value('waktu_upload'));
    }

    public function test_multiple_choice_task_uses_a_to_e_and_is_scored_automatically(): void
    {
        $seed = LmsPengumpulanTugas::with(['tugas', 'mahasiswa'])->firstOrFail();
        $task = $seed->tugas;
        $student = $seed->mahasiswa;
        $task->pengumpulan()->delete();
        $task->soal()->delete();
        $task->update([
            'tipe' => 'pilihan_ganda',
            'aktif' => true,
            'deadline' => now()->addHour(),
            'nilai_maksimal' => 100,
            'izinkan_upload_ulang' => true,
        ]);
        $first = LmsTugasSoal::create([
            'tugas_id' => $task->tugas_id, 'pertanyaan' => 'Soal pertama',
            'opsi' => ['A1', 'B1', 'C1', 'D1', 'E1'], 'kunci_jawaban' => 4, 'bobot' => 1, 'urutan' => 1,
        ]);
        $second = LmsTugasSoal::create([
            'tugas_id' => $task->tugas_id, 'pertanyaan' => 'Soal kedua',
            'opsi' => ['A2', 'B2', 'C2', 'D2', 'E2'], 'kunci_jawaban' => 0, 'bobot' => 1, 'urutan' => 2,
        ]);

        $dosen = Dosen::findOrFail($task->dosen_id);
        $this->actingAs($dosen, 'dosen')->get(route('dosen.lms.tugas.soal.manage', $task))
            ->assertOk()
            ->assertSee('TUGAS PILIHAN GANDA A-E')
            ->assertSee('E1');

        $this->actingAs($student, 'mahasiswa')->post(route('mahasiswa.lms.tugas.kumpulkan', $task), [
            'jawaban_pg' => [$first->soal_id => 4, $second->soal_id => 1],
        ])->assertSessionHas('success');

        $submission = LmsPengumpulanTugas::where('tugas_id', $task->tugas_id)
            ->where('mahasiswa_id', $student->mahasiswa_id)->firstOrFail();
        $this->assertNull($submission->file);
        $this->assertTrue($submission->dinilai_otomatis);
        $this->assertSame(50.0, (float) $submission->nilai);
        $this->assertSame(4, (int) $submission->jawaban_pg[(string) $first->soal_id]);

        $task->update(['deadline' => now()->subMinute(), 'izinkan_terlambat' => true]);
        $this->post(route('mahasiswa.lms.tugas.kumpulkan', $task), [
            'jawaban_pg' => [$first->soal_id => 0, $second->soal_id => 0],
        ])->assertSessionHas('error', 'Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.');

        $this->assertSame(50.0, (float) $submission->fresh()->nilai);
    }
}
