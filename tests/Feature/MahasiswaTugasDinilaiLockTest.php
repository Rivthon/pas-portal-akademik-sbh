<?php

namespace Tests\Feature;

use App\Models\LmsPengumpulanTugas;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MahasiswaTugasDinilaiLockTest extends TestCase
{
    use DatabaseTransactions;

    public function test_graded_submission_cannot_be_reuploaded_even_when_reupload_is_allowed(): void
    {
        $pengumpulan = LmsPengumpulanTugas::with(['tugas', 'mahasiswa'])->findOrFail(1);
        $pengumpulan->update([
            'nilai' => 0,
            'dinilai_pada' => null,
        ]);
        $pengumpulan->tugas->update([
            'aktif' => true,
            'deadline' => now()->addDay(),
            'izinkan_upload_ulang' => true,
        ]);

        $page = $this->actingAs($pengumpulan->mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $pengumpulan->tugas));

        $page->assertOk()
            ->assertSee('Jawaban sudah dikunci')
            ->assertSee('Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.')
            ->assertDontSee('Ganti Berkas Jawaban');

        $fileSebelumnya = $pengumpulan->file;
        $waktuSebelumnya = DB::table('lms_pengumpulan_tugas')
            ->where('pengumpulan_id', $pengumpulan->pengumpulan_id)
            ->value('waktu_upload');

        $submit = $this->post(route('mahasiswa.lms.tugas.kumpulkan', $pengumpulan->tugas), [
            'file' => UploadedFile::fake()->create('jawaban-baru.pdf', 100, 'application/pdf'),
            'catatan' => 'Percobaan mengganti jawaban yang sudah dinilai.',
        ]);

        $submit->assertSessionHas(
            'error',
            'Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.'
        );

        $pengumpulan->refresh();
        $this->assertSame($fileSebelumnya, $pengumpulan->file);
        $this->assertSame(
            $waktuSebelumnya,
            DB::table('lms_pengumpulan_tugas')
                ->where('pengumpulan_id', $pengumpulan->pengumpulan_id)
                ->value('waktu_upload')
        );
        $this->assertSame(0.0, (float) $pengumpulan->nilai);
    }
}
