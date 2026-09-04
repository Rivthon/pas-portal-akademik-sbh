<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\LmsMateri;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LmsMateriUploadTest extends TestCase
{
    use DatabaseTransactions;

    public function test_assigned_dosen_can_upload_pdf_material(): void
    {
        Storage::fake('public');

        $jadwal = Jadwal::query()
            ->whereHas('pertemuan')
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) {
                $query->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)');
            })
            ->firstOrFail();
        $assignment = $jadwal->kurikulum->dosenToMatakuliah()
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($jadwal->jenis_kelas)])
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $pertemuan = $jadwal->pertemuan()->firstOrFail();

        $response = $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.lms.materi.store'), [
                'pertemuan_id' => $pertemuan->pertemuan_id,
                'jadwal_id' => $jadwal->id,
                'judul' => 'Materi PDF Pengujian',
                'deskripsi' => 'Materi pengujian upload.',
                'file' => UploadedFile::fake()->create(
                    'Materi: Pengujian PDF.pdf',
                    100,
                    'application/pdf'
                ),
            ]);

        $response->assertRedirect(route('dosen.lms.kelola', $jadwal->id))
            ->assertSessionHas('success');

        $materi = LmsMateri::query()
            ->where('judul', 'Materi PDF Pengujian')
            ->firstOrFail();

        $this->assertSame('pdf', $materi->tipe);
        $this->assertStringNotContainsString(':', $materi->file);
        Storage::disk('public')->assertExists($materi->file);
    }

    public function test_material_requires_a_file_or_valid_link(): void
    {
        $jadwal = Jadwal::query()
            ->whereHas('pertemuan')
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) {
                $query->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
                    ->whereRaw('LOWER(dosen_mata_kuliah.jenis_kelas) = LOWER(jadwal.jenis_kelas)');
            })
            ->firstOrFail();
        $assignment = $jadwal->kurikulum->dosenToMatakuliah()
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereRaw('LOWER(jenis_kelas) = ?', [strtolower($jadwal->jenis_kelas)])
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);

        $this->actingAs($dosen, 'dosen')
            ->from(route('dosen.lms.kelola', $jadwal->id))
            ->post(route('dosen.lms.materi.store'), [
                'pertemuan_id' => $jadwal->pertemuan()->value('pertemuan_id'),
                'jadwal_id' => $jadwal->id,
                'judul' => 'Materi Kosong',
            ])
            ->assertRedirect(route('dosen.lms.kelola', $jadwal->id))
            ->assertSessionHasErrors(['file', 'youtube_url']);
    }
}
