<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\Rps;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RpsReuploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_reupload_replaces_the_file_and_updates_the_form_information(): void
    {
        Storage::fake('public');

        $activeTA = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignment = DosenMatakuliah::whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereIn('jenis_kelas', ['reguler', 'karyawan'])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTA->ta_id))
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $jenisKelas = strtolower($assignment->jenis_kelas);

        Rps::where('kurikulum_id', $assignment->kurikulum_id)
            ->where('jenis_kelas', $jenisKelas)
            ->delete();

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.rps.store'), [
                'kurikulum_id' => $assignment->kurikulum_id,
                'jenis_kelas' => $jenisKelas,
                'file' => UploadedFile::fake()->create('rps-pertama.pdf', 20, 'application/pdf'),
            ])
            ->assertRedirect();

        $rps = Rps::where('kurikulum_id', $assignment->kurikulum_id)
            ->where('jenis_kelas', $jenisKelas)
            ->firstOrFail();
        $oldPath = $rps->file;
        Storage::disk('public')->assertExists($oldPath);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.rps.store'), [
                'kurikulum_id' => $assignment->kurikulum_id,
                'jenis_kelas' => $jenisKelas,
                'file' => UploadedFile::fake()->create('rps-pengganti.pdf', 25, 'application/pdf'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'RPS berhasil diupload ulang dan file lama telah diganti.');

        $rps->refresh();

        $this->assertNotSame($oldPath, $rps->file);
        $this->assertSame('rps-pengganti.pdf', $rps->nama_file);
        Storage::disk('public')->assertMissing($oldPath);
        Storage::disk('public')->assertExists($rps->file);
        $this->assertSame(1, Rps::where('kurikulum_id', $assignment->kurikulum_id)
            ->where('jenis_kelas', $jenisKelas)
            ->count());

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.rps.index'))
            ->assertOk()
            ->assertSee('Upload Ulang')
            ->assertSee('File saat ini')
            ->assertSee('rps-pengganti.pdf');

        $showResponse = $this->get(route('dosen.rps.show', $rps))
            ->assertOk();

        $cacheControl = $showResponse->headers->get('Cache-Control');
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
    }
}
