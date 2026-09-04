<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\LmsTugas;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LmsTaskTypeDefaultTest extends TestCase
{
    use DatabaseTransactions;

    public function test_legacy_task_form_without_type_defaults_to_file(): void
    {
        $reference = LmsTugas::with(['jadwal', 'pertemuan'])->firstOrFail();
        $dosen = Dosen::findOrFail($reference->dosen_id);
        $title = 'Tugas form lama '.uniqid();

        $this->actingAs($dosen, 'dosen')->post(route('dosen.lms.tugas.store'), [
            'jadwal_id' => $reference->jadwal_id,
            'pertemuan_id' => $reference->pertemuan_id,
            'judul' => $title,
            'deskripsi' => 'Simulasi cache form sebelum pilihan jenis tugas tersedia.',
            'deadline' => now()->addDay()->format('Y-m-d H:i:s'),
            'nilai_maksimal' => 100,
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('lms_tugas', [
            'judul' => $title,
            'tipe' => 'file',
        ]);
    }
}
