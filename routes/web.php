<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Akademik\AbsensiController;
use App\Http\Controllers\Admin\Akademik\AdminKrsController;
use App\Http\Controllers\Admin\Akademik\CalendarAkademikController;
use App\Http\Controllers\Admin\Akademik\JadwalController;
use App\Http\Controllers\Admin\Akademik\JadwalPraktikController;
use App\Http\Controllers\Admin\Akademik\JadwalUapController;
use App\Http\Controllers\Admin\Akademik\JadwalUasController;
use App\Http\Controllers\Admin\Akademik\JadwalUtsController;
use App\Http\Controllers\Admin\Akademik\KrsArchiveController;
use App\Http\Controllers\Admin\Akademik\KurikulumController;
use App\Http\Controllers\Admin\Akademik\LmsController as AdminLmsController;
use App\Http\Controllers\Admin\Akademik\MatakuliahController;
use App\Http\Controllers\Admin\Akademik\PedomanAkademikController as AdminPedomanAkademikController;
use App\Http\Controllers\Admin\Akademik\PertemuanController;
use App\Http\Controllers\Admin\Akademik\RpsController as AdminRpsController;
use App\Http\Controllers\Admin\Akademik\RuanganController;
use App\Http\Controllers\Admin\Akademik\TahunAkademikController;
use App\Http\Controllers\Admin\BapPengajaranController;
use App\Http\Controllers\Admin\BeritaController;
use App\Http\Controllers\Admin\DosenKurikulumController;
use App\Http\Controllers\Admin\DosenImpersonationController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\Kemahasiswaan\AktivasiController;
use App\Http\Controllers\Admin\Kemahasiswaan\MahasiswaController;
use App\Http\Controllers\Admin\Kemahasiswaan\PengajuanTranskripController;
use App\Http\Controllers\Admin\Kemahasiswaan\PermintaanController;
use App\Http\Controllers\Admin\Keuangan\GelombangController;
use App\Http\Controllers\Admin\Keuangan\TagihanMahasiswaController;
use App\Http\Controllers\Admin\Keuangan\TarifController;
use App\Http\Controllers\Admin\Keuangan\TenorPembayaranController;
use App\Http\Controllers\Admin\LaporanController;
use App\Http\Controllers\Admin\MahasiswaImpersonationController;
use App\Http\Controllers\Admin\MasterData\DosenController;
use App\Http\Controllers\Admin\MasterData\ProgramStudiController;
use App\Http\Controllers\Admin\MasterData\RoleController;
use App\Http\Controllers\Admin\MasterData\UserController;
use App\Http\Controllers\Admin\Penilaian\AdminCekNilaiController;
use App\Http\Controllers\Admin\Penilaian\EvaluasiController;
use App\Http\Controllers\Admin\Penilaian\InputNilaiController;
use App\Http\Controllers\Admin\Penilaian\KhsPublicationController;
use App\Http\Controllers\Admin\Penilaian\NilaiController;
use App\Http\Controllers\Admin\Penilaian\PenilaianController;
use App\Http\Controllers\Admin\Penilaian\UapNilaiController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\ValidatorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MahasiswaLoginController;
use App\Http\Controllers\CalendarAkademikFileController;
use App\Http\Controllers\Dosen\AbsensiPraktikController as DosenAbsensiPraktikController;
use App\Http\Controllers\Dosen\DashboardDosenController;
use App\Http\Controllers\Dosen\KaprodiVerificationController;
use App\Http\Controllers\Dosen\KurikulumKrsController;
use App\Http\Controllers\Dosen\LaporanAbsensiController;
use App\Http\Controllers\Dosen\LmsDosenController;
use App\Http\Controllers\Dosen\LoginDosenController;
use App\Http\Controllers\Dosen\ModulAkademikController;
use App\Http\Controllers\Dosen\PerkuliahanDosenController;
use App\Http\Controllers\Dosen\PermintaanController as DosenPermintaanController;
use App\Http\Controllers\Dosen\ProfileDosenController;
use App\Http\Controllers\Dosen\QuizDosenController;
use App\Http\Controllers\Dosen\RpsDosenController;
use App\Http\Controllers\LmsCalendarNoteController;
use App\Http\Controllers\Mahasiswa\AbsensiPraktikController as MahasiswaAbsensiPraktikController;
use App\Http\Controllers\Mahasiswa\AdministrasiController;
use App\Http\Controllers\Mahasiswa\AkademikController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\EdomController;
use App\Http\Controllers\Mahasiswa\JadwalKuliahController;
use App\Http\Controllers\Mahasiswa\LmsMahasiswaController;
use App\Http\Controllers\Mahasiswa\PedomanAkademikController as MahasiswaPedomanAkademikController;
use App\Http\Controllers\Mahasiswa\PerkuliahanController;
use App\Http\Controllers\Mahasiswa\PermintaanController as MhsPermintaanController;
use App\Http\Controllers\Mahasiswa\ProfileUserController;
use App\Http\Controllers\Mahasiswa\QuizMahasiswaController;
use App\Http\Controllers\Mahasiswa\RekapAbsensiController;
use App\Http\Controllers\Mahasiswa\RpsMhsController;
use App\Http\Controllers\Mahasiswa\SkpiController;
use App\Http\Controllers\Mahasiswa\UapController;
use App\Http\Controllers\Mahasiswa\UjianController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // 1. Cek jika yang login adalah Admin (guard: web)
    if (Auth::guard('web')->check()) {
        return redirect()->route('admin.home');
    }

    // 2. Cek jika yang login adalah Dosen (guard: dosen)
    if (Auth::guard('dosen')->check()) {
        return redirect()->route('dosen.dashboard');
    }

    // 3. Cek jika yang login adalah Mahasiswa (guard: mahasiswa)
    if (Auth::guard('mahasiswa')->check()) {
        return redirect()->route('mahasiswa.dashboard');
    }

    // Jika tidak ada session yang aktif, redirect ke halaman login default (misal: mahasiswa)
    return redirect()->route('mahasiswa.login');

})->name('login'); // <--- PENTING: Tambahkan name('login') di sini

// Rute Publik (Verifikasi Keaslian Dokumen)
Route::get('/verify-ujian/{token}', [VerificationController::class, 'verifyUjian'])->name('verify.ujian');

