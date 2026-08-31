<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PermissionCatalog
{
    public static function groups(Collection $permissions): array
    {
        $definitions = [
            'users' => ['label' => 'Pengguna & Role', 'icon' => 'bx-group', 'prefixes' => ['users-', 'role-']],
            'students' => ['label' => 'Data Mahasiswa', 'icon' => 'bx-user', 'prefixes' => ['mahasiswa-']],
            'lecturers' => ['label' => 'Data Dosen', 'icon' => 'bx-user-voice', 'prefixes' => ['dosen-']],
            'master' => ['label' => 'Master Akademik', 'icon' => 'bx-data', 'prefixes' => ['program-studi-', 'matakuliah-', 'ruangan-', 'gelombang-']],
            'curriculum' => ['label' => 'Kurikulum & Penugasan', 'icon' => 'bx-book-bookmark', 'prefixes' => ['kurikulum-', 'assign-dosen-']],
            'schedule' => ['label' => 'Jadwal & Tahun Akademik', 'icon' => 'bx-calendar', 'prefixes' => ['jadwal-', 'tahun-ajaran-', 'kalender-', 'calender-']],
            'learning' => ['label' => 'LMS, RPS & Perkuliahan', 'icon' => 'bx-book-reader', 'prefixes' => ['lms-', 'rps-', 'absensi-', 'bap-pengajaran-', 'pedoman-akademik-']],
            'krs' => ['label' => 'KRS & Arsip KRS', 'icon' => 'bx-spreadsheet', 'prefixes' => ['krs-', 'krs-archive-']],
            'assessment' => ['label' => 'Nilai, Ujian & EDOM', 'icon' => 'bx-bar-chart-square', 'prefixes' => ['evaluasi-', 'edom-', 'nilai-', 'penilaian-'], 'exact' => ['input-nilai', 'list-nilai', 'input-uap', 'list-uap']],
            'finance' => ['label' => 'Keuangan', 'icon' => 'bx-wallet', 'prefixes' => ['tarif-', 'tenor-', 'tagihan-', 'pembayaran-']],
            'services' => ['label' => 'Helpdesk & Transkrip', 'icon' => 'bx-support', 'prefixes' => ['permintaan-', 'pengajuan-transkrip-']],
            'student_affairs' => ['label' => 'Kemahasiswaan & SKPI', 'icon' => 'bx-award', 'prefixes' => ['aktivasi-', 'skpi-', 'berita-'], 'exact' => ['list-aktivasi']],
            'reports' => ['label' => 'Laporan', 'icon' => 'bx-file', 'prefixes' => ['laporan-', 'mutu-laporan-']],
            'system' => ['label' => 'Sistem & Audit', 'icon' => 'bx-cog', 'prefixes' => ['settings-', 'activity-log-', 'system-', 'product-']],
        ];

        $groups = [];
        foreach ($definitions as $key => $definition) {
            $groups[$key] = [
                'key' => $key,
                'label' => $definition['label'],
                'icon' => $definition['icon'],
                'permissions' => [],
            ];
        }
        $groups['other'] = [
            'key' => 'other',
            'label' => 'Permission Lainnya',
            'icon' => 'bx-dots-horizontal-rounded',
            'permissions' => [],
        ];

        foreach ($permissions as $permission) {
            $groupKey = 'other';
            foreach ($definitions as $key => $definition) {
                if (in_array($permission->name, $definition['exact'] ?? [], true)
                    || self::startsWithAny($permission->name, $definition['prefixes'])) {
                    $groupKey = $key;
                    break;
                }
            }

            $groups[$groupKey]['permissions'][] = [
                'model' => $permission,
                'label' => self::label($permission->name),
            ];
        }

        return array_values(array_filter(
            $groups,
            fn (array $group) => count($group['permissions']) > 0
        ));
    }

    public static function label(string $permission): string
    {
        $overrides = [
            'input-nilai' => 'Input nilai mahasiswa',
            'list-nilai' => 'Lihat nilai mahasiswa',
            'input-uap' => 'Input nilai UAP',
            'list-uap' => 'Lihat nilai UAP',
            'list-aktivasi' => 'Lihat aktivasi mahasiswa',
            'lms-list' => 'Buka LMS semua mata kuliah',
            'rps-list' => 'Buka RPS semua mata kuliah',
            'bap-pengajaran-list' => 'Buka BAP pengajaran dosen',
            'pedoman-akademik-list' => 'Lihat Pedoman Akademik',
            'pedoman-akademik-create' => 'Upload Pedoman Akademik',
            'pedoman-akademik-edit' => 'Ubah Pedoman Akademik',
            'pedoman-akademik-delete' => 'Hapus Pedoman Akademik',
            'krs-archive-list' => 'Lihat arsip KRS',
            'krs-archive-export' => 'Unduh arsip KRS',
            'mahasiswa-impersonate' => 'Login sebagai mahasiswa',
            'dosen-reset-password' => 'Reset password dosen',
            'settings-edit' => 'Ubah pengaturan portal',
            'system-health-list' => 'Lihat kesehatan sistem',
            'system-error-log-list' => 'Lihat error production',
            'system-backup-list' => 'Lihat daftar backup',
            'system-backup-create' => 'Buat backup manual',
            'system-backup-download' => 'Unduh backup sistem',
        ];

        if (isset($overrides[$permission])) {
            return $overrides[$permission];
        }

        $actions = [
            '-list' => 'Lihat',
            '-create' => 'Tambah',
            '-edit' => 'Ubah',
            '-delete' => 'Hapus',
            '-export' => 'Ekspor / unduh',
            '-import' => 'Import',
            '-generate' => 'Generate',
            '-status' => 'Ubah status',
            '-update' => 'Perbarui',
            '-reset-password' => 'Reset password',
            '-reset' => 'Reset',
            '-bulk-update' => 'Ubah massal',
            '-bulk' => 'Proses massal',
            '-show' => 'Lihat detail',
            '-detail' => 'Lihat detail',
        ];

        foreach ($actions as $suffix => $action) {
            if (Str::endsWith($permission, $suffix)) {
                $subject = Str::of($permission)->beforeLast($suffix)->replace('-', ' ')->title();

                return "{$action} {$subject}";
            }
        }

        return Str::of($permission)->replace('-', ' ')->title()->toString();
    }

    private static function startsWithAny(string $permission, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (Str::startsWith($permission, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
