<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = array_unique(array_merge(
            $this->masterDataPermissions(),
            $this->academicPermissions(),
            $this->assessmentAndQualityPermissions(),
            $this->financePermissions(),
            $this->studentAffairsPermissions(),
            $this->systemPermissions(),
            $this->deprecatedPermissions()
        ));

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->syncFinalRoles();
        $this->syncLegacyRoles();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Berhasil meng-generate role final dan permission Portal Mahasiswa SBH / SIAKAD.');
    }

    private function masterDataPermissions(): array
    {
        return [
            'role-list', 'role-create', 'role-edit', 'role-delete',
            'users-list', 'users-create', 'users-edit', 'users-delete',
            'program-studi-list', 'program-studi-create', 'program-studi-edit', 'program-studi-delete',
            'dosen-list', 'dosen-create', 'dosen-edit', 'dosen-delete',
            'mahasiswa-list', 'mahasiswa-create', 'mahasiswa-edit', 'mahasiswa-delete',
            'mahasiswa-import', 'mahasiswa-export', 'mahasiswa-reset-password',
            'matakuliah-list', 'matakuliah-create', 'matakuliah-edit', 'matakuliah-delete',
            'ruangan-list', 'ruangan-create', 'ruangan-edit', 'ruangan-delete',
            'tahun-ajaran-list', 'tahun-ajaran-create', 'tahun-ajaran-edit', 'tahun-ajaran-delete', 'tahun-ajaran-status',
            'kalender-list', 'kalender-create', 'kalender-edit', 'kalender-delete',
        ];
    }

    private function academicPermissions(): array
    {
        return [
            'kurikulum-list', 'kurikulum-create', 'kurikulum-edit', 'kurikulum-delete',
            'assign-dosen-list', 'assign-dosen-create', 'assign-dosen-delete',
            'jadwal-list', 'jadwal-create', 'jadwal-edit', 'jadwal-delete', 'jadwal-generate',
            'jadwal-praktik-list', 'jadwal-praktik-create', 'jadwal-praktik-edit', 'jadwal-praktik-delete', 'jadwal-praktik-generate',
            'jadwal-uts-list', 'jadwal-uts-create', 'jadwal-uts-edit', 'jadwal-uts-delete', 'jadwal-uts-generate',
            'jadwal-uas-list', 'jadwal-uas-create', 'jadwal-uas-edit', 'jadwal-uas-delete', 'jadwal-uas-generate',
            'jadwal-uap-list', 'jadwal-uap-create', 'jadwal-uap-edit', 'jadwal-uap-delete',
            'krs-list', 'krs-create', 'krs-delete',
            'krs-archive-list', 'krs-archive-export',
            'absensi-list', 'absensi-edit', 'absensi-export',
            'lms-list', 'rps-list',
            'bap-pengajaran-list',
            'pedoman-akademik-list', 'pedoman-akademik-create', 'pedoman-akademik-edit', 'pedoman-akademik-delete',
        ];
    }

    private function assessmentAndQualityPermissions(): array
    {
        return [
            'evaluasi-list', 'evaluasi-create', 'evaluasi-edit', 'evaluasi-delete',
            'input-nilai', 'list-nilai', 'nilai-export',
            'input-uap', 'list-uap',
            'edom-list', 'edom-detail', 'edom-export', 'edom-reset',
            'penilaian-reset-edom',
            'laporan-list', 'laporan-export',
            'mutu-laporan-list', 'mutu-laporan-export',
        ];
    }

    private function financePermissions(): array
    {
        return [
            'gelombang-list', 'gelombang-create', 'gelombang-edit', 'gelombang-delete',
            'tarif-list', 'tarif-create', 'tarif-edit', 'tarif-delete', 'tarif-import',
            'tenor-list', 'tenor-create', 'tenor-edit', 'tenor-delete', 'tenor-bulk',
            'tagihan-list', 'tagihan-create', 'tagihan-edit', 'tagihan-delete', 'tagihan-generate',
            'pembayaran-update',
        ];
    }

    private function studentAffairsPermissions(): array
    {
        return [
            'aktivasi-list', 'aktivasi-update', 'aktivasi-bulk-update', 'aktivasi-reset',
            'list-aktivasi',
            'permintaan-list', 'permintaan-show', 'permintaan-edit', 'permintaan-status',
            'pengajuan-transkrip-list', 'pengajuan-transkrip-edit', 'pengajuan-transkrip-delete', 'pengajuan-transkrip-status',
            'skpi-list',
            'skpi-sertifikasi-list', 'skpi-sertifikasi-edit',
            'skpi-bahasa-list', 'skpi-bahasa-edit',
            'skpi-wirausaha-list', 'skpi-wirausaha-edit',
            'skpi-pkm-list', 'skpi-pkm-edit',
            'skpi-ppsm-list', 'skpi-ppsm-edit',
            'skpi-tambahan-list', 'skpi-tambahan-edit',
            'berita-list', 'berita-create', 'berita-edit', 'berita-delete',
        ];
    }

    private function systemPermissions(): array
    {
        return [
            'settings-edit',
            'activity-log-list', 'activity-log-export',
            'mahasiswa-impersonate',
        ];
    }

    private function deprecatedPermissions(): array
    {
        // TODO: Deprecated permissions kept temporarily for existing databases and legacy views/controllers.
        // Verify no route, controller, view, role, or user still depends on these before removing them.
        return [
            'calender-list', 'calender-edit',
            'product-list', 'product-create', 'product-edit', 'product-delete',
        ];
    }

    private function syncFinalRoles(): void
    {
        Role::firstOrCreate(['name' => 'Super Admin'])->syncPermissions(Permission::all());

        Role::firstOrCreate(['name' => 'BAAK'])->syncPermissions($this->baakPermissions());
        Role::firstOrCreate(['name' => 'UPMI'])->syncPermissions($this->upmiPermissions());
        Role::firstOrCreate(['name' => 'BAUK'])->syncPermissions($this->baukPermissions());
        Role::firstOrCreate(['name' => 'Kemahasiswaan'])->syncPermissions($this->kemahasiswaanPermissions());
    }

    private function syncLegacyRoles(): void
    {
        // TODO: Legacy roles are retained for transition. Map users to final roles before archiving these.
        Role::firstOrCreate(['name' => 'Admin Akademik'])->syncPermissions($this->baakPermissions());
        Role::firstOrCreate(['name' => 'Admin Keuangan'])->syncPermissions($this->baukPermissions());
        Role::firstOrCreate(['name' => 'Admin Kemahasiswaan'])->syncPermissions($this->kemahasiswaanPermissions());
        Role::firstOrCreate(['name' => 'Unit Penjamin Mutu Internal'])->syncPermissions($this->upmiPermissions());
        Role::firstOrCreate(['name' => 'baak'])->syncPermissions($this->baakPermissions());
        Role::firstOrCreate(['name' => 'bauk'])->syncPermissions($this->baukPermissions());
        Role::firstOrCreate(['name' => 'upmi'])->syncPermissions($this->upmiPermissions());

        Role::firstOrCreate(['name' => 'Kaprodi'])->syncPermissions([
            'mahasiswa-list',
            'dosen-list',
            'matakuliah-list',
            'kurikulum-list',
            'jadwal-list',
            'list-nilai',
        ]);
    }

    private function baakPermissions(): array
    {
        return [
            'program-studi-list', 'program-studi-create', 'program-studi-edit', 'program-studi-delete',
            'dosen-list', 'dosen-create', 'dosen-edit', 'dosen-delete',
            'mahasiswa-list', 'mahasiswa-edit',
            'matakuliah-list', 'matakuliah-create', 'matakuliah-edit', 'matakuliah-delete',
            'ruangan-list', 'ruangan-create', 'ruangan-edit', 'ruangan-delete',
            'tahun-ajaran-list', 'tahun-ajaran-create', 'tahun-ajaran-edit', 'tahun-ajaran-delete', 'tahun-ajaran-status',
            'kalender-list', 'kalender-create', 'kalender-edit', 'kalender-delete',
            'kurikulum-list', 'kurikulum-create', 'kurikulum-edit', 'kurikulum-delete',
            'assign-dosen-list', 'assign-dosen-create', 'assign-dosen-delete',
            'jadwal-list', 'jadwal-create', 'jadwal-edit', 'jadwal-delete', 'jadwal-generate',
            'jadwal-praktik-list', 'jadwal-praktik-create', 'jadwal-praktik-edit', 'jadwal-praktik-delete', 'jadwal-praktik-generate',
            'jadwal-uts-list', 'jadwal-uts-create', 'jadwal-uts-edit', 'jadwal-uts-delete', 'jadwal-uts-generate',
            'jadwal-uas-list', 'jadwal-uas-create', 'jadwal-uas-edit', 'jadwal-uas-delete', 'jadwal-uas-generate',
            'jadwal-uap-list', 'jadwal-uap-create', 'jadwal-uap-edit', 'jadwal-uap-delete',
            'krs-list', 'krs-create', 'krs-delete',
            'krs-archive-list', 'krs-archive-export',
            'absensi-list', 'absensi-edit', 'absensi-export',
            'lms-list', 'rps-list',
            'pedoman-akademik-list', 'pedoman-akademik-create', 'pedoman-akademik-edit', 'pedoman-akademik-delete',
            'evaluasi-list',
            'input-nilai', 'list-nilai', 'nilai-export',
            'input-uap', 'list-uap',
            'laporan-list', 'laporan-export',
            'pengajuan-transkrip-list',
        ];
    }

    private function upmiPermissions(): array
    {
        return [
            'program-studi-list',
            'dosen-list',
            'mahasiswa-list',
            'matakuliah-list',
            'kurikulum-list',
            'jadwal-list',
            'absensi-list',
            'lms-list', 'rps-list',
            'list-nilai',
            'evaluasi-list', 'evaluasi-create', 'evaluasi-edit', 'evaluasi-delete',
            'edom-list', 'edom-detail', 'edom-export', 'edom-reset',
            'penilaian-reset-edom',
            'laporan-list', 'laporan-export',
            'mutu-laporan-list', 'mutu-laporan-export',
        ];
    }

    private function baukPermissions(): array
    {
        return [
            'mahasiswa-list',
            'gelombang-list', 'gelombang-create', 'gelombang-edit', 'gelombang-delete',
            'tarif-list', 'tarif-create', 'tarif-edit', 'tarif-delete', 'tarif-import',
            'tenor-list', 'tenor-create', 'tenor-edit', 'tenor-delete', 'tenor-bulk',
            'tagihan-list', 'tagihan-create', 'tagihan-edit', 'tagihan-delete', 'tagihan-generate',
            'pembayaran-update',
            'bap-pengajaran-list',
        ];
    }

    private function kemahasiswaanPermissions(): array
    {
        return [
            'mahasiswa-list', 'mahasiswa-create', 'mahasiswa-edit', 'mahasiswa-delete',
            'mahasiswa-import', 'mahasiswa-export', 'mahasiswa-reset-password',
            'aktivasi-list', 'aktivasi-update', 'aktivasi-bulk-update', 'aktivasi-reset',
            'list-aktivasi',
            'permintaan-list', 'permintaan-show', 'permintaan-edit', 'permintaan-status',
            'pengajuan-transkrip-list', 'pengajuan-transkrip-edit', 'pengajuan-transkrip-delete', 'pengajuan-transkrip-status',
            'skpi-list',
            'skpi-sertifikasi-list', 'skpi-sertifikasi-edit',
            'skpi-bahasa-list', 'skpi-bahasa-edit',
            'skpi-wirausaha-list', 'skpi-wirausaha-edit',
            'skpi-pkm-list', 'skpi-pkm-edit',
            'skpi-ppsm-list', 'skpi-ppsm-edit',
            'skpi-tambahan-list', 'skpi-tambahan-edit',
            'berita-list', 'berita-create', 'berita-edit', 'berita-delete',
        ];
    }
}
