<?php

namespace Tests\Feature;

use App\Models\Jadwal;
use App\Models\Krs;
use App\Models\LmsPengumpulanTugas;
use App\Models\LmsTugas;
use App\Models\TahunAkademik;
use App\Support\KrsClassResolver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LmsIndividualTaskTest extends TestCase
{
    use DatabaseTransactions;

    public function test_individual_task_is_only_visible_and_accessible_by_selected_students(): void
    {
        Storage::fake('private');
        [$jadwal, $selected, $notSelected] = $this->scheduleAndStudents();
        $pertemuan = $jadwal->pertemuan()->firstOrFail();

        $task = LmsTugas::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'dosen_id' => $pertemuan->dosen_id,
            'judul' => 'Tugas Individu Pengujian',
            'deskripsi' => 'Hanya untuk mahasiswa terpilih.',
            'tipe' => 'teks',
            'cakupan' => 'individu',
            'deadline' => now()->addDay(),
            'nilai_maksimal' => 100,
            'aktif' => true,
            'izinkan_upload_ulang' => false,
        ]);
        $task->targetMahasiswa()->attach($selected->mahasiswa_id);

        $this->assertTrue(LmsTugas::visibleForMahasiswa($selected->mahasiswa_id)->whereKey($task->tugas_id)->exists());
        $this->assertFalse(LmsTugas::visibleForMahasiswa($notSelected->mahasiswa_id)->whereKey($task->tugas_id)->exists());

        $this->actingAs($selected, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $task))
            ->assertOk()
            ->assertSee('Tugas Individu Pengujian');

        auth('mahasiswa')->logout();
        $this->actingAs($notSelected, 'mahasiswa')
            ->get(route('mahasiswa.lms.tugas.show', $task))
            ->assertForbidden();

        Sanctum::actingAs($selected, ['mahasiswa']);
        $this->getJson('/api/v1/mahasiswa/lms/'.$jadwal->id)
            ->assertOk()
            ->assertJsonFragment([
                'judul' => 'Tugas Individu Pengujian',
                'cakupan' => 'individu',
                'tugas_individu' => true,
            ]);

        $this->getJson('/api/v1/mahasiswa/lms/tugas/'.$task->tugas_id)
            ->assertOk()
            ->assertJsonPath('tugas.id', $task->tugas_id)
            ->assertJsonPath('aturan.boleh_mengumpulkan', true);

        $this->postJson('/api/v1/mahasiswa/lms/tugas/'.$task->tugas_id.'/kumpulkan', [
            'jawaban_teks' => 'Jawaban dari aplikasi Android.',
            'catatan' => 'Dikirim melalui PAS Mobile.',
        ])->assertOk();

        $this->assertDatabaseHas('lms_pengumpulan_tugas', [
            'tugas_id' => $task->tugas_id,
            'mahasiswa_id' => $selected->mahasiswa_id,
            'jawaban_teks' => 'Jawaban dari aplikasi Android.',
        ]);

        $this->postJson('/api/v1/mahasiswa/lms/tugas/'.$task->tugas_id.'/kumpulkan', [
            'jawaban_teks' => 'Tidak boleh mengganti tanpa izin.',
        ])->assertUnprocessable();

        $multipleChoiceTask = LmsTugas::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'dosen_id' => $pertemuan->dosen_id,
            'judul' => 'Tugas PG Mobile',
            'deskripsi' => 'Uji penilaian otomatis.',
            'tipe' => 'pilihan_ganda',
            'cakupan' => 'semua',
            'deadline' => now()->addDay(),
            'nilai_maksimal' => 100,
            'aktif' => true,
        ]);
        $question = $multipleChoiceTask->soal()->create([
            'pertanyaan' => 'Jawaban yang benar adalah B.',
            'opsi' => ['Pilihan A', 'Pilihan B', 'Pilihan C', 'Pilihan D', 'Pilihan E'],
            'kunci_jawaban' => 1,
            'bobot' => 10,
            'urutan' => 1,
        ]);

        $this->getJson('/api/v1/mahasiswa/lms/tugas/'.$multipleChoiceTask->tugas_id)
            ->assertOk()
            ->assertJsonMissingPath('soal.0.kunci_jawaban');
        $this->postJson('/api/v1/mahasiswa/lms/tugas/'.$multipleChoiceTask->tugas_id.'/kumpulkan', [
            'jawaban_pg' => [(string) $question->soal_id => 1],
        ])->assertOk();
        $this->assertDatabaseHas('lms_pengumpulan_tugas', [
            'tugas_id' => $multipleChoiceTask->tugas_id,
            'mahasiswa_id' => $selected->mahasiswa_id,
            'nilai' => 100,
            'dinilai_otomatis' => true,
        ]);

        $fileTask = LmsTugas::create([
            'jadwal_id' => $jadwal->id,
            'pertemuan_id' => $pertemuan->pertemuan_id,
            'dosen_id' => $pertemuan->dosen_id,
            'judul' => 'Tugas File Mobile',
            'deskripsi' => 'Uji upload privat.',
            'tipe' => 'file',
            'cakupan' => 'semua',
            'deadline' => now()->addDay(),
            'nilai_maksimal' => 100,
            'aktif' => true,
        ]);
        $this->post('/api/v1/mahasiswa/lms/tugas/'.$fileTask->tugas_id.'/kumpulkan', [
            'file' => UploadedFile::fake()->create('jawaban-mobile.pdf', 128, 'application/pdf'),
            'catatan' => 'Berkas dari Android.',
        ], ['Accept' => 'application/json'])->assertOk();
        $fileSubmission = LmsPengumpulanTugas::where('tugas_id', $fileTask->tugas_id)
            ->where('mahasiswa_id', $selected->mahasiswa_id)
            ->firstOrFail();
        Storage::disk('private')->assertExists($fileSubmission->file);
        $this->get('/api/v1/mahasiswa/lms/pengumpulan/'.$fileSubmission->pengumpulan_id.'/file', [
            'Accept' => 'application/json',
        ])->assertOk();

        Sanctum::actingAs($notSelected, ['mahasiswa']);
        $this->getJson('/api/v1/mahasiswa/lms/'.$jadwal->id)
            ->assertOk()
            ->assertJsonMissing(['judul' => 'Tugas Individu Pengujian']);

        $this->getJson('/api/v1/mahasiswa/lms/tugas/'.$task->tugas_id)
            ->assertForbidden();
        $this->postJson('/api/v1/mahasiswa/lms/tugas/'.$task->tugas_id.'/kumpulkan', [
            'jawaban_teks' => 'Mencoba mengakses tugas mahasiswa lain.',
        ])->assertForbidden();

        $this->assertSame(
            1,
            LmsPengumpulanTugas::where('tugas_id', $task->tugas_id)->count()
        );
    }

    private function scheduleAndStudents(): array
    {
        $activeTaId = TahunAkademik::where('status_ta', 1)->value('ta_id');

        foreach (Jadwal::with(['pertemuan', 'kurikulum'])->where('ta_id', $activeTaId)->get() as $jadwal) {
            if ($jadwal->pertemuan->isEmpty()) {
                continue;
            }

            $students = Krs::with('mahasiswa')
                ->where('kurikulum_id', $jadwal->kurikulum_id)
                ->where('ta_id', $jadwal->ta_id)
                ->whereNotNull('disetujui_pada')
                ->get()
                ->filter(fn (Krs $krs) => $krs->mahasiswa
                    && KrsClassResolver::matches($krs, $jadwal, $krs->mahasiswa))
                ->pluck('mahasiswa')
                ->unique('mahasiswa_id')
                ->values();

            if ($students->count() >= 2) {
                return [$jadwal, $students[0], $students[1]];
            }
        }

        $this->markTestSkipped('Tidak ada jadwal dengan minimal dua peserta KRS disetujui.');
    }
}
