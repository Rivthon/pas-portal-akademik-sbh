<?php

namespace Tests\Feature;

use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugas;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TextTaskSubmissionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_student_can_submit_text_answer_and_it_locks_after_grading(): void
    {
        [$tugas, $mahasiswa] = $this->eligibleTaskAndStudent();
        Storage::fake('public');
        $lampiran = 'lms/tugas/panduan-jawaban-teks.pdf';
        Storage::disk('public')->put($lampiran, '%PDF-1.4 lampiran pengujian');

        $tugas->update([
            'tipe' => 'teks',
            'deskripsi' => 'Instruksi khusus untuk tugas jawaban teks.',
            'lampiran' => $lampiran,
            'aktif' => true,
            'deadline' => now()->addDay(),
            'izinkan_terlambat' => false,
            'izinkan_upload_ulang' => true,
        ]);
        LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->delete();

        $answer = 'Ini adalah jawaban yang ditulis langsung melalui LMS.';

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->post(route('mahasiswa.lms.tugas.kumpulkan', $tugas), [
                'jawaban_teks' => $answer,
                'catatan' => 'Mohon diperiksa.',
            ])
            ->assertRedirect(route('mahasiswa.lms.tugas.show', $tugas))
            ->assertSessionHas('success', 'Jawaban teks berhasil dikumpulkan.');

        $pengumpulan = LmsPengumpulanTugas::where('tugas_id', $tugas->tugas_id)
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->firstOrFail();

        $this->assertSame($answer, $pengumpulan->jawaban_teks);
        $this->assertNull($pengumpulan->file);
        $this->assertNull($pengumpulan->jawaban_pg);

        $this->get(route('mahasiswa.lms.tugas.show', $tugas))
            ->assertOk()
            ->assertSee($answer)
            ->assertSee('Instruksi khusus untuk tugas jawaban teks.')
            ->assertSee('Lampiran Berkas dari Dosen')
            ->assertSee('panduan-jawaban-teks.pdf')
            ->assertSee(route('mahasiswa.lms.tugas.lampiran', $tugas), false);

        $this->get(route('mahasiswa.lms.tugas.lampiran', $tugas))
            ->assertOk()
            ->assertHeader('content-disposition', 'inline; filename=panduan-jawaban-teks.pdf');

        $pengumpulan->update([
            'nilai' => 85,
            'dinilai_pada' => now(),
            'dinilai_otomatis' => false,
        ]);

        $this->post(route('mahasiswa.lms.tugas.kumpulkan', $tugas), [
            'jawaban_teks' => 'Jawaban yang seharusnya ditolak.',
        ])->assertSessionHas(
            'error',
            'Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.'
        );

        $this->assertSame($answer, $pengumpulan->fresh()->jawaban_teks);
    }

    private function eligibleTaskAndStudent(): array
    {
        foreach (LmsTugas::with('jadwal')->get() as $tugas) {
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
