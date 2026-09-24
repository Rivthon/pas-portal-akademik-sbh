<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanCuti;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CutiApprovalWorkflowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cuti_harus_disetujui_berurutan_dan_status_baru_berubah_setelah_baak(): void
    {
        $ta = TahunAkademik::where('status_ta', 1)->firstOrFail();
        $mahasiswa = Mahasiswa::with('programStudi')
            ->whereRaw('LOWER(status_mhs) = ?', ['aktif'])
            ->whereNotNull('dosen_id')
            ->whereHas('programStudi', fn ($query) => $query->whereNotNull('kaprodi_dosen_id'))
            ->firstOrFail();
        $dospem = Dosen::findOrFail($mahasiswa->dosen_id);
        $kaprodi = Dosen::findOrFail($mahasiswa->programStudi->kaprodi_dosen_id);
        $admin = User::firstOrFail();
        $admin->givePermissionTo(Permission::findOrCreate('cuti-list', 'web'));
        $admin->givePermissionTo(Permission::findOrCreate('cuti-validasi', 'web'));

        $this->actingAs($mahasiswa, 'mahasiswa')->get(route('mahasiswa.cuti.index'))->assertOk();
        $this->actingAs($mahasiswa, 'mahasiswa')->post(route('mahasiswa.cuti.store'), [
            'alasan' => 'Keperluan keluarga yang memerlukan cuti akademik sementara.',
        ])->assertRedirect();

        $cuti = PengajuanCuti::where('mahasiswa_id', $mahasiswa->mahasiswa_id)
            ->where('ta_id', $ta->ta_id)->latest('id')->firstOrFail();
        $this->assertSame(PengajuanCuti::MENUNGGU_DOSPEM, $cuti->status);
        $this->assertSame('aktif', strtolower((string) $mahasiswa->fresh()->status_mhs));
        $this->actingAs($dospem, 'dosen')->get(route('dosen.cuti.index'))->assertOk();

        $this->actingAs($kaprodi, 'dosen')->patch(route('dosen.kaprodi.cuti.decide', $cuti), [
            'keputusan' => 'setujui',
        ])->assertStatus(422);

        $this->actingAs($dospem, 'dosen')->patch(route('dosen.cuti.decide', $cuti), [
            'keputusan' => 'setujui',
        ])->assertRedirect();
        $this->assertSame(PengajuanCuti::MENUNGGU_KAPRODI, $cuti->fresh()->status);
        $this->actingAs($kaprodi, 'dosen')->get(route('dosen.kaprodi.cuti.index'))->assertOk();

        $this->actingAs($kaprodi, 'dosen')->patch(route('dosen.kaprodi.cuti.decide', $cuti), [
            'keputusan' => 'setujui',
        ])->assertRedirect();
        $this->assertSame(PengajuanCuti::MENUNGGU_BAAK, $cuti->fresh()->status);
        $this->assertSame('aktif', strtolower((string) $mahasiswa->fresh()->status_mhs));
        $this->actingAs($admin)->get(route('admin.cuti.index'))->assertOk();

        $this->actingAs($admin)->patch(route('admin.cuti.decide', $cuti), [
            'keputusan' => 'setujui',
        ])->assertRedirect();
        $this->assertSame(PengajuanCuti::DISETUJUI, $cuti->fresh()->status);
        $this->assertSame('cuti', strtolower((string) $mahasiswa->fresh()->status_mhs));
    }
}
