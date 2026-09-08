<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Krs;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DosenKrsApprovalTest extends TestCase
{
    use DatabaseTransactions;

    public function test_dosen_can_approve_krs_of_one_advisee(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $this->resetApproval([$mahasiswa->mahasiswa_id], $ta->ta_id);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.krs.approve', $mahasiswa))
            ->assertSessionHas('success');

        $this->assertSame(
            0,
            Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereNull('disetujui_pada')
                ->count()
        );
        $this->assertDatabaseHas('krs', [
            'mahasiswa_id' => $mahasiswa->mahasiswa_id,
            'ta_id' => $ta->ta_id,
            'disetujui_oleh' => $dosen->dosen_id,
        ]);
    }

    public function test_dosen_can_bulk_approve_advisees_without_approving_other_dosen_students(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $mahasiswaKedua = Mahasiswa::where('dosen_id', $dosen->dosen_id)
            ->where('status_mhs', 'aktif')
            ->where('mahasiswa_id', '!=', $mahasiswa->mahasiswa_id)
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $ta->ta_id))
            ->firstOrFail();
        $mahasiswaLain = Mahasiswa::where('dosen_id', '!=', $dosen->dosen_id)
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $ta->ta_id))
            ->firstOrFail();

        $this->resetApproval([
            $mahasiswa->mahasiswa_id,
            $mahasiswaKedua->mahasiswa_id,
            $mahasiswaLain->mahasiswa_id,
        ], $ta->ta_id);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.krs.bulk-approve'), [
                'mahasiswa_ids' => [
                    $mahasiswa->mahasiswa_id,
                    $mahasiswaKedua->mahasiswa_id,
                    $mahasiswaLain->mahasiswa_id,
                ],
            ])
            ->assertSessionHas('success');

        $this->assertSame(
            0,
            Krs::whereIn('mahasiswa_id', [$mahasiswa->mahasiswa_id, $mahasiswaKedua->mahasiswa_id])
                ->where('ta_id', $ta->ta_id)
                ->whereNull('disetujui_pada')
                ->count()
        );
        $this->assertGreaterThan(
            0,
            Krs::where('mahasiswa_id', $mahasiswaLain->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereNull('disetujui_pada')
                ->count()
        );
    }

    public function test_dosen_can_cancel_approval_then_approve_krs_again(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $this->resetApproval([$mahasiswa->mahasiswa_id], $ta->ta_id);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.krs.approve', $mahasiswa))
            ->assertSessionHas('success');

        $this->post(route('dosen.mahasiswa.krs.cancel-approval', $mahasiswa))
            ->assertSessionHas('success');

        $this->assertGreaterThan(
            0,
            Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereNull('disetujui_pada')
                ->whereNull('disetujui_oleh')
                ->count()
        );

        $this->post(route('dosen.mahasiswa.krs.approve', $mahasiswa))
            ->assertSessionHas('success');

        $this->assertSame(
            0,
            Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
                ->where('ta_id', $ta->ta_id)
                ->whereNull('disetujui_pada')
                ->count()
        );
    }

    public function test_approved_krs_is_visible_and_cannot_be_deleted_by_mahasiswa(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $this->resetApproval([$mahasiswa->mahasiswa_id], $ta->ta_id);

        $this->actingAs($dosen, 'dosen')
            ->post(route('dosen.mahasiswa.krs.approve', $mahasiswa));

        $krs = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->firstOrFail();

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.status.krs.index'))
            ->assertOk()
            ->assertSee('KRS telah disetujui Dosen Pembimbing')
            ->assertDontSee('Cetak KAPRO')
            ->assertDontSee('Cetak BAAK')
            ->assertDontSee('Cetak DOSPEM')
            ->assertSee('Cetak Mahasiswa')
            ->assertSee($dosen->nama);

        $this->get(route('mahasiswa.krs.cetak-krs-mahasiswa'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->delete(route('mahasiswa.hapus.krs', $krs->krs_id))
            ->assertSessionHas('error', 'KRS sudah disetujui oleh Dosen Pembimbing dan tidak dapat dihapus.');

        $this->assertDatabaseHas('krs', ['krs_id' => $krs->krs_id]);
    }

    public function test_print_buttons_and_endpoints_are_locked_before_dospem_approval(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $this->resetApproval([$mahasiswa->mahasiswa_id], $ta->ta_id);
        $statusUrl = route('mahasiswa.status.krs.index');

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get($statusUrl)
            ->assertOk()
            ->assertSee('Dosen Pembimbing Akademik')
            ->assertSee($dosen->nama)
            ->assertSee('Menu cetak KRS tersedia setelah KRS disetujui Dosen Pembimbing.')
            ->assertDontSee('Cetak KAPRO')
            ->assertDontSee('Cetak BAAK')
            ->assertDontSee('Cetak DOSPEM')
            ->assertDontSee('Cetak Mahasiswa');

        $printRoutes = [
            'mahasiswa.krs.cetak-kapro',
            'mahasiswa.krs.cetak-krs-baak',
            'mahasiswa.krs.cetak-krs-dospem',
            'mahasiswa.krs.cetak-krs-mahasiswa',
        ];

        foreach ($printRoutes as $routeName) {
            $this->from($statusUrl)
                ->get(route($routeName))
                ->assertRedirect($statusUrl)
                ->assertSessionHas('error', 'KRS belum dapat dicetak karena belum disetujui Dosen Pembimbing.');
        }
    }

    public function test_status_krs_ignores_orphaned_curriculum_record_without_error(): void
    {
        [, $mahasiswa, $ta] = $this->approvalContext();
        $orphan = Krs::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->firstOrFail();
        $orphan->update(['kurikulum_id' => 999999999, 'matakuliah_id' => null]);

        $this->actingAs($mahasiswa, 'mahasiswa')
            ->get(route('mahasiswa.status.krs.index'))
            ->assertOk()
            ->assertSee('data KRS lama yang mata kuliahnya sudah tidak tersedia');
    }

    public function test_dosen_can_open_krs_detail_only_for_own_advisee(): void
    {
        [$dosen, $mahasiswa, $ta] = $this->approvalContext();
        $this->resetApproval([$mahasiswa->mahasiswa_id], $ta->ta_id);
        $mataKuliah = Krs::with('kurikulum.mataKuliah')
            ->where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)
            ->firstOrFail()
            ->kurikulum
            ->mataKuliah;

        $this->actingAs($dosen, 'dosen')
            ->get(route('dosen.nilai-dosen.lihat'))
            ->assertOk()
            ->assertSee('Aksi')
            ->assertSee('Lihat KRS');

        $detailUrl = route('dosen.mahasiswa.krs.show', $mahasiswa);
        $this->get($detailUrl)
            ->assertOk()
            ->assertSee('KRS Tahun Akademik')
            ->assertSee('ACC KRS Sekarang')
            ->assertSee($mataKuliah->nama);

        $this->from($detailUrl)
            ->post(route('dosen.mahasiswa.krs.approve', $mahasiswa))
            ->assertRedirect($detailUrl);

        $this->get($detailUrl)
            ->assertOk()
            ->assertSee('KRS Sudah Disetujui')
            ->assertSee('Batalkan ACC')
            ->assertDontSee('ACC KRS Sekarang');

        $mahasiswaLain = Mahasiswa::where('dosen_id', '!=', $dosen->dosen_id)
            ->where('status_mhs', 'aktif')
            ->firstOrFail();

        $this->get(route('dosen.mahasiswa.krs.show', $mahasiswaLain))
            ->assertForbidden();

        $this->post(route('dosen.mahasiswa.krs.cancel-approval', $mahasiswaLain))
            ->assertForbidden();
    }

    private function approvalContext(): array
    {
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $dosen = Dosen::findOrFail(16);
        $mahasiswa = Mahasiswa::where('dosen_id', $dosen->dosen_id)
            ->where('status_mhs', 'aktif')
            ->whereHas('krs', fn ($query) => $query->where('ta_id', $ta->ta_id))
            ->firstOrFail();

        return [$dosen, $mahasiswa, $ta];
    }

    private function resetApproval(array $mahasiswaIds, int $taId): void
    {
        Krs::whereIn('mahasiswa_id', $mahasiswaIds)
            ->where('ta_id', $taId)
            ->update([
                'disetujui_oleh' => null,
                'disetujui_pada' => null,
            ]);
    }
}
