<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\PedomanAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PedomanAkademikTest extends TestCase
{
    use DatabaseTransactions;

    public function test_baak_can_upload_pdf_and_activate_it(): void
    {
        Storage::fake('private');
        $admin = User::factory()->create();
        $admin->givePermissionTo(Permission::findByName('pedoman-akademik-create'));

        $this->actingAs($admin)->post(route('admin.pedoman-akademik.store'), [
            'judul' => 'Pedoman Akademik 2026',
            'tahun_berlaku' => '2026/2027',
            'status' => 1,
            'file' => UploadedFile::fake()->create('pedoman-2026.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $pedoman = PedomanAkademik::where('judul', 'Pedoman Akademik 2026')->firstOrFail();
        $this->assertTrue($pedoman->status);
        $this->assertSame('pedoman-2026.pdf', $pedoman->nama_file);
        Storage::disk('private')->assertExists($pedoman->path);
    }

    public function test_admin_without_permission_cannot_upload_pdf(): void
    {
        Storage::fake('private');
        $admin = User::factory()->create();

        $this->actingAs($admin)->post(route('admin.pedoman-akademik.store'), [
            'judul' => 'Tidak Diizinkan',
            'status' => 1,
            'file' => UploadedFile::fake()->create('pedoman.pdf', 100, 'application/pdf'),
        ])->assertForbidden();

        $this->assertDatabaseMissing('pedoman_akademik', ['judul' => 'Tidak Diizinkan']);
    }

    public function test_student_can_only_see_preview_and_download_active_guideline(): void
    {
        Storage::fake('private');
        $mahasiswa = Mahasiswa::firstOrFail();
        $aktif = $this->makePedoman('Pedoman Aktif', true, 'aktif.pdf');
        $arsip = $this->makePedoman('Pedoman Arsip', false, 'arsip.pdf');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.pedoman-akademik.index'))
            ->assertOk()
            ->assertSee('Pedoman Aktif')
            ->assertDontSee('Pedoman Arsip');

        $this->get(route('mahasiswa.pedoman-akademik.preview', $aktif))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->get(route('mahasiswa.pedoman-akademik.download', $aktif))
            ->assertOk()
            ->assertDownload('aktif.pdf');

        $this->get(route('mahasiswa.pedoman-akademik.preview', $arsip))->assertNotFound();
        $this->get(route('mahasiswa.pedoman-akademik.download', $arsip))->assertNotFound();
    }

    private function makePedoman(string $judul, bool $status, string $filename): PedomanAkademik
    {
        $path = 'pedoman-akademik/'.$filename;
        Storage::disk('private')->put($path, '%PDF-1.4 test');

        return PedomanAkademik::create([
            'judul' => $judul,
            'tahun_berlaku' => '2026/2027',
            'nama_file' => $filename,
            'path' => $path,
            'status' => $status,
        ]);
    }
}