// Routes untuk login mahasiswa
Route::prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('login', [MahasiswaLoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [MahasiswaLoginController::class, 'login'])
        ->middleware(['login.progressive:mahasiswa', 'throttle:login-ip'])->name('mhs.login');
    Route::post('logout', [MahasiswaLoginController::class, 'logout'])->name('logout');
    Route::middleware('auth:mahasiswa')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/kalender-akademik/{calendar}/file', [CalendarAkademikFileController::class, 'show'])
            ->name('calendar-akademik.file');
        Route::get('/pedoman-akademik', [MahasiswaPedomanAkademikController::class, 'index'])
            ->name('pedoman-akademik.index');
        Route::get('/pedoman-akademik/{pedoman}/lihat', [MahasiswaPedomanAkademikController::class, 'preview'])
            ->name('pedoman-akademik.preview');
        Route::get('/pedoman-akademik/{pedoman}/download', [MahasiswaPedomanAkademikController::class, 'download'])
            ->name('pedoman-akademik.download');
        // Profil mahasiswa
        Route::get('profile', [ProfileUserController::class, 'index'])->name('profile.index');
        Route::post('profile', [ProfileUserController::class, 'update'])->name('profile.update');
        Route::post('/semester/update', [ProfileUserController::class, 'smtUpdate'])->name('semester.update');

        // Jadwal mahasiswa
        // Route::get('jadwal', [JadwalKuliahController::class, 'index'])->name('jadwal.index');
        Route::get('jadwal-kuliah', [PerkuliahanController::class, 'index'])->name('jadwal-kuliah.index');
        Route::get('jadwal-praktik', [PerkuliahanController::class, 'jadwalPraktik'])->name('jadwal-praktik.index');
        Route::get('jadwal-uts', [PerkuliahanController::class, 'jadwalUts'])->name('jadwal-uts.index');
        Route::get('jadwal-uas', [PerkuliahanController::class, 'jadwalUas'])->name('jadwal-uas.index');
        Route::get('jadwal-uap', [PerkuliahanController::class, 'jadwalUap'])->name('jadwal-uap.index');

        Route::get('/cetak-kartu-uts', [PerkuliahanController::class, 'cetakUts'])->middleware('mhs.status:uts')->name('cetak.kartu.uts');
        Route::get('/cetak-kartu-uas', [PerkuliahanController::class, 'cetakUas'])->middleware('mhs.status:uas')->name('cetak.kartu.uas');
        Route::get('/cetak-kartu-uap', [PerkuliahanController::class, 'cetakUap'])->middleware('mhs.status:uap')->name('cetak.kartu.uap');

        Route::post('/krs/simpan', [AkademikController::class, 'nyimpenKrs'])->name('simpan.krs');
        Route::get('/krs', [AkademikController::class, 'index'])->name('krs.index');
        Route::get('/status-krs', [AkademikController::class, 'tampilkanKrs'])->name('status.krs.index');
        Route::post('/status-krs/komentar', [AkademikController::class, 'storeKrsGuidanceReply'])->name('status.krs.comment.store');
        Route::delete('/krs/{id}/hapus', [AkademikController::class, 'hapusKrs'])->name('hapus.krs');

        Route::get('/kartu-hasil-studi/mhs', [AkademikController::class, 'tampilanKartuHasil'])->name('kartu-hasil.index');
        Route::get('/riwayat-khs', [AkademikController::class, 'riwayatKartuHasil'])->name('khs.riwayat');
        Route::get('/khs/cetak-pdf', [AkademikController::class, 'cetakKhs'])->name('khs.cetak');

        Route::get('/krs/cetak-pdf-kapro', [AkademikController::class, 'cetakKapro'])->middleware('mhs.status:krs')->name('krs.cetak-kapro');
        Route::get('/krs/cetak-pdf-baak', [AkademikController::class, 'cetakBaak'])->middleware('mhs.status:krs')->name('krs.cetak-krs-baak');
        Route::get('/krs/cetak-pdf-dospem', [AkademikController::class, 'cetakDospem'])->middleware('mhs.status:krs')->name('krs.cetak-krs-dospem');
        Route::get('/krs/cetak-pdf-mahasiswa', [AkademikController::class, 'cetakMahasiswa'])->middleware('mhs.status:krs')->name('krs.cetak-krs-mahasiswa');

        Route::get('/krs/cetak-transkrip-mahasiswa', [AkademikController::class, 'cetakTranskrip'])->name('cetak-transkrip');

        Route::get('/nilai-uts', [UjianController::class, 'tampilkanNilaiUts'])->name('nilai-uts.index');
        Route::get('/nilai-uas', [UjianController::class, 'tampilkanNilaiUas'])->name('nilai-uas.index');
        Route::get('/nilai-akhir', [UjianController::class, 'tampilkanNilaiAkhir'])->name('nilai-akhir.index');

        Route::get('/edom', [EdomController::class, 'index'])->name('edom.index');
        Route::get('/edom/form/{krs_id}', [EdomController::class, 'form'])->name('edom.form');
        Route::post('/edom/submit/{krs_id}/{dosen_id}', [EdomController::class, 'submit'])->name('edom.submit');
        Route::post('/edom/konfirmasi', [EdomController::class, 'konfirmasiEdom'])->name('edom.konfirmasi');
        // Absensi
        Route::get('jadwal/{jadwalId}/absensi', [App\Http\Controllers\Mahasiswa\AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('absensi', [App\Http\Controllers\Mahasiswa\AbsensiController::class, 'store'])->name('absensi.store');
        // Route::get('jadwal/{jadwalId}/riwayat-absensi', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
        Route::get('/administrasi', [AdministrasiController::class, 'index'])->name('administrasi.index');

        Route::get('/api/berita', [BeritaController::class, 'getBerita'])->name('getBerita');
        Route::get('/api/index/berita', [BeritaController::class, 'indexBerita'])->name('index.berita');
        Route::get('/api/berita-kampus', [BeritaController::class, 'getBeritaKampus'])->name('getBeritaKampus');
        Route::get('/berita/{id}', [BeritaController::class, 'getDetailBerita'])->name('berita.detail');

        Route::get('/pengajuan', [PengajuanTranskripController::class, 'index'])->name('pengajuan.index');
        Route::get('/pengajuan/create', [PengajuanTranskripController::class, 'create'])->name('pengajuan.create');
        Route::post('/pengajuan', [PengajuanTranskripController::class, 'store'])->name('pengajuan.store');
        Route::get('/pengajuan/{pengajuan}', [PengajuanTranskripController::class, 'show'])->name('pengajuan.show');

        Route::get('/aktivitas/prestasi', [SkpiController::class, 'index'])->name('skpi.index');

        Route::get('/skpi/sertifikasi', [SkpiController::class, 'sertifikasi'])->name('skpi.sertifikasi');
        Route::post('sertifikasi', [SkpiController::class, 'store_sertifikasi'])->name('sertifikasi.store');
        Route::get('edit-sertifikasi/{id}/edit', [SkpiController::class, 'edit_sertifikasi'])->name('edit.sertifikasi');
        Route::put('update-sertifikasi/{id}', [SkpiController::class, 'update_sertifikasi'])->name('update.sertifikasi');
        Route::delete('delete-sertifikasi/{id}', [SkpiController::class, 'destroy_sertifikasi'])->name('delete.sertifikasi');

        Route::get('/skpi/bahasa', [SkpiController::class, 'bahasa'])->name('skpi.bahasa');
        Route::post('bahasa', [SkpiController::class, 'store_bahasa'])->name('bahasa.store');
        Route::get('edit-bahasa/{id}/edit', [SkpiController::class, 'edit_bahasa'])->name('edit.bahasa');
        Route::put('update-bahasa/{id}', [SkpiController::class, 'update_bahasa'])->name('update.bahasa');
        Route::delete('delete-bahasa/{id}', [SkpiController::class, 'destroy_bahasa'])->name('delete.bahasa');

        Route::get('/skpi/wirausaha', [SkpiController::class, 'wirausaha'])->name('skpi.wirausaha');
        Route::post('wirausaha', [SkpiController::class, 'store_wirausaha'])->name('wirausaha.store');
        Route::get('edit-wirausaha/{id}/edit', [SkpiController::class, 'edit_wirausaha'])->name('edit.wirausaha');
        Route::put('update-wirausaha/{id}', [SkpiController::class, 'update_wirausaha'])->name('update.wirausaha');
        Route::delete('delete-wirausaha/{id}', [SkpiController::class, 'destroy_wirausaha'])->name('delete.wirausaha');

        Route::get('/skpi/pkm', [SkpiController::class, 'pkm'])->name('skpi.pkm');
        Route::post('pkm', [SkpiController::class, 'store_pkm'])->name('pkm.store');
        Route::get('edit-pkm/{id}/edit', [SkpiController::class, 'edit_pkm'])->name('edit.pkm');
        Route::put('update-pkm/{id}', [SkpiController::class, 'update_pkm'])->name('update.pkm');
        Route::delete('delete-pkm/{id}', [SkpiController::class, 'destroy_pkm'])->name('delete.pkm');

        Route::get('/skpi/ppsm', [SkpiController::class, 'ppsm'])->name('skpi.ppsm');
        Route::post('ppsm', [SkpiController::class, 'store_ppsm'])->name('ppsm.store');
        Route::get('edit-ppsm/{id}/edit', [SkpiController::class, 'edit_ppsm'])->name('edit.ppsm');
        Route::put('update-ppsm/{id}', [SkpiController::class, 'update_ppsm'])->name('update.ppsm');
        Route::delete('delete-ppsm/{id}', [SkpiController::class, 'destroy_ppsm'])->name('delete.ppsm');

        Route::get('/skpi/tambahan', [SkpiController::class, 'tambahan'])->name('skpi.tambahan');
        Route::get('edit-tambahan/{id}/edit', [SkpiController::class, 'edit_tambahan'])->name('edit.tambahan');
        Route::put('update-tambahan/{id}', [SkpiController::class, 'update_tambahan'])->name('update.tambahan');
        Route::delete('delete-tambahan/{id}', [SkpiController::class, 'destroy_tambahan'])->name('delete.tambahan');
        Route::post('tambahan', [SkpiController::class, 'store_tambahan'])->name('tambahan.store');
        Route::get('/skpi/cetak', [SkpiController::class, 'cetak'])->name('skpi.cetak');
        Route::get('/skpi/download', [SkpiController::class, 'download'])->name('skpi.download');

        Route::get('/transkrip/download', [PengajuanTranskripController::class, 'downloadTranskrip'])
            ->name('pengajuan.cetak');
        Route::get('/permintaan', [MhsPermintaanController::class, 'index'])->name('permintaan.index');
        Route::get('/permintaan/create', [MhsPermintaanController::class, 'create'])->name('permintaan.create');
        Route::post('/permintaan', [MhsPermintaanController::class, 'store'])->name('permintaan.store');
        Route::delete('/permintaan/{id}', [MhsPermintaanController::class, 'destroy'])->name('permintaan.destroy');
        Route::get('/nilai-uap', [UapController::class, 'index'])->name('uap.index');
        Route::get('/rekap-absensi', [RekapAbsensiController::class, 'index'])->name('rekap.absensi');
        Route::get('/rekap-absensi/{jadwalId}', [RekapAbsensiController::class, 'detail'])->name('rekap.absensi.detail');
        Route::get('/riwayat-absensi-praktik', [MahasiswaAbsensiPraktikController::class, 'index'])->name('absensi-praktik.index');
        Route::get('/riwayat-absensi-praktik/{jadwal}', [MahasiswaAbsensiPraktikController::class, 'show'])->name('absensi-praktik.show');

        Route::prefix('rps')->name('rps.')->group(function () {

            Route::get('/', [RpsMhsController::class, 'index'])
                ->name('index');
            Route::get('/{rps}/lihat', [RpsMhsController::class, 'show'])
                ->name('show');

        });
        Route::prefix('lms')
            ->name('lms.') //
            ->group(function () {

                Route::get('/', [LmsMahasiswaController::class, 'index'])->name('index');
                Route::post('/calendar/notes', [LmsCalendarNoteController::class, 'store'])->name('calendar.notes.store');
                Route::put('/calendar/notes/{note}', [LmsCalendarNoteController::class, 'update'])->name('calendar.notes.update');
                Route::delete('/calendar/notes/{note}', [LmsCalendarNoteController::class, 'destroy'])->name('calendar.notes.destroy');
                Route::get('/nilai', [LmsMahasiswaController::class, 'gradebookIndex'])->name('gradebook.index');
                Route::get('/nilai/{jadwal}', [LmsMahasiswaController::class, 'gradebookShow'])->name('gradebook.show');
                Route::get('/mata-kuliah/{jadwal}', [LmsMahasiswaController::class, 'show'])->name('show');
                Route::get('/mata-kuliah/{jadwal}/quiz', [QuizMahasiswaController::class, 'index'])->name('quiz.index');
                Route::get('/quiz/{quiz}', [QuizMahasiswaController::class, 'show'])->name('quiz.show');
                Route::post('/quiz/{quiz}/submit', [QuizMahasiswaController::class, 'submit'])->name('quiz.submit');
                Route::get('/quiz/jawaban/{jawaban}/download', [QuizMahasiswaController::class, 'downloadJawaban'])->name('quiz.jawaban.download');

                Route::get('/tugas/{tugas}', [
                    LmsMahasiswaController::class,
                    'showTugas',
                ])->name('tugas.show');
                Route::get('/materi/{materi}/view', [LmsMahasiswaController::class, 'showMateri'])->name('materi.show');
                Route::post('/tugas/{tugas}/kumpulkan', [
                    LmsMahasiswaController::class,
                    'kumpulkanTugas',
                ])->name('tugas.kumpulkan');

                Route::get('/pengumpulan/{pengumpulan}/download', [
                    LmsMahasiswaController::class,
                    'downloadPengumpulan',
                ])->name('pengumpulan.download');
            });
    });
});

