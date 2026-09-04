<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\DosenMatakuliah;
use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DosenEdomVisibilityTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_only_sees_anonymous_comments_from_active_academic_year(): void
    {
        $tahunAktif = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $assignment = DosenMatakuliah::whereHas('kurikulum', fn ($query) => $query->where('ta_id', $tahunAktif->ta_id))
            ->whereHas('kurikulum.krs')
            ->with('dosen')
            ->firstOrFail();
        $krsAktif = Krs::with(['mahasiswa', 'kurikulum.mataKuliah'])
            ->where('ta_id', $tahunAktif->ta_id)
            ->where('kurikulum_id', $assignment->kurikulum_id)
            ->firstOrFail();
        $evaluasiId = DB::table('evaluasi')->value('eval_id');
        $komentarAktif = 'Komentar anonim tahun aktif '.uniqid();

        DB::table('penilaian')->updateOrInsert([
            'mahasiswa_id' => $krsAktif->mahasiswa_id,
            'dosen_id' => $assignment->dosen_id,
            'kurikulum_id' => $assignment->kurikulum_id,
            'evaluasi_id' => $evaluasiId,
        ], [
            'krs_id' => $krsAktif->krs_id,
            'nilai' => 5,
            'jenis_dosen' => $assignment->jenis_dosen,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('saran')->updateOrInsert([
            'mahasiswa_id' => $krsAktif->mahasiswa_id,
            'dosen_id' => $assignment->dosen_id,
            'kurikulum_id' => $assignment->kurikulum_id,
        ], [
            'krs_id' => $krsAktif->krs_id,
            'saran' => $komentarAktif,
            'jenis_dosen' => $assignment->jenis_dosen,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $komentarLama = 'Komentar tahun lama '.uniqid();
        $krsLama = Krs::whereHas('kurikulum', fn ($query) => $query->where('ta_id', '!=', $tahunAktif->ta_id))->first();
        if ($krsLama) {
            DB::table('saran')->updateOrInsert([
                'mahasiswa_id' => $krsLama->mahasiswa_id,
                'dosen_id' => $assignment->dosen_id,
                'kurikulum_id' => $krsLama->kurikulum_id,
            ], [
                'krs_id' => $krsLama->krs_id,
                'saran' => $komentarLama,
                'jenis_dosen' => $assignment->jenis_dosen,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs(Dosen::findOrFail($assignment->dosen_id), 'dosen')
            ->get(route('dosen.edom.hasil'));

        $response->assertOk()
            ->assertSee($tahunAktif->nama)
            ->assertSee($komentarAktif)
            ->assertSee('Identitas mahasiswa tidak ditampilkan')
            ->assertDontSee($krsAktif->mahasiswa->nim)
            ->assertDontSee($krsAktif->mahasiswa->nama);

        if ($krsLama) {
            $response->assertDontSee($komentarLama);
        }
    }
}
