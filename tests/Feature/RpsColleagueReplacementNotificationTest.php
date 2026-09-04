<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\Rps;
use App\Models\RpsRevision;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RpsColleagueReplacementNotificationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_colleague_is_notified_when_another_lecturer_replaces_rps(): void
    {
        Storage::fake('public');

        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignments = DosenMatakuliah::query()
            ->whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTa->ta_id))
            ->get()
            ->groupBy(fn ($item) => $item->kurikulum_id.'|'.strtolower($item->jenis_kelas))
            ->first(fn ($items) => $items->pluck('dosen_id')->unique()->count() >= 2);

        $this->assertNotNull($assignments, 'Tidak ada kelas dengan dua dosen teori untuk pengujian.');
        $firstAssignment = $assignments->first();
        $secondAssignment = $assignments->first(fn ($item) => (int) $item->dosen_id !== (int) $firstAssignment->dosen_id);
        $firstDosen = Dosen::findOrFail($firstAssignment->dosen_id);
        $secondDosen = Dosen::findOrFail($secondAssignment->dosen_id);
        $jenisKelas = strtolower($firstAssignment->jenis_kelas);

        Rps::where('kurikulum_id', $firstAssignment->kurikulum_id)
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas])
            ->delete();

        $payload = fn (string $name) => [
            'kurikulum_id' => $firstAssignment->kurikulum_id,
            'jenis_kelas' => $jenisKelas,
            'file' => UploadedFile::fake()->create($name, 25, 'application/pdf'),
        ];

        $this->actingAs($firstDosen, 'dosen')->post(route('dosen.rps.store'), $payload('rps-awal.pdf'))
            ->assertRedirect();
        $this->actingAs($secondDosen, 'dosen')->post(route('dosen.rps.store'), $payload('rps-pengganti.pdf'))
            ->assertRedirect()
            ->assertSessionHas('success', 'RPS berhasil diganti. Rekan dosen pengampu akan mendapatkan pemberitahuan.');

        $revision = RpsRevision::latest()->firstOrFail();
        $this->assertSame((int) $firstDosen->dosen_id, (int) $revision->previous_dosen_id);
        $this->assertSame((int) $secondDosen->dosen_id, (int) $revision->uploaded_by_dosen_id);

        $this->actingAs($firstDosen, 'dosen')->get(route('dosen.dashboard'))
            ->assertOk()
            ->assertSee('Pembaruan RPS oleh Rekan Dosen')
            ->assertSee($secondDosen->nama);
    }

    public function test_same_lecturer_reupload_does_not_create_colleague_notification(): void
    {
        Storage::fake('public');
        $activeTa = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignment = DosenMatakuliah::whereRaw('LOWER(jenis_dosen) = ?', ['teori'])
            ->whereHas('kurikulum', fn ($query) => $query->where('ta_id', $activeTa->ta_id))
            ->firstOrFail();
        $dosen = Dosen::findOrFail($assignment->dosen_id);
        $jenisKelas = strtolower($assignment->jenis_kelas);

        Rps::where('kurikulum_id', $assignment->kurikulum_id)
            ->whereRaw('LOWER(jenis_kelas) = ?', [$jenisKelas])
            ->delete();

        foreach (['awal.pdf', 'ulang.pdf'] as $name) {
            $this->actingAs($dosen, 'dosen')->post(route('dosen.rps.store'), [
                'kurikulum_id' => $assignment->kurikulum_id,
                'jenis_kelas' => $jenisKelas,
                'file' => UploadedFile::fake()->create($name, 25, 'application/pdf'),
            ])->assertRedirect();
        }

        $this->assertFalse(RpsRevision::where('kurikulum_id', $assignment->kurikulum_id)
            ->where('jenis_kelas', $jenisKelas)
            ->exists());
    }
}