Route::get('/storage/lms/materi/{filename}', [AdminLmsController::class, 'showMateriFile'])
    ->middleware(['auth', 'role:Admin|Super Admin|Admin Akademik|bauk'])
    ->where('filename', '[^/]+')
    ->name('admin.lms.materi.file');

Route::prefix('admin')->name('admin.')->group(function () {

    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login'])
        ->middleware(['login.progressive:web', 'throttle:login-ip'])->name('login.submit');
    Route::post('logout', [LoginController::class, 'logout'])->name('logout');
    Route::middleware('auth')->group(function () {
        Route::get('/home', [HomeController::class, 'index'])->name('home');

        Route::middleware('permission:bap-pengajaran-list')
            ->prefix('bap-pengajaran')
            ->name('bap-pengajaran.')
            ->group(function () {
                Route::get('/', [BapPengajaranController::class, 'index'])->name('index');
                Route::get('/dosen/{dosen}', [BapPengajaranController::class, 'show'])->name('show');
                Route::get('/dosen/{dosen}/pdf', [BapPengajaranController::class, 'downloadPdf'])->name('pdf');
            });

        Route::middleware('permission:lms-list')->group(function () {
            Route::get('/lms', [AdminLmsController::class, 'index'])->name('lms.index');
            Route::post('/lms/calendar/notes', [LmsCalendarNoteController::class, 'store'])->name('lms.calendar.notes.store');
            Route::put('/lms/calendar/notes/{note}', [LmsCalendarNoteController::class, 'update'])->name('lms.calendar.notes.update');
            Route::delete('/lms/calendar/notes/{note}', [LmsCalendarNoteController::class, 'destroy'])->name('lms.calendar.notes.destroy');
            Route::get('/lms/materi/{materi}/view', [AdminLmsController::class, 'showMateri'])->name('lms.materi.show');
            Route::get('/lms/{jadwal}', [AdminLmsController::class, 'show'])->name('lms.show');
        });
        Route::middleware('permission:rps-list')->group(function () {
            Route::get('/rps', [AdminRpsController::class, 'index'])->name('rps.index');
            Route::get('/rps/{rps}', [AdminRpsController::class, 'show'])->name('rps.show');
        });

        Route::prefix('pedoman-akademik')->name('pedoman-akademik.')->group(function () {
            Route::get('/', [AdminPedomanAkademikController::class, 'index'])
                ->middleware('permission:pedoman-akademik-list')->name('index');
            Route::post('/', [AdminPedomanAkademikController::class, 'store'])
                ->middleware('permission:pedoman-akademik-create')->name('store');
            Route::get('/{pedoman}/edit', [AdminPedomanAkademikController::class, 'edit'])
                ->middleware('permission:pedoman-akademik-edit')->name('edit');
            Route::put('/{pedoman}', [AdminPedomanAkademikController::class, 'update'])
                ->middleware('permission:pedoman-akademik-edit')->name('update');
            Route::delete('/{pedoman}', [AdminPedomanAkademikController::class, 'destroy'])
                ->middleware('permission:pedoman-akademik-delete')->name('destroy');
            Route::get('/{pedoman}/lihat', [AdminPedomanAkademikController::class, 'preview'])
                ->middleware('permission:pedoman-akademik-list')->name('preview');
            Route::get('/{pedoman}/download', [AdminPedomanAkademikController::class, 'download'])
                ->middleware('permission:pedoman-akademik-list')->name('download');
        });

        Route::get('/settings', [SettingController::class, 'edit'])->middleware('permission:settings-edit')->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->middleware('permission:settings-edit')->name('settings.update');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Resource controllers
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])
            ->middleware('permission:activity-log-list')->name('activity-logs.index');
        Route::get('/activity-logs/pdf', [ActivityLogController::class, 'exportPdf'])
            ->middleware('permission:activity-log-export')->name('activity-logs.pdf');
        Route::prefix('system')->name('system.')->group(function () {
            Route::get('/health', [SystemHealthController::class, 'index'])
                ->middleware('permission:system-health-list')->name('health');
            Route::post('/backups', [SystemHealthController::class, 'createBackup'])
                ->middleware('permission:system-backup-create')->name('backups.create');
            Route::get('/backups/{filename}', [SystemHealthController::class, 'downloadBackup'])
                ->middleware('permission:system-backup-download')
                ->where('filename', '[A-Za-z0-9_.-]+')
                ->name('backups.download');
        });
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::resource('products', ProductController::class);
        Route::resource('program-studi', ProgramStudiController::class);
        Route::resource('mahasiswa', MahasiswaController::class);
        Route::post('/mahasiswa/{mahasiswa}/impersonate', [MahasiswaImpersonationController::class, 'store'])
            ->middleware('permission:mahasiswa-impersonate,web')
            ->name('mahasiswa.impersonate');
        Route::post('/mahasiswa/impersonate/stop', [MahasiswaImpersonationController::class, 'destroy'])
            ->middleware('permission:mahasiswa-impersonate,web')
            ->name('mahasiswa.impersonate.stop');
        Route::put('/mahasiswa/{id}/update-status', [MahasiswaController::class, 'updateStatus'])->middleware('permission:mahasiswa-edit')->name('mahasiswa.updateStatus');
        Route::put('/mahasiswa/{id}/update-dosen', [MahasiswaController::class, 'updateDosen'])->middleware('permission:mahasiswa-edit')->name('mahasiswa.updateDosen');
        Route::put('/mahasiswa/search-dosen', [MahasiswaController::class, 'searchDosen'])->middleware('permission:mahasiswa-list')->name('mahasiswa.searchDosen');

        Route::get('/export', [MahasiswaController::class, 'exportExcel'])->middleware('permission:mahasiswa-export')->name('export');

        Route::resource('matakuliah', MatakuliahController::class);
        Route::post('/dosen/{dosen}/reset-password', [DosenController::class, 'resetPassword'])
            ->middleware('permission:dosen-reset-password')
            ->name('dosen.reset-password');
        Route::post('/dosen/{dosen}/impersonate', [DosenImpersonationController::class, 'store'])
            ->middleware('permission:dosen-impersonate,web')
            ->name('dosen.impersonate');
        Route::post('/dosen/impersonate/stop', [DosenImpersonationController::class, 'destroy'])
            ->middleware('permission:dosen-impersonate,web')
            ->name('dosen.impersonate.stop');
        Route::resource('dosen', DosenController::class);
        Route::resource('tahun-ajaran', TahunAkademikController::class);
        Route::resource('evaluasi', EvaluasiController::class);
        Route::resource('ruangan', RuanganController::class);
        Route::resource('kurikulum', KurikulumController::class);
        Route::resource('tarif', TarifController::class);
        Route::post('/tarif/bulk', [TarifController::class, 'storeBulk'])->middleware('permission:tarif-create')->name('tarif.storeBulk');
        Route::post('/tarif/import', [TarifController::class, 'import'])->middleware('permission:tarif-import')->name('tarif.import');
        Route::get('/tarif/import/template', [TarifController::class, 'downloadTemplate'])->middleware('permission:tarif-import')->name('tarif.download-template');
        Route::resource('gelombang', GelombangController::class);

        Route::get('/calender', [CalendarAkademikController::class, 'index'])->name('calender.index');
        Route::get('/calender/{calendar}/file', [CalendarAkademikFileController::class, 'show'])->middleware('permission:kalender-list')->name('calender.file');
        Route::get('/calender/{id}/edit', [CalendarAkademikController::class, 'edit'])->name('calender.edit');
        Route::put('/calender/{id}', [CalendarAkademikController::class, 'update'])->name('calender.update');

        Route::get('tenor-pembayaran', [TenorPembayaranController::class, 'index'])->name('tenor-pembayaran.index');
        Route::get('tenor-pembayaran/create', [TenorPembayaranController::class, 'create'])->name('tenor-pembayaran.create');
        Route::post('tenor-pembayaran/bulk', [TenorPembayaranController::class, 'storeBulk'])->middleware('permission:tenor-bulk')->name('tenor-pembayaran.storeBulk');
        Route::post('tenor-pembayaran', [TenorPembayaranController::class, 'store'])->name('tenor-pembayaran.store');
        Route::get('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'show'])->name('tenor-pembayaran.show');
        Route::get('tenor-pembayaran/{id}/edit', [TenorPembayaranController::class, 'edit'])->name('tenor-pembayaran.edit');
        Route::put('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'update'])->name('tenor-pembayaran.update');
        Route::delete('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'destroy'])->name('tenor-pembayaran.destroy');

        Route::get('/tagihan-mahasiswa', [TagihanMahasiswaController::class, 'index'])->name('tagihan-mahasiswa.index');
        Route::post('/tagihan-mahasiswa', [TagihanMahasiswaController::class, 'store'])->name('tagihan-mahasiswa.store');
        Route::put('/tagihan-mahasiswa/{id}', [TagihanMahasiswaController::class, 'update'])->name('tagihan-mahasiswa.update');
        Route::delete('/tagihan-mahasiswa/{id}', [TagihanMahasiswaController::class, 'destroy'])->name('tagihan-mahasiswa.destroy');
        Route::get('/tagihan-mahasiswa/get-mahasiswa', [TagihanMahasiswaController::class, 'getMahasiswa'])->middleware('permission:tagihan-list')->name('get.mahasiswa');
        Route::get('/tagihan-mahasiswa/{id}/detail', [TagihanMahasiswaController::class, 'getDetailTagihan'])->middleware('permission:tagihan-list')->name('tagihan.detail');
        Route::get('/tagihan-mahasiswa/{id}/belum-lunas', [TagihanMahasiswaController::class, 'getTagihanForBayar'])->middleware('permission:pembayaran-update')->name('tagihan.belumLunas');
        Route::post('/tagihan-mahasiswa/{id}/update-pembayaran', [TagihanMahasiswaController::class, 'updatePembayaran'])->middleware('permission:pembayaran-update')->name('simpanPembayaran');

        // Generate tagihan otomatis untuk mahasiswa aktif
        Route::post('/generate-tagihan', [TagihanMahasiswaController::class, 'generateTagihan'])->middleware('permission:tagihan-generate')->name('generate');

        Route::post('/mata-kuliah/store-multiple', [KurikulumController::class, 'storeMultiple'])->middleware('permission:kurikulum-create')->name('kurikulum.storeMultiple');
        Route::get('/mata-kuliah/filter', [KurikulumController::class, 'filter'])->middleware('permission:kurikulum-list')->name('kurikulum.filter');

        Route::get('/jadwaluts', [JadwalUtsController::class, 'index'])->middleware('permission:jadwal-uts-list')->name('jadwal-uts.index');
        Route::get('/jadwaluts/filter', [JadwalUtsController::class, 'filter'])->middleware('permission:jadwal-uts-list')->name('jadwal-uts.filter');
        Route::post('/jadwal-uts/generate', [JadwalUtsController::class, 'generateJadwalUTS'])->middleware('permission:jadwal-uts-generate')->name('jadwal-uts.generate');
        Route::get('/jadwaluts/create', [JadwalUtsController::class, 'create'])->middleware('permission:jadwal-uts-create')->name('jadwal-uts.create');
        Route::post('/jadwaluts', [JadwalUtsController::class, 'store'])->middleware('permission:jadwal-uts-create')->name('jadwal-uts.store');
        Route::get('/jadwaluts/{id}/edit', [JadwalUtsController::class, 'edit'])->middleware('permission:jadwal-uts-edit')->name('jadwal-uts.edit');
        Route::post('/jadwal-uts/update/{id}', [JadwalUtsController::class, 'update'])->middleware('permission:jadwal-uts-edit')->name('jadwal-uts.update');
        Route::delete('/jadwal-uts/delete/{id}', [JadwalUtsController::class, 'destroy'])->middleware('permission:jadwal-uts-delete')->name('jadwal-uts.destroy');

        Route::get('/jadwaluas', [JadwalUasController::class, 'index'])->middleware('permission:jadwal-uas-list')->name('jadwal-uas.index');
        Route::get('/jadwaluas/filter', [JadwalUasController::class, 'filter'])->middleware('permission:jadwal-uas-list')->name('jadwal-uas.filter');
        Route::get('/jadwaluas/create', [JadwalUasController::class, 'create'])->middleware('permission:jadwal-uas-create')->name('jadwal-uas.create');
        Route::post('/jadwaluas', [JadwalUasController::class, 'store'])->middleware('permission:jadwal-uas-create')->name('jadwal-uas.store');
        Route::get('/jadwaluas/{id}/edit', [JadwalUasController::class, 'edit'])->middleware('permission:jadwal-uas-edit')->name('jadwal-uas.edit');
        Route::post('/jadwal-uas/update/{id}', [JadwalUasController::class, 'update'])->middleware('permission:jadwal-uas-edit')->name('jadwal-uas.update');
        Route::post('/jadwal-uas/generate', [JadwalUasController::class, 'generateJadwalUAS'])->middleware('permission:jadwal-uas-generate')->name('jadwal-uas.generate');
        Route::delete('/jadwal-uas/delete/{id}', [JadwalUasController::class, 'destroy'])->middleware('permission:jadwal-uas-delete')->name('jadwal-uas.destroy');

        Route::get('/jadwal', [JadwalController::class, 'index'])->middleware('permission:jadwal-list')->name('jadwal.index');
        Route::get('/jadwal/filter', [JadwalController::class, 'filter'])->middleware('permission:jadwal-list')->name('jadwal.filter');
        Route::post('/jadwal-kuliah/update/{id}', [JadwalController::class, 'update'])->middleware('permission:jadwal-edit')->name('jadwal.update');
        Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->middleware('permission:jadwal-delete')->name('jadwal.destroy');
        Route::post('/jadwal-kuliah/generate', [JadwalController::class, 'generatejadwal'])->middleware('permission:jadwal-generate')->name('jadwal.generate');

        Route::get('/jadwal-praktik', [JadwalPraktikController::class, 'index'])->middleware('permission:jadwal-praktik-list')->name('jadwal-praktik.index');
        Route::get('/jadwal-praktik/filter', [JadwalPraktikController::class, 'filter'])->middleware('permission:jadwal-praktik-list')->name('jadwal-praktik.filter');
        Route::post('/jadwal-praktik/update/{id}', [JadwalPraktikController::class, 'update'])->middleware('permission:jadwal-praktik-edit')->name('jadwal-praktik.update');
        Route::delete('/jadwal-praktik/delete/{id}', [JadwalPraktikController::class, 'destroy'])->middleware('permission:jadwal-praktik-delete')->name('jadwal-praktik.destroy');
        Route::post('/jadwal-praktik/generate', [JadwalPraktikController::class, 'generatejadwal'])->middleware('permission:jadwal-praktik-generate')->name('jadwal-praktik.generate');

        Route::get('/jadwaluap', [JadwalUapController::class, 'index'])->middleware('permission:jadwal-uap-list')->name('jadwal-uap.index');
        Route::get('/jadwaluap/filter', [JadwalUapController::class, 'filter'])->middleware('permission:jadwal-uap-list')->name('jadwal-uap.filter');
        Route::get('/jadwaluap/create', [JadwalUapController::class, 'create'])->middleware('permission:jadwal-uap-create')->name('jadwal-uap.create');
        Route::post('/jadwaluap', [JadwalUapController::class, 'store'])->middleware('permission:jadwal-uap-create')->name('jadwal-uap.store');
        Route::get('/jadwaluap/{id}/edit', [JadwalUapController::class, 'edit'])->middleware('permission:jadwal-uap-edit')->name('jadwal-uap.edit');
        Route::put('/jadwaluap/{id}', [JadwalUapController::class, 'update'])->middleware('permission:jadwal-uap-edit')->name('jadwal-uap.update');
        Route::delete('/jadwaluap/{id}', [JadwalUapController::class, 'destroy'])->middleware('permission:jadwal-uap-delete')->name('jadwal-uap.destroy');

        Route::middleware(['permission:assign-dosen-list'])->group(function () {
            Route::get('/assign/dosen', [DosenKurikulumController::class, 'index'])->name('index.assign');
            Route::post('/assign/dosen', [DosenKurikulumController::class, 'store'])->middleware('permission:assign-dosen-create')->name('assign.dosen');
            Route::get('/assign/filter', [DosenKurikulumController::class, 'filter'])->name('assign.filter');
            Route::get('/assign/kurikulum/filter/cascade', [DosenKurikulumController::class, 'getKurikulumByFilter'])->name('assign.kurikulum.filter');
            Route::get('/assign/kurikulum/{taId}', [DosenKurikulumController::class, 'getKurikulumByTA'])->name('assign.kurikulum.byTA');
        });
        // Route untuk remove relasi dosen dan kurikulum
        Route::delete('/remove/dosen/kurikulum/{id}', [DosenKurikulumController::class, 'destroy'])->middleware('permission:assign-dosen-delete')->name('remove.dosen.kurikulum');

        Route::middleware(['permission:input-nilai'])->group(function () {
            Route::get('/input-nilai', [InputNilaiController::class, 'index'])->name('input-nilai.index');
            Route::get('/mata-kuliah/{programStudiId}/{tahunAjaranId}', [InputNilaiController::class, 'getMataKuliah'])->name('input-nilai.mata-kuliah');
            Route::get('/mahasiswa/input-nilai/{mataKuliahId}/{tahunAjaranId}', [InputNilaiController::class, 'getMahasiswa'])->name('input-nilai.mahasiswa');
            Route::post('/nilai/save', [InputNilaiController::class, 'saveNilai'])->name('nilai.save');
            Route::post('bobot-nilai', [InputNilaiController::class, 'store'])->name('bobot-nilai.store');
            Route::post('bobot-nilai/save', [InputNilaiController::class, 'saveBobotNilai'])->name('bobot-nilai.save');
        });
        Route::get('/mahasiswa/input-nilai/export/{mataKuliahId}/{tahunAjaranId}/{format}', [InputNilaiController::class, 'export'])
            ->middleware('permission:nilai-export')
            ->name('nilai.export');

        Route::middleware(['permission:list-nilai'])->group(function () {
            Route::get('/nilai', [NilaiController::class, 'index'])->name('nilai.index');
            Route::get('/api/mahasiswa-by-prodi-semester', [NilaiController::class, 'getMahasiswaByProdiSemester'])->name('nilai.mahasiswa-by-prodi-semester');
            Route::get('/api/krs-mahasiswa/{mahasiswaId}', [NilaiController::class, 'getKRSByMahasiswa'])->name('nilai.krs-mahasiswa');
        });
        Route::middleware('permission:nilai-publish')->prefix('penerbitan-khs')->name('nilai-publish.')->group(function () {
            Route::get('/', [KhsPublicationController::class, 'index'])->name('index');
            Route::post('/', [KhsPublicationController::class, 'publish'])->name('store');
            Route::delete('/{publication}', [KhsPublicationController::class, 'revoke'])->name('destroy');
        });

        Route::get('/aktivasi-mhs', [AktivasiController::class, 'index'])->middleware('permission:aktivasi-list')->name('aktivasi.index');
        Route::post('/aktivasi-mhs/update-status', [AktivasiController::class, 'updateStatus'])->middleware('permission:aktivasi-update')->name('aktivasi-mhs.updateStatus');
        Route::post('/aktivasi-mhs/bulk-update', [AktivasiController::class, 'bulkUpdateStatus'])->middleware('permission:aktivasi-bulk-update')->name('aktivasi-mhs.bulkUpdate');
        Route::post('/reset-status', [AktivasiController::class, 'resetAllStatus'])->middleware('permission:aktivasi-reset')->name('reset.all.status');

        // Manajemen KRS Admin
        Route::middleware(['permission:krs-list'])->group(function () {
            Route::get('/krs-admin', [AdminKrsController::class, 'index'])->name('krs-admin.index');
            Route::get('/krs-admin/filter', [AdminKrsController::class, 'getKrsByFilter'])->name('krs-admin.filter');
            Route::get('/krs-admin/get-mahasiswa', [AdminKrsController::class, 'getMahasiswaForKrs'])->name('krs-admin.getMahasiswa');
            Route::get('/krs-admin/get-kurikulum', [AdminKrsController::class, 'getKurikulumForKrs'])->name('krs-admin.getKurikulum');
            Route::post('/krs-admin', [AdminKrsController::class, 'store'])->middleware('permission:krs-create')->name('krs-admin.store');
            Route::post('/krs-admin/bulk', [AdminKrsController::class, 'bulkStore'])->middleware('permission:krs-create')->name('krs-admin.bulkStore');
            Route::delete('/krs-admin/{id}', [AdminKrsController::class, 'destroy'])->middleware('permission:krs-delete')->name('krs-admin.destroy');
        });
        Route::get('/arsip-krs', [KrsArchiveController::class, 'index'])
            ->middleware('permission:krs-archive-list')
            ->name('krs-archive.index');
        Route::get('/arsip-krs/download-semester', [KrsArchiveController::class, 'downloadSemester'])
            ->middleware('permission:krs-archive-export')
            ->name('krs-archive.download-semester');
        Route::get('/arsip-krs/{mahasiswa}/{tahunAkademik}/{semester}/download', [KrsArchiveController::class, 'download'])
            ->middleware('permission:krs-archive-export')
            ->whereNumber(['tahunAkademik', 'semester'])
            ->name('krs-archive.download');

        // Cek Nilai UTS/UAS Admin
        Route::middleware(['permission:list-nilai'])->group(function () {
            Route::get('/cek-nilai', [AdminCekNilaiController::class, 'index'])->name('cek-nilai.index');
            Route::get('/cek-nilai/filter', [AdminCekNilaiController::class, 'getNilaiByFilter'])->name('cek-nilai.filter');
        });

        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
        Route::get('/penilaian/get-kurikulum', [PenilaianController::class, 'getKurikulumAjax'])->name('penilaian.getKurikulum');
        Route::get('/penilaian/get-dosen', [PenilaianController::class, 'getDosenAjax'])->name('penilaian.getDosen');
        Route::get('/penilaian/detail/{dosen_id}/{kurikulum_id}/{jenis_dosen}', [PenilaianController::class, 'detail'])->name('penilaian.detail');
        Route::post('/penilaian/cetak-pdf', [PenilaianController::class, 'cetakPdf'])->name('penilaian.cetak-pdf');
        Route::get('/penilaian/cetak-registry', [PenilaianController::class, 'cetakRegistry'])->name('penilaian.cetak-registry');
        Route::post('/mahasiswa/reset-edom', [PenilaianController::class, 'resetEdom'])->name('reset.edom');
        Route::post('/mahasiswa/setup-edom', [PenilaianController::class, 'setupEdom'])->name('setup.edom');

        Route::patch('/tahun-ajaran/{id}/update-status', [TahunAkademikController::class, 'updateStatus'])->middleware('permission:tahun-ajaran-status')->name('tahun-ajaran.updateStatus');
        Route::post('/mahasiswa/{id}/reset-password', [MahasiswaController::class, 'resetPassword'])->middleware('permission:mahasiswa-reset-password')->name('resetPassword');
        Route::get('/mahasiswa/search', [MahasiswaController::class, 'search'])->middleware('permission:mahasiswa-list')->name('mahasiswa.search');

        // Route::get('/jadwal/{jadwalId}/pertemuan', [PertemuanController::class, 'index'])->name('pertemuan.index');
        // Route::post('/pertemuan/store', [PertemuanController::class, 'store'])->name('pertemuan.store');
        // Route::put('/admin/pertemuan/{pertemuan_id}/toggle', [PertemuanController::class, 'toggleStatus'])->name('pertemuan.toggleStatus');
        // Route::get('/jadwal/{jadwal_id}/pertemuan/{id}/edit', [PertemuanController::class, 'edit'])->name('pertemuan.edit');
        // Route::put('/jadwal/{jadwal_id}/pertemuan/{id}', [PertemuanController::class, 'update'])->name('pertemuan.update');
        // Route::delete('/jadwal/{jadwal_id}/pertemuan/{id}', [PertemuanController::class, 'destroy'])->name('pertemuan.destroy');

        Route::get('rekap-absensi/{jadwalId}', [AbsensiController::class, 'rekapAbsensi'])->name('rekap.absensi');

        // Tambahan route untuk absensi
        Route::get('absensi/filter', [AbsensiController::class, 'getFilteredAbsensi'])->name('absensi.filter');
        Route::post('absensi/cetak-rekap', [AbsensiController::class, 'cetakRekapMahasiswa'])->middleware('permission:absensi-export')->name('absensi.cetakRekap');
        Route::post('absensi/cetak-bap', [AbsensiController::class, 'cetakBAPDosen'])->middleware('permission:absensi-export')->name('absensi.cetakBAP');
        Route::post('absensi/jurnal-mengajar', [AbsensiController::class, 'cetakJurnalMengajar'])->middleware('permission:absensi-export')->name('absensi.jurnalMengajar');
        Route::put('absensi/update-massal', [AbsensiController::class, 'updateMassal'])->middleware('permission:absensi-edit')->name('absensi.updateMassal');
        Route::get('/absensi/{jadwal_id}/detail/{pertemuan_id}', [AbsensiController::class, 'detail'])->name('absensi.detail');
        Route::post('absensi/open/{jadwalId}', [AbsensiController::class, 'openAbsensi'])->middleware('permission:absensi-edit')->name('absensi.open');
        Route::post('absensi/close/{jadwalId}', [AbsensiController::class, 'closeAbsensi'])->middleware('permission:absensi-edit')->name('absensi.close');
        Route::post('absensi/tutup/{jadwalId}', [AbsensiController::class, 'tutupSesiAbsensi'])->name('absensi.tutupSesi');
        Route::post('absensi/buka/{jadwalId}', [AbsensiController::class, 'bukaSesiAbsensi'])->name('absensi.bukaSesi');
        Route::put('absensi/update/{absensiId}', [AbsensiController::class, 'updateStatus'])->name('absensi.updateStatus');
        Route::get('absensi/riwayat', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
        Route::get('absensi/detail/{jadwal_id}', [AbsensiController::class, 'detail'])->name('absensi.show-by-jadwal');
        Route::resource('absensi', AbsensiController::class)->only(['index', 'show']);
        Route::get('/mahasiswa/data', [MahasiswaController::class, 'getMahasiswa'])->middleware('permission:mahasiswa-list')->name('mahasiswa.data');
        Route::get('/api/berita', [BeritaController::class, 'getBerita'])->name('getBerita');

        // Import ke Excel
        Route::post('/mahasiswa/import', [MahasiswaController::class, 'importExcel'])->middleware('permission:mahasiswa-import')->name('mahasiswa.import');
        Route::get('/mahasiswa/template-mhs', [MahasiswaController::class, 'downloadTemplateNew'])->middleware('permission:mahasiswa-import')
            ->name('mahasiswa.download-template');

        // Laporan PDF
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::post('/laporan/generate-pdf', [LaporanController::class, 'generatePDF'])->name('laporan.generate-pdf');

        Route::get('/pengajuan-transkrip', [PengajuanTranskripController::class, 'indexTransrkip'])->name('transkrip.index');
        Route::get('/pengajuan/{pengajuan}/edit', [PengajuanTranskripController::class, 'edit'])->name('pengajuan.edit');
        Route::put('/pengajuan/{pengajuan}', [PengajuanTranskripController::class, 'update'])->name('pengajuan.update');
        Route::delete('/pengajuan/{pengajuan}', [PengajuanTranskripController::class, 'destroy'])->name('pengajuan.destroy');
        Route::patch('/pengajuan/{id}/update-status', [PengajuanTranskripController::class, 'updateStatus'])->name('pengajuan.updateStatus');

        Route::get('/permintaan', [PermintaanController::class, 'index'])->name('permintaan.index');
        Route::get('/permintaan/{id}/edit', [PermintaanController::class, 'edit'])->name('permintaan.edit');

        Route::get('/helpdesk/permintaan', [PermintaanController::class, 'index'])->name('helpdesk.index');
        Route::get('/helpdesk/permintaan/{id}', [PermintaanController::class, 'show'])->name('helpdesk.show');
        Route::put('/helpdesk/permintaan/{id}/status', [PermintaanController::class, 'updateStatus'])->name('helpdesk.updateStatus');

        // Surat Keterangan Pendamping Ijazah Mahasiswa Validator
        Route::get('/surat-keterangan-pendamping-ijazah', [ValidatorController::class, 'index'])->name('skpi.index');

        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi', [ValidatorController::class, 'sertifikasi'])->name('skpi.sertifikasi');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/validator', [ValidatorController::class, 'sertifikasiValidator'])->middleware('permission:skpi-sertifikasi-list')->name('skpi.sertifikasi.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/disetujui', [ValidatorController::class, 'sertifikasiDisetujui'])->name('skpi.sertifikasi.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/ditolak', [ValidatorController::class, 'sertifikasiDitolak'])->name('skpi.sertifikasi.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/ditinjau', [ValidatorController::class, 'sertifikasiDitinjau'])->name('skpi.sertifikasi.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/menunggu', [ValidatorController::class, 'sertifikasiMenunggu'])->name('skpi.sertifikasi.menunggu');
        Route::put('/sertifikasi/{id}/catatan', [ValidatorController::class, 'updateCatatan'])->name('sertifikasi.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/bahasa', [ValidatorController::class, 'bahasa'])->name('skpi.bahasa');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/validator', [ValidatorController::class, 'bahasaValidator'])->middleware('permission:skpi-bahasa-list')->name('skpi.bahasa.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/disetujui', [ValidatorController::class, 'bahasaDisetujui'])->name('skpi.bahasa.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/ditolak', [ValidatorController::class, 'bahasaDitolak'])->name('skpi.bahasa.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/ditinjau', [ValidatorController::class, 'bahasaDitinjau'])->name('skpi.bahasa.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/menunggu', [ValidatorController::class, 'bahasaMenunggu'])->name('skpi.bahasa.menunggu');
        Route::put('/penguasaan-bahasa-asing/{id}/catatan', [ValidatorController::class, 'updateCatatanBahasa'])->name('bahasa.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha', [ValidatorController::class, 'wirausaha'])->name('skpi.wirausaha');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/validator', [ValidatorController::class, 'wirausahaValidator'])->middleware('permission:skpi-wirausaha-list')->name('skpi.wirausaha.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/disetujui', [ValidatorController::class, 'wirausahaDisetujui'])->name('skpi.wirausaha.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/ditolak', [ValidatorController::class, 'wirausahaDitolak'])->name('skpi.wirausaha.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/ditinjau', [ValidatorController::class, 'wirausahaDitinjau'])->name('skpi.wirausaha.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/menunggu', [ValidatorController::class, 'wirausahaMenunggu'])->name('skpi.wirausaha.menunggu');
        Route::put('/wirausaha/{id}/catatan', [ValidatorController::class, 'updateCatatanWirausaha'])->name('wirausaha.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/pkm', [ValidatorController::class, 'pkm'])->name('skpi.pkm');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/validator', [ValidatorController::class, 'pkmValidator'])->middleware('permission:skpi-pkm-list')->name('skpi.pkm.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/disetujui', [ValidatorController::class, 'pkmDisetujui'])->name('skpi.pkm.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/ditolak', [ValidatorController::class, 'pkmDitolak'])->name('skpi.pkm.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/ditinjau', [ValidatorController::class, 'pkmDitinjau'])->name('skpi.pkm.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/menunggu', [ValidatorController::class, 'pkmMenunggu'])->name('skpi.pkm.menunggu');
        Route::put('/pkm/{id}/catatan', [ValidatorController::class, 'updateCatatanPkm'])->name('pkm.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/ppsm', [ValidatorController::class, 'ppsm'])->name('skpi.ppsm');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/validator', [ValidatorController::class, 'ppsmValidator'])->middleware('permission:skpi-ppsm-list')->name('skpi.ppsm.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/disetujui', [ValidatorController::class, 'ppsmDisetujui'])->name('skpi.ppsm.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/ditolak', [ValidatorController::class, 'ppsmDitolak'])->name('skpi.ppsm.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/ditinjau', [ValidatorController::class, 'ppsmDitinjau'])->name('skpi.ppsm.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/menunggu', [ValidatorController::class, 'ppsmMenunggu'])->name('skpi.ppsm.menunggu');
        Route::put('/ppsm/{id}/catatan', [ValidatorController::class, 'updateCatatanPpsm'])->name('ppsm.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/tambahan', [ValidatorController::class, 'tambahan'])->name('skpi.tambahan');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/validator', [ValidatorController::class, 'tambahanValidator'])->middleware('permission:skpi-tambahan-list')->name('skpi.tambahan.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/disetujui', [ValidatorController::class, 'tambahanDisetujui'])->name('skpi.tambahan.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/ditolak', [ValidatorController::class, 'tambahanDitolak'])->name('skpi.tambahan.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/ditinjau', [ValidatorController::class, 'tambahanDitinjau'])->name('skpi.tambahan.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/menunggu', [ValidatorController::class, 'tambahanMenunggu'])->name('skpi.tambahan.menunggu');
        Route::put('/tambahan/{id}/catatan', [ValidatorController::class, 'updateCatatanTambahan'])->name('tambahan.catatan');

        Route::get('/input-uap', [UapNilaiController::class, 'index'])->name('uap.index');
        Route::get('/get-mahasiswa-uap', [UapNilaiController::class, 'getMahasiswa'])->name('uap.getMahasiswa');
        Route::post('/simpan-nilai-uap', [UapNilaiController::class, 'simpanNilai'])->name('uap.simpanNilai');

    });
});

Route::prefix('dosen')->name('dosen.')->group(function () {
    Route::get('login', [LoginDosenController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginDosenController::class, 'login'])
        ->middleware(['login.progressive:dosen', 'throttle:login-ip'])->name('mhs.login');
    Route::post('logout', [LoginDosenController::class, 'logout'])->name('logout');
    Route::middleware('auth:dosen')->group(function () {
        Route::get('dashboard', [DashboardDosenController::class, 'index'])->name('dashboard');
        Route::get('/kalender-akademik/{calendar}/file', [CalendarAkademikFileController::class, 'show'])
            ->name('calendar-akademik.file');
        // Profil mahasiswa
        Route::middleware(['auth:dosen'])->group(function () {
            // ===== Perkuliahan =====

            // Jadwal Mengajar
            Route::get('/jadwal', [PerkuliahanDosenController::class, 'jadwalIndex'])->name('jadwal.index');
            Route::get('/jadwal/search', [PerkuliahanDosenController::class, 'search'])->name('jadwal.search');
            Route::get('/praktik/search', [PerkuliahanDosenController::class, 'searchPraktik'])->name('praktik.search');

            // Jadwal Mengajar
            Route::get('/jadwal-praktik', [PerkuliahanDosenController::class, 'PraktikIndex'])->name('jadwal-praktik.index');
            Route::get('/jadwal-praktik/search', [PerkuliahanDosenController::class, 'searchPraktik'])->name('jadwal-praktik.search');

            // Absensi
            Route::get('/absensi', [PerkuliahanDosenController::class, 'indexAbsensi'])->name('absensi.index');
            Route::get('/absensi/filter', [PerkuliahanDosenController::class, 'getFilteredAbsensi'])->name('absensi.filter');
            Route::get('/absensi/search', [PerkuliahanDosenController::class, 'searchAbsen'])->name('absensi.search');

            // Teori Absensi
            Route::post('/absensi/pertemuan', [PerkuliahanDosenController::class, 'storePertemuan'])->name('absensi.store');
            Route::get('/pertemuan/list/{jadwal_id}', [PerkuliahanDosenController::class, 'listPertemuan'])->name('pertemuan.list');
            Route::get('/absensi/buat/{pertemuan_id}', [PerkuliahanDosenController::class, 'lihat'])->name('absensi.create');
            Route::post('/absensi/store', [PerkuliahanDosenController::class, 'store'])->name('absensi-store');

            // Praktik Absensi
            Route::get('/absensi-praktik', [DosenAbsensiPraktikController::class, 'index'])->name('absensi-praktik.index');
            Route::post('/absensi-praktik/pertemuan-baru', [DosenAbsensiPraktikController::class, 'storePertemuan'])->name('absensi-praktik.pertemuan.store');
            Route::get('/absensi-praktik/pertemuan/{pertemuan}', [DosenAbsensiPraktikController::class, 'show'])->name('absensi-praktik.show');
            Route::put('/absensi-praktik/pertemuan/{pertemuan}', [DosenAbsensiPraktikController::class, 'update'])->name('absensi-praktik.update');
            Route::post('/absensi-praktik/pertemuan', [PerkuliahanDosenController::class, 'storePertemuanPraktik'])->name('absensi-praktik.store');
            Route::get('/pertemuan-praktik/list/{jadwal_praktik_id}', [PerkuliahanDosenController::class, 'listPertemuanPraktik'])->name('pertemuan-praktik.list');
            Route::get('/absensi-praktik/buat/{pertemuan_praktik_id}', [PerkuliahanDosenController::class, 'lihatPraktik'])->name('absensi-praktik.create');
            Route::post('/absensi-praktik/store', [PerkuliahanDosenController::class, 'storePraktik'])->name('absensi-praktik-store');

            // Cetak Absensi Teori
            Route::get('/absensi/cetak', [LaporanAbsensiController::class, 'index'])->name('absensi.cetak.index');
            Route::post('/absensi/generate-pdf', [LaporanAbsensiController::class, 'generatePDF'])->name('absensi.generate-pdf');
            Route::post('/pertemuan/generate-pdf', [LaporanAbsensiController::class, 'generatePertemuan'])->name('generate-pdf');

            // Cetak Absensi Praktik
            Route::post('/absensi-praktik/generate-pdf', [LaporanAbsensiController::class, 'generatePraktikPDF'])->name('absensi-praktik.generate-pdf');
            Route::post('/pertemuan-praktik/generate-pdf', [LaporanAbsensiController::class, 'generatePraktikPertemuan'])->name('praktik.generate-pdf');

            // Input Nilai
            // Route::get('/nilai/input', [ModulAkademikController::class, 'inputNilai'])->name('nilai.input');
            // Route::get('/nilai-dosen/input', [ModulAkademikController::class, 'index'])->name('doseninput.index');
            // Route::get('/mata-kuliah/{programStudiId}/{tahunAjaranId}', [ModulAkademikController::class, 'getMataKuliah']);
            // Route::get('/mahasiswa/input-nilai/{mataKuliahId}/{tahunAjaranId}', [ModulAkademikController::class, 'getMahasiswa']);
            // Route::post('/nilai/save', [ModulAkademikController::class, 'saveNilaiDosen'])->name('nilai.save');
            Route::get('/nilai/input', [ModulAkademikController::class, 'index'])->name('nilai-dosen.input');
            // Route::get('/input-nilai', [InputNilaiController::class, 'index'])->name('input-nilai.index');
            Route::get('/mata-kuliah/{programStudiId}/{tahunAjaranId}', [ModulAkademikController::class, 'getMataKuliahDosen'])->name('mata-kuliah.by-filter');
            Route::get('/input-nilai-dosen/jadwal/{jadwal}', [ModulAkademikController::class, 'getMahasiswaDosen'])->name('input-nilai-dosen.mahasiswa');
            Route::post('/nilai-mahasiswa/save', [ModulAkademikController::class, 'saveNilaiDosen'])->name('nilai.save');
            Route::post('/bobot-nilai/save', [ModulAkademikController::class, 'saveBobotNilai'])->name('bobot-nilai.save');
            Route::get('/mahasiswa-bimbingan', [ModulAkademikController::class, 'lihatMahasiswaDosen'])->name('nilai-dosen.lihat');
            Route::get('/kurikulum-krs', [KurikulumKrsController::class, 'index'])->name('kurikulum-krs.index');
            Route::get('/mahasiswa-bimbingan/{mahasiswa}/krs', [ModulAkademikController::class, 'lihatKrsMahasiswa'])
                ->name('mahasiswa.krs.show');
            Route::post('/mahasiswa-bimbingan/{mahasiswa}/komentar', [ModulAkademikController::class, 'storeGuidanceComment'])
                ->name('mahasiswa.guidance.store');
            Route::post('/mahasiswa-bimbingan/{mahasiswa}/krs/acc', [ModulAkademikController::class, 'approveKrs'])
                ->name('mahasiswa.krs.approve');
            Route::post('/mahasiswa-bimbingan/{mahasiswa}/krs/batalkan-acc', [ModulAkademikController::class, 'cancelKrsApproval'])
                ->name('mahasiswa.krs.cancel-approval');
            Route::post('/mahasiswa-bimbingan/krs/bulk-acc', [ModulAkademikController::class, 'bulkApproveKrs'])
                ->name('mahasiswa.krs.bulk-approve');
            Route::get('/mahasiswa-bimbingan/{mahasiswa}/transkrip', [ModulAkademikController::class, 'transkripMahasiswa'])->name('mahasiswa.transkrip');
            // ===== Modul Akademik =====
            Route::get('/edom/hasil', [DashboardDosenController::class, 'hasilEdom'])->name('edom.hasil');

            Route::prefix('kaprodi')->name('kaprodi.')->group(function () {
                Route::get('/monitoring', [KaprodiVerificationController::class, 'monitoring'])->name('monitoring.index');
                Route::get('/absensi', [KaprodiVerificationController::class, 'absensi'])->name('absensi.index');
                Route::post('/absensi/verifikasi-massal', [KaprodiVerificationController::class, 'bulkVerifyAbsensi'])->name('absensi.bulk-verify');
                Route::get('/absensi/{jadwal}', [KaprodiVerificationController::class, 'absensiDetail'])->name('absensi.show');
                Route::post('/absensi/{jadwal}/verifikasi', [KaprodiVerificationController::class, 'verifyAbsensi'])->name('absensi.verify');
                Route::get('/nilai', [KaprodiVerificationController::class, 'nilai'])->name('nilai.index');
                Route::post('/nilai/setujui-massal', [KaprodiVerificationController::class, 'bulkApproveNilai'])->name('nilai.bulk-approve');
                Route::get('/nilai/{submission}', [KaprodiVerificationController::class, 'nilaiDetail'])->name('nilai.show');
                Route::post('/nilai/{submission}/setujui', [KaprodiVerificationController::class, 'approveNilai'])->name('nilai.approve');
                Route::post('/nilai/{submission}/revisi', [KaprodiVerificationController::class, 'revisionNilai'])->name('nilai.revision');
            });

            // Materi Kuliah
            Route::get('/materi', [ModulAkademikController::class, 'indexRps'])->name('materi.index');
            Route::get('/materi/create', [ModulAkademikController::class, 'tambahRps'])->name('materi.create');

            Route::get('/api/berita', [BeritaController::class, 'getBerita'])->name('getBerita');
            Route::get('/api/index/berita', [BeritaController::class, 'index'])->name('index.berita');
            Route::get('/api/berita-kampus', [BeritaController::class, 'getBeritaKampus'])->name('getBeritaKampus');
            Route::get('/berita/{id}', [BeritaController::class, 'getDetailBeritaDosen'])->name('berita.detail');
            Route::get('profile', [ProfileDosenController::class, 'index'])->name('profile.index');
            Route::post('profile', [ProfileDosenController::class, 'update'])->name('profile.update');
            Route::get('/permintaan', [DosenPermintaanController::class, 'index'])->name('permintaan.index');
            Route::get('/permintaan/create', [DosenPermintaanController::class, 'create'])->name('permintaan.create');
            Route::post('/permintaan', [DosenPermintaanController::class, 'store'])->name('permintaan.store');
            Route::delete('/permintaan/{id}', [DosenPermintaanController::class, 'destroy'])->name('permintaan.destroy');
            Route::prefix('lms')->name('lms.')->group(function () {

                Route::get('/', [LmsDosenController::class, 'index'])->name('index');
                Route::post('/calendar/notes', [LmsCalendarNoteController::class, 'store'])->name('calendar.notes.store');
                Route::put('/calendar/notes/{note}', [LmsCalendarNoteController::class, 'update'])->name('calendar.notes.update');
                Route::delete('/calendar/notes/{note}', [LmsCalendarNoteController::class, 'destroy'])->name('calendar.notes.destroy');

                Route::get('/gradebook/{jadwal}', [LmsDosenController::class, 'gradebook'])->name('gradebook');
                Route::post('/gradebook/{jadwal}/sync-khs', [LmsDosenController::class, 'syncGradebook'])->name('gradebook.sync-khs');
                Route::get('/gradebook/{jadwal}/excel', [LmsDosenController::class, 'exportGradebookExcel'])->name('gradebook.excel');
                Route::get('/gradebook/{jadwal}/pdf', [LmsDosenController::class, 'exportGradebookPdf'])->name('gradebook.pdf');
                Route::get('/{jadwal}/kelola', [LmsDosenController::class, 'kelola'])->name('kelola');
                Route::get('/{jadwal}/quiz', [QuizDosenController::class, 'index'])->name('quiz.index');
                Route::post('/{jadwal}/quiz', [QuizDosenController::class, 'store'])->name('quiz.store');
                Route::get('/quiz/{quiz}/kelola', [QuizDosenController::class, 'manage'])->name('quiz.manage');
                Route::put('/quiz/{quiz}', [QuizDosenController::class, 'update'])->name('quiz.update');
                Route::delete('/quiz/{quiz}', [QuizDosenController::class, 'destroy'])->name('quiz.destroy');
                Route::post('/quiz/{quiz}/soal', [QuizDosenController::class, 'storeSoal'])->name('quiz.soal.store');
                Route::put('/quiz/soal/{soal}', [QuizDosenController::class, 'updateSoal'])->name('quiz.soal.update');
                Route::delete('/quiz/soal/{soal}', [QuizDosenController::class, 'destroySoal'])->name('quiz.soal.destroy');
                Route::get('/quiz/{quiz}/hasil', [QuizDosenController::class, 'hasil'])->name('quiz.hasil');
                Route::put('/quiz/attempt/{attempt}/nilai', [QuizDosenController::class, 'nilai'])->name('quiz.attempt.nilai');
                Route::put('/quiz/attempt/{attempt}/izinkan-ulang', [QuizDosenController::class, 'izinkanUlang'])->name('quiz.attempt.izinkan-ulang');
                Route::get('/quiz/jawaban/{jawaban}/download', [QuizDosenController::class, 'downloadJawaban'])->name('quiz.jawaban.download');

                Route::post('/materi/store', [LmsDosenController::class, 'storeMateri'])
                    ->name('materi.store');
                Route::get('/materi/{materi}/download', [LmsDosenController::class, 'downloadMateri'])
                    ->name('materi.download');

                Route::delete('/materi/{materi}', [LmsDosenController::class, 'destroyMateri'])
                    ->name('materi.destroy');
                Route::get('/materi/{materi}/preview', [LmsDosenController::class, 'previewMateri'])
                    ->name('materi.preview');
                Route::put('/materi/{materi}', [LmsDosenController::class, 'updateMateri'])
                    ->name('materi.update');
                Route::post('/tugas/store', [LmsDosenController::class, 'storeTugas'])
                    ->name('tugas.store');

                Route::put('/tugas/{tugas}', [LmsDosenController::class, 'updateTugas'])
                    ->name('tugas.update');
                Route::get('/tugas/{tugas}/soal', [LmsDosenController::class, 'manageTugasSoal'])
                    ->name('tugas.soal.manage');
                Route::post('/tugas/{tugas}/soal', [LmsDosenController::class, 'storeTugasSoal'])
                    ->name('tugas.soal.store');
                Route::put('/tugas-soal/{soal}', [LmsDosenController::class, 'updateTugasSoal'])
                    ->name('tugas.soal.update');
                Route::delete('/tugas-soal/{soal}', [LmsDosenController::class, 'destroyTugasSoal'])
                    ->name('tugas.soal.destroy');

                Route::delete('/tugas/{tugas}', [LmsDosenController::class, 'destroyTugas'])
                    ->name('tugas.destroy');
                Route::get(
                    '/tugas/{tugas}/pengumpulan',
                    [LmsDosenController::class, 'pengumpulanTugas']
                )->name('tugas.pengumpulan');

                Route::get(
                    '/pengumpulan/{pengumpulan}/preview',
                    [LmsDosenController::class, 'previewPengumpulan']
                )->name('pengumpulan.preview');
                Route::get(
                    '/pengumpulan/{pengumpulan}/download',
                    [LmsDosenController::class, 'downloadPengumpulan']
                )->name('pengumpulan.download');
                Route::put(
                    '/pengumpulan/{pengumpulan}/nilai',
                    [LmsDosenController::class, 'nilaiPengumpulan']
                )->name('pengumpulan.nilai');

            });

            // RPS
            Route::prefix('rps')->name('rps.')->group(function () {

                Route::get('/', [RpsDosenController::class, 'index'])
                    ->name('index');

                Route::post('/upload', [RpsDosenController::class, 'store'])
                    ->name('store');
                Route::get('/{rps}/lihat', [RpsDosenController::class, 'show'])
                    ->name('show');

                Route::delete('/{id}', [RpsDosenController::class, 'destroy'])
                    ->name('destroy');

            });
        });
    });
});
