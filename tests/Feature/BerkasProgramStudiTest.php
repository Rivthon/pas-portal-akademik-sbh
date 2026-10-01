<?php

namespace Tests\Feature;

use App\Models\BerkasProgramStudi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BerkasProgramStudiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_only_assigned_sekprodi_can_upload_and_manage_program_files(): void
    {
        Storage::fake('private');
        [$program, $sekprodi] = $this->programAndLecturer();
        $program->update(['sekprodi_dosen_id' => $sekprodi->dosen_id]);

        $this->actingAs($sekprodi, 'dosen')
            ->post(route('dosen.berkas-program-studi.store'), [
                'jurusan_id' => $program->jurusan_id,
                'judul' => 'Form Daftar Sidang',
                'deskripsi' => 'Form resmi untuk pendaftaran sidang.',
                'target' => 'mahasiswa',
                'berkas' => UploadedFile::fake()->create('form-sidang.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $berkas = BerkasProgramStudi::where('judul', 'Form Daftar Sidang')->firstOrFail();
        Storage::disk('private')->assertExists($berkas->path);

        $otherLecturer = Dosen::query()->whereKeyNot($sekprodi->dosen_id)->firstOrFail();
        $this->actingAs($otherLecturer, 'dosen')
            ->post(route('dosen.berkas-program-studi.store'), [
                'jurusan_id' => $program->jurusan_id,
                'judul' => 'Tidak Sah',
                'target' => 'semua',
                'berkas' => UploadedFile::fake()->create('tidak-sah.pdf', 20, 'application/pdf'),
            ])
            ->assertForbidden();
    }

    public function test_student_only_sees_and_downloads_active_files_for_their_program(): void
    {
        Storage::fake('private');
        [$program, $sekprodi] = $this->programAndLecturer();
        $program->update(['sekprodi_dosen_id' => $sekprodi->dosen_id]);
        $student = Mahasiswa::query()->where('jurusan_id', $program->jurusan_id)->firstOrFail();
        $otherStudent = Mahasiswa::query()->where('jurusan_id', '!=', $program->jurusan_id)->firstOrFail();
        Storage::disk('private')->put('berkas-program-studi/form-sidang.pdf', '%PDF-1.4 test');
        $berkas = BerkasProgramStudi::create([
            'jurusan_id' => $program->jurusan_id,
            'uploaded_by_dosen_id' => $sekprodi->dosen_id,
            'judul' => 'Form Daftar Sidang Prodi',
            'target' => 'mahasiswa',
            'nama_file' => 'form-sidang.pdf',
            'path' => 'berkas-program-studi/form-sidang.pdf',
            'mime_type' => 'application/pdf',
            'ukuran' => 20,
            'aktif' => true,
        ]);

        $this->actingAs($student, 'mahasiswa')
            ->get(route('mahasiswa.berkas-program-studi.index'))
            ->assertOk()
            ->assertSee('Form Daftar Sidang Prodi');
        $this->get(route('mahasiswa.berkas-program-studi.download', $berkas))->assertOk();

        $this->actingAs($otherStudent, 'mahasiswa')
            ->get(route('mahasiswa.berkas-program-studi.index'))
            ->assertOk()
            ->assertDontSee('Form Daftar Sidang Prodi');
        $this->get(route('mahasiswa.berkas-program-studi.download', $berkas))->assertForbidden();
    }

    public function test_admin_program_form_contains_sekprodi_selection(): void
    {
        $admin = User::query()->firstOrFail();
        $program = ProgramStudi::query()->firstOrFail();

        $this->actingAs($admin)
            ->get(route('admin.program-studi.edit', $program))
            ->assertOk()
            ->assertSee('sekprodi_dosen_id');
    }

    private function programAndLecturer(): array
    {
        $program = ProgramStudi::query()
            ->whereHas('mahasiswa')
            ->firstOrFail();
        $lecturer = Dosen::query()->firstOrFail();

        return [$program, $lecturer];
    }
}
