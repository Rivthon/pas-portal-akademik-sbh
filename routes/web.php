<?php

use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DosenController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\TarifController;
use App\Http\Controllers\BeritaController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\AktivasiController;
use App\Http\Controllers\EvaluasiController;
use App\Http\Controllers\GelombangController;
use App\Http\Controllers\JadwaluapController;
use App\Http\Controllers\JadwaluasController;
use App\Http\Controllers\JadwalutsController;
use App\Http\Controllers\KurikulumController;
use App\Http\Controllers\MahasiswaController;
use App\Http\Controllers\PenilaianController;
use App\Http\Controllers\PertemuanController;
use App\Http\Controllers\ValidatorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\InputNilaiController;
use App\Http\Controllers\MatakuliahController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\ProgramStudiController;
use App\Http\Controllers\JadwalPraktikController;
use App\Http\Controllers\TahunAkademikController;
use App\Http\Controllers\DosenKurikulumController;
use App\Http\Controllers\Mahasiswa\EdomController;
use App\Http\Controllers\Mahasiswa\SkpiController;
use App\Http\Controllers\Mahasiswa\UjianController;
use App\Http\Controllers\TenorPembayaranController;
use App\Http\Controllers\CalenderAkademikController;
use App\Http\Controllers\Dosen\LoginDosenController;
use App\Http\Controllers\TagihanMahasiswaController;
use App\Http\Controllers\Dosen\ProfileDosenController;
use App\Http\Controllers\Mahasiswa\AkademikController;
use App\Http\Controllers\PengajuanTranskripController;
use App\Http\Controllers\Auth\MahasiswaLoginController;
use App\Http\Controllers\Dosen\ModulAkademikController;
use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Dosen\DashboardDosenController;
use App\Http\Controllers\Dosen\LaporanAbsensiController;
use App\Http\Controllers\Mahasiswa\PerkuliahanController;
use App\Http\Controllers\Mahasiswa\PerminataanController;
use App\Http\Controllers\Mahasiswa\ProfileUserController;
use App\Http\Controllers\Dosen\PerkuliahanDosenController;
use App\Http\Controllers\Mahasiswa\AdministrasiController;
use App\Http\Controllers\Mahasiswa\JadwalKuliahController;

Route::get('/', function () {
    if (Auth::guard('mahasiswa')->check()) {
        return redirect()->route('mahasiswa.dashboard');
    }
    return redirect('/mahasiswa/login');
});

// Routes untuk login mahasiswa
Route::prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        Route::get('login', [MahasiswaLoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [MahasiswaLoginController::class, 'login'])->name('mhs.login');
        Route::post('logout', [MahasiswaLoginController::class, 'logout'])->name('logout');
        Route::middleware('auth:mahasiswa')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
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

        Route::get('/cetak-kartu-uts', [PerkuliahanController::class, 'cetakUts'])->name('cetak.kartu.uts');
        Route::get('/cetak-kartu-uas', [PerkuliahanController::class, 'cetakUas'])->name('cetak.kartu.uas');
        Route::get('/cetak-kartu-uap', [PerkuliahanController::class, 'cetakUap'])->name('cetak.kartu.uap');

        Route::post('/krs/simpan', [AkademikController::class, 'nyimpenKrs'])->name('simpan.krs');
        Route::get('/krs', [AkademikController::class, 'index'])->name('krs.index');
        Route::get('/status-krs', [AkademikController::class, 'tampilkanKrs'])->name('status.krs.index');
        Route::delete('/krs/{id}/hapus', [AkademikController::class, 'hapusKrs'])->name('hapus.krs');

        Route::get('/mahasiswa/khs', [AkademikController::class, 'tampilanKhs'])->name('khs.index');
        Route::get('/khs/cetak-pdf', [AkademikController::class, 'cetakKhs'])->name('khs.cetak');

        Route::get('/krs/cetak-pdf-kapro', [AkademikController::class, 'cetakKapro'])->name('krs.cetak-kapro');
        Route::get('/krs/cetak-pdf-baak', [AkademikController::class, 'cetakBaak'])->name('krs.cetak-krs-baak');
        Route::get('/krs/cetak-pdf-dospem', [AkademikController::class, 'cetakDospem'])->name('krs.cetak-krs-dospem');
        Route::get('/pengajuan/cetak-transkrip', [AkademikController::class, 'cetakTranskrip'])->name('krs.cetak-transkrip');

        Route::get('/mahasiswa/nilai-uts', [UjianController::class, 'tampikanNilaiUts'])->name('nilai-uts.index');
        Route::get('/mahasiswa/nilai-uas', [UjianController::class, 'tampikanNilaiUas'])->name('nilai-uas.index');
        Route::get('/mahasiswa/nilai-akhir', [UjianController::class, 'tampikanNilaiAkhir'])->name('nilai-akhir.index');

        Route::get('/mahasiswa/edom', [EdomController::class, 'index'])->name('edom.index');
        Route::get('/mahasiswa/edom/form/{krs_id}', [EdomController::class, 'form'])->name('edom.form');
        Route::post('/edom/submit/{krs_id}/{dosen_id}', [EdomController::class, 'submit'])->name('edom.submit');
        Route::post('/mahasiswa/edom/konfirmasi', [EdomController::class, 'konfirmasiEdom'])->name('edom.konfirmasi');
        //Absensi
        Route::get('jadwal/{jadwalId}/absensi', [App\Http\Controllers\Mahasiswa\AbsensiController::class, 'index'])->name('absensi.index');
        Route::post('absensi', [App\Http\Controllers\Mahasiswa\AbsensiController::class, 'store'])->name('absensi.store');

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
        Route::get('/permintaan', [PerminataanController::class, 'index'])->name('permintaan.index');
        Route::get('/permintaan/create', [PerminataanController::class, 'create'])->name('permintaan.create');
        Route::post('/permintaan', [PerminataanController::class, 'store'])->name('permintaan.store');
        Route::delete('/permintaan/{id}', [PerminataanController::class, 'destroy'])->name('permintaan.destroy');
    });
});

Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login']);
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');
        Route::middleware('auth')->group(function () {
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
        Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Resource controllers
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::resource('products', ProductController::class);
        Route::resource('program-studi', ProgramStudiController::class);
        Route::resource('mahasiswa', MahasiswaController::class);
        Route::put('/mahasiswa/{id}/update-status', [MahasiswaController::class, 'updateStatus'])->name('mahasiswa.updateStatus');

        Route::resource('matakuliah', MatakuliahController::class);
        Route::resource('absensi', AbsensiController::class);
        Route::resource('dosen', DosenController::class);
        Route::resource('tahun-ajaran', TahunAkademikController::class);
        Route::resource('evaluasi', EvaluasiController::class);
        Route::resource('ruangan', RuanganController::class);
        Route::resource('kurikulum', KurikulumController::class);
        Route::resource('tarif', TarifController::class);
        Route::post('/tarif/import', [TarifController::class, 'import'])->name('tarif.import');
        Route::get('/tarif/import/template', [TarifController::class, 'downloadTemplate'])->name('tarif.download-template');
        Route::resource('gelombang', GelombangController::class);

        Route::get('/calender', [CalenderAkademikController::class, 'index'])->name('calender.index');
        Route::get('/calender/{id}/edit', [CalenderAkademikController::class, 'edit'])->name('calender.edit');
        Route::put('/calender/{id}', [CalenderAkademikController::class, 'update'])->name('calender.update');

        Route::get('tenor-pembayaran', [TenorPembayaranController::class, 'index'])->name('tenor-pembayaran.index');
        Route::get('tenor-pembayaran/create', [TenorPembayaranController::class, 'create'])->name('tenor-pembayaran.create');
        Route::post('tenor-pembayaran', [TenorPembayaranController::class, 'store'])->name('tenor-pembayaran.store');
        Route::get('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'show'])->name('tenor-pembayaran.show');
        Route::get('tenor-pembayaran/{id}/edit', [TenorPembayaranController::class, 'edit'])->name('tenor-pembayaran.edit');
        Route::put('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'update'])->name('tenor-pembayaran.update');
        Route::delete('tenor-pembayaran/{id}', [TenorPembayaranController::class, 'destroy'])->name('tenor-pembayaran.destroy');


        Route::get('/', [TagihanMahasiswaController::class, 'index'])->name('tagihan-mahasiswa.index');
        Route::get('/tambah-tagihan', [TagihanMahasiswaController::class, 'create'])->name('tagihan-mahasiswa.create');
        Route::post('/tambah-tagihan', [TagihanMahasiswaController::class, 'store'])->name('tagihan-mahasiswa.store');
        Route::get('/edit-tagihan/{id}', [TagihanMahasiswaController::class, 'edit'])->name('tagihan-mahasiswa.edit');
        Route::put('/update-tagihan/{id}', [TagihanMahasiswaController::class, 'update'])->name('tagihan-mahasiswa.update');
        Route::delete('/delete-tagihan/{id}', [TagihanMahasiswaController::class, 'destroy'])->name('tagihan-mahasiswa.destroy');
        Route::get('/get-mahasiswa/tagihan', [TagihanMahasiswaController::class, 'getMahasiswa'])->name('get.mahasiswa');
        Route::get('/search-tagihan', [TagihanMahasiswaController::class, 'searchTagihan'])->name('search.tagihan');
        Route::post('/tagihan-mahasiswa/{id}/update-pembayaran', [TagihanMahasiswaController::class, 'updatePembayaran'])->name('simpanPembayaran');

        // Generate tagihan otomatis untuk mahasiswa aktif
        Route::post('/generate-tagihan', [TagihanMahasiswaController::class, 'generateTagihan'])->name('generate');

        // Menampilkan detail tagihan mahasiswa tertentu
        Route::get('/detail/{id}', [TagihanMahasiswaController::class, 'show'])->name('show');


        Route::get('/mata-kuliah/filter', [KurikulumController::class, 'filter'])->middleware('permission:kurikulum-list')->name('kurikulum.filter');


        Route::get('/jadwaluts', [JadwalutsController::class, 'index'])->middleware('permission:jadwal-uts-list')->name('jadwal-uts.index');
        Route::get('/jadwaluts/filter', [JadwalutsController::class, 'filter'])->middleware('permission:jadwal-uts-list')->name('jadwal-uts.filter');
        Route::post('/jadwal-uts/generate', [JadwalutsController::class, 'generateJadwalUTS'])->name('jadwal-uts.generate');
        Route::get('/jadwaluts/create', [JadwalutsController::class, 'create'])->middleware('permission:jadwal-uts-create')->name('jadwal-uts.create');
        Route::post('/jadwaluts', [JadwalutsController::class, 'store'])->middleware('permission:jadwal-uts-create')->name('jadwal-uts.store');
        Route::get('/jadwaluts/{id}/edit', [JadwalutsController::class, 'edit'])->middleware('permission:jadwal-uts-edit')->name('jadwal-uts.edit');
        Route::post('/jadwal-uts/update/{id}', [JadwalutsController::class, 'update'])->name('jadwal-uts.update');
        Route::delete('/jadwal-uts/delete/{id}', [JadwalutsController::class, 'destroy'])->middleware('permission:jadwal-uts-delete')->name('jadwal-uts.destroy');

        Route::get('/jadwaluas', [JadwaluasController::class, 'index'])->middleware('permission:jadwal-uas-list')->name('jadwal-uas.index');
        Route::get('/jadwaluas/filter', [JadwaluasController::class, 'filter'])->middleware('permission:jadwal-uas-list')->name('jadwal-uas.filter');
        Route::get('/jadwaluas/create', [JadwaluasController::class, 'create'])->middleware('permission:jadwal-uas-create')->name('jadwal-uas.create');
        Route::post('/jadwaluas', [JadwaluasController::class, 'store'])->middleware('permission:jadwal-uas-create')->name('jadwal-uas.store');
        Route::get('/jadwaluas/{id}/edit', [JadwaluasController::class, 'edit'])->middleware('permission:jadwal-uas-edit')->name('jadwal-uas.edit');
        Route::post('/jadwal-uas/update/{id}', [JadwaluasController::class, 'update'])->name('jadwal-uas.update');
        Route::post('/jadwal-uas/generate', [JadwaluasController::class, 'generateJadwalUAS'])->name('jadwal-uas.generate');
        Route::delete('/jadwal-uas/delete/{id}', [JadwaluasController::class, 'destroy'])->middleware('permission:jadwal-uas-delete')->name('jadwal-uas.destroy');

        Route::get('/jadwal', [JadwalController::class, 'index'])->middleware('permission:jadwal-list')->name('jadwal.index');
        Route::get('/jadwal/filter', [JadwalController::class, 'filter'])->middleware('permission:jadwal-list')->name('jadwal.filter');
        Route::post('/jadwal-kuliah/update/{id}', [JadwalController::class, 'update'])->name('jadwal.update');
        Route::delete('/jadwal/{id}', [JadwalController::class, 'destroy'])->middleware('permission:jadwal-delete')->name('jadwal.destroy');
        Route::post('/jadwal-kuliah/generate', [JadwalController::class, 'generatejadwal'])->name('jadwal.generate');

        Route::get('/jadwal-praktik', [JadwalPraktikController::class, 'index'])->middleware('permission:jadwal-praktik-list')->name('jadwal-praktik.index');
        Route::get('/jadwal-praktik/filter', [JadwalPraktikController::class, 'filter'])->middleware('permission:jadwal-praktik-list')->name('jadwal-praktik.filter');
        Route::post('/jadwal-praktik/update/{id}', [JadwalPraktikController::class, 'update'])->name('jadwal-praktik.update');
        Route::delete('/jadwal-praktik/delete/{id}', [JadwalPraktikController::class, 'destroy'])->middleware('permission:jadwal-praktik-delete')->name('jadwal-praktik.destroy');
        Route::post('/jadwal-praktik/generate', [JadwalPraktikController::class, 'generatejadwal'])->name('jadwal-praktik.generate');

        Route::get('/jadwaluap', [JadwaluapController::class, 'index'])->middleware('permission:jadwal-uap-list')->name('jadwal-uap.index');
        Route::get('/jadwaluap/filter', [JadwaluapController::class, 'filter'])->middleware('permission:jadwal-uap-list')->name('jadwal-uap.filter');
        Route::get('/jadwaluap/create', [JadwaluapController::class, 'create'])->middleware('permission:jadwal-uap-create')->name('jadwal-uap.create');
        Route::post('/jadwaluap', [JadwaluapController::class, 'store'])->middleware('permission:jadwal-uap-create')->name('jadwal-uap.store');
        Route::get('/jadwaluap/{id}/edit', [JadwaluapController::class, 'edit'])->middleware('permission:jadwal-uap-edit')->name('jadwal-uap.edit');
        Route::put('/jadwaluap/{id}', [JadwaluapController::class, 'update'])->middleware('permission:jadwal-uap-edit')->name('jadwal-uap.update');
        Route::delete('/jadwaluap/{id}', [JadwaluapController::class, 'destroy'])->middleware('permission:jadwal-uap-delete')->name('jadwal-uap.destroy');


        Route::middleware(['permission:assign-dosen-list'])->group(function () {
        Route::get('/admin/assign/dosen', [DosenKurikulumController::class, 'index'])->name('index.assign');
        Route::post('/admin/assign/dosen', [DosenKurikulumController::class, 'store'])->name('assign.dosen');
        Route::get('/admin/assign/filter', [DosenKurikulumController::class, 'filter'])->name('admin.assign.filter');

          });
        // Route untuk remove relasi dosen dan kurikulum
        Route::delete('/admin/remove/dosen/kurikulum/{id}', [DosenKurikulumController::class, 'destroy'])->name('remove.dosen.kurikulum');
        // ->middleware('permission:dosen-kurikulum-delete');

        Route::middleware(['permission:input-nilai'])->group(function () {
        Route::get('/input-nilai', [InputNilaiController::class, 'index'])->name('input-nilai.index');
        Route::get('/mata-kuliah/{programStudiId}/{tahunAjaranId}', [InputNilaiController::class, 'getMataKuliah']);
        Route::get('/mahasiswa/input-nilai/{mataKuliahId}/{tahunAjaranId}', [InputNilaiController::class, 'getMahasiswa']);
        Route::post('/nilai/save', [InputNilaiController::class, 'saveNilai'])->name('nilai.save');
          });

        Route::middleware(['permission:list-nilai'])->group(function () {
        Route::get('/nilai', [NilaiController::class,  'index'])->name('nilai.index');
        Route::get('/api/mahasiswa-by-prodi-semester', [NilaiController::class, 'getMahasiswaByProdiSemester']);
        Route::get('/api/krs-mahasiswa/{mahasiswaId}', [NilaiController::class, 'getKRSByMahasiswa']);
         });

        Route::middleware(['permission:list-aktivasi'])->group(function () {
        Route::get('/aktivasi-mhs', [AktivasiController::class,'index'])->name('aktivasi.index');
        Route::post('/aktivasi-mhs/update-status', [AktivasiController::class, 'updateStatus'])->name('aktivasi-mhs.updateStatus');
        Route::post('/reset-status', [AktivasiController::class, 'resetAllStatus'])->name('reset.all.status');
        });
        Route::get('/penilaian', [PenilaianController::class, 'index'])->name('penilaian.index');
        Route::post('/penilaian/filter', [PenilaianController::class, 'filter'])->name('penilaian.filter');
        Route::post('/mahasiswa/reset-edom', [PenilaianController::class, 'resetEdom'])->name('reset.edom');
        Route::post('/mahasiswa/setup-edom', [PenilaianController::class, 'setupEdom'])->name('setup.edom');


        Route::patch('/tahun-ajaran/{id}/update-status', [TahunAkademikController::class, 'updateStatus'])->name('tahun-ajaran.updateStatus');
        Route::post('/mahasiswa/{id}/reset-password', [MahasiswaController::class, 'resetPassword'])->name('resetPassword');
        Route::get('/mahasiswa/search', [MahasiswaController::class, 'search'])->name('mahasiswa.search');

        // Route::get('/jadwal/{jadwalId}/pertemuan', [PertemuanController::class, 'index'])->name('pertemuan.index');
        // Route::post('/pertemuan/store', [PertemuanController::class, 'store'])->name('pertemuan.store');
        // Route::put('/admin/pertemuan/{pertemuan_id}/toggle', [PertemuanController::class, 'toggleStatus'])->name('pertemuan.toggleStatus');
        // Route::get('/jadwal/{jadwal_id}/pertemuan/{id}/edit', [PertemuanController::class, 'edit'])->name('pertemuan.edit');
        // Route::put('/jadwal/{jadwal_id}/pertemuan/{id}', [PertemuanController::class, 'update'])->name('pertemuan.update');
        // Route::delete('/jadwal/{jadwal_id}/pertemuan/{id}', [PertemuanController::class, 'destroy'])->name('pertemuan.destroy');


        Route::post('absensi/tutup/{jadwalId}', [AbsensiController::class, 'tutupSesiAbsensi'])->name('absensi.tutupSesi');
        Route::get('rekap-absensi/{jadwalId}', [AbsensiController::class, 'rekapAbsensi'])->name('rekap.absensi');

        // Tambahan route untuk absensi
        Route::get('/absensi/{jadwal_id}/detail/{pertemuan_id}', [AbsensiController::class, 'detail'])->name('absensi.detail');
        Route::get('absensi/open/{jadwalId}', [AbsensiController::class, 'openAbsensi'])->name('absensi.open');
        Route::get('absensi/close/{jadwalId}', [AbsensiController::class, 'closeAbsensi'])->name('absensi.close');
        Route::post('absensi/tutup/{jadwalId}', [AbsensiController::class, 'tutupSesiAbsensi'])->name('absensi.tutupSesi');
        Route::post('absensi/buka/{jadwalId}', [AbsensiController::class, 'bukaSesiAbsensi'])->name('absensi.bukaSesi');
        Route::put('absensi/update/{absensiId}', [AbsensiController::class, 'updateStatus'])->name('absensi.updateStatus');
        Route::get('absensi/riwayat', [AbsensiController::class, 'riwayat'])->name('absensi.riwayat');
        Route::get('absensi/detail/{jadwal_id}', [AbsensiController::class, 'show'])->name('admin.absensi.show');
        Route::get('/mahasiswa/data', [MahasiswaController::class, 'getMahasiswa'])->name('mahasiswa.data');
        Route::get('/api/berita', [BeritaController::class, 'getBerita'])->name('getBerita')->name('getBerita');

        //Import ke Excel
        Route::post('/admin/mahasiswa/import', [MahasiswaController::class, 'importExcel'])->name('mahasiswa.import');
        Route::get('/admin/mahasiswa/template-mhs', [MahasiswaController::class, 'downloadTemplateNew'])
        ->name('mahasiswa.download-template');

        //Laporan PDF
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::post('/laporan/generate-pdf', [LaporanController::class, 'generatePDF'])->name('laporan.generate-pdf');

        Route::get('/pengajuan-transkrip', [PengajuanTranskripController::class, 'indexTransrkip'])->name('transkrip.index');
        Route::get('/pengajuan/{pengajuan}/edit', [PengajuanTranskripController::class, 'edit'])->name('pengajuan.edit');
        Route::put('/pengajuan/{pengajuan}', [PengajuanTranskripController::class, 'update'])->name('pengajuan.update');
        Route::delete('/pengajuan/{pengajuan}', [PengajuanTranskripController::class, 'destroy'])->name('pengajuan.destroy');
        Route::patch('/pengajuan/{id}/update-status', [PengajuanTranskripController::class, 'updateStatus'])->name('pengajuan.updateStatus');

        Route::get('/permintaan', [PerminataanController::class, 'index'])->name('permintaan.index');
        Route::get('/permintaan/{id}/edit', [PerminataanController::class, 'edit'])->name('permintaan.edit');

        Route::get('/helpdesk/permintaan', [PermintaanController::class, 'index'])->name('helpdesk.index');
        Route::get('/helpdesk/permintaan/{id}', [PermintaanController::class, 'show'])->name('helpdesk.show');
        Route::put('/helpdesk/permintaan/{id}/status', [PermintaanController::class, 'updateStatus'])->name('helpdesk.updateStatus');


        //Surat Keterangan Pendamping Ijazah Mahasiswa Validator
        Route::get('/surat-keterangan-pendamping-ijazah', [ValidatorController::class, 'index'])->name('skpi.index');

        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi', [ValidatorController::class, 'sertifikasi'])->name('skpi.sertifikasi');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/validator', [ValidatorController::class, 'sertifikasiValidator'])->name('skpi.sertifikasi.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/disetujui', [ValidatorController::class, 'sertifikasiDisetujui'])->name('skpi.sertifikasi.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/ditolak', [ValidatorController::class, 'sertifikasiDitolak'])->name('skpi.sertifikasi.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/ditinjau', [ValidatorController::class, 'sertifikasiDitinjau'])->name('skpi.sertifikasi.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/sertifikasi/menunggu', [ValidatorController::class, 'sertifikasiMenunggu'])->name('skpi.sertifikasi.menunggu');
        Route::put('/sertifikasi/{id}/catatan', [ValidatorController::class, 'updateCatatan'])->name('sertifikasi.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/bahasa', [ValidatorController::class, 'bahasa'])->name('skpi.bahasa');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/validator', [ValidatorController::class, 'bahasaValidator'])->name('skpi.bahasa.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/disetujui', [ValidatorController::class, 'bahasaDisetujui'])->name('skpi.bahasa.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/ditolak', [ValidatorController::class, 'bahasaDitolak'])->name('skpi.bahasa.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/ditinjau', [ValidatorController::class, 'bahasaDitinjau'])->name('skpi.bahasa.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/bahasa/menunggu', [ValidatorController::class, 'bahasaMenunggu'])->name('skpi.bahasa.menunggu');
        Route::put('/penguasaan-bahasa-asing/{id}/catatan', [ValidatorController::class, 'updateCatatanBahasa'])->name('bahasa.catatan');


        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha', [ValidatorController::class, 'wirausaha'])->name('skpi.wirausaha');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/validator', [ValidatorController::class, 'wirausahaValidator'])->name('skpi.wirausaha.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/disetujui', [ValidatorController::class, 'wirausahaDisetujui'])->name('skpi.wirausaha.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/ditolak', [ValidatorController::class, 'wirausahaDitolak'])->name('skpi.wirausaha.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/ditinjau', [ValidatorController::class, 'wirausahaDitinjau'])->name('skpi.wirausaha.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/wirausaha/menunggu', [ValidatorController::class, 'wirausahaMenunggu'])->name('skpi.wirausaha.menunggu');
        Route::put('/wirausaha/{id}/catatan', [ValidatorController::class, 'updateCatatanWirausaha'])->name('wirausaha.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/pkm', [ValidatorController::class, 'pkm'])->name('skpi.pkm');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/validator', [ValidatorController::class, 'pkmValidator'])->name('skpi.pkm.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/disetujui', [ValidatorController::class, 'pkmDisetujui'])->name('skpi.pkm.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/ditolak', [ValidatorController::class, 'pkmDitolak'])->name('skpi.pkm.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/ditinjau', [ValidatorController::class, 'pkmDitinjau'])->name('skpi.pkm.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/pkm/menunggu', [ValidatorController::class, 'pkmMenunggu'])->name('skpi.pkm.menunggu');
        Route::put('/pkm/{id}/catatan', [ValidatorController::class, 'updateCatatanPkm'])->name('pkm.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/ppsm', [ValidatorController::class, 'ppsm'])->name('skpi.ppsm');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/validator', [ValidatorController::class, 'ppsmValidator'])->name('skpi.ppsm.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/disetujui', [ValidatorController::class, 'ppsmDisetujui'])->name('skpi.ppsm.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/ditolak', [ValidatorController::class, 'ppsmDitolak'])->name('skpi.ppsm.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/ditinjau', [ValidatorController::class, 'ppsmDitinjau'])->name('skpi.ppsm.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/ppsm/menunggu', [ValidatorController::class, 'ppsmMenunggu'])->name('skpi.ppsm.menunggu');
        Route::put('/ppsm/{id}/catatan', [ValidatorController::class, 'updateCatatanPpsm'])->name('ppsm.catatan');

        Route::get('/surat-keterangan-pendamping-ijazah/tambahan', [ValidatorController::class, 'tambahan'])->name('skpi.tambahan');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/validator', [ValidatorController::class, 'tambahanValidator'])->name('skpi.tambahan.validator');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/disetujui', [ValidatorController::class, 'tambahanDisetujui'])->name('skpi.tambahan.disetujui');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/ditolak', [ValidatorController::class, 'tambahanDitolak'])->name('skpi.tambahan.ditolak');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/ditinjau', [ValidatorController::class, 'tambahanDitinjau'])->name('skpi.tambahan.ditinjau');
        Route::get('/surat-keterangan-pendamping-ijazah/tambahan/menunggu', [ValidatorController::class, 'tambahanMenunggu'])->name('skpi.tambahan.menunggu');
        Route::put('/tambahan/{id}/catatan', [ValidatorController::class, 'updateCatatanTambahan'])->name('tambahan.catatan');


    });
});

Route::prefix('dosen')->name('dosen.')->group(function () {
        Route::get('login', [LoginDosenController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginDosenController::class, 'login'])->name('mhs.login');
        Route::post('logout', [LoginDosenController::class, 'logout'])->name('logout');
        Route::middleware('auth:dosen')->group(function () {
        Route::get('dashboard', [DashboardDosenController::class, 'index'])->name('dashboard');
        // Profil mahasiswa
        Route::group([
            'prefix' => 'dosen',
            'middleware' => ['auth:dosen'],
        ], function () {
            // ===== Perkuliahan =====

            // Jadwal Mengajar
            Route::get('/jadwal', [PerkuliahanDosenController::class, 'jadwalIndex'])->name('jadwal.index');
            Route::get('/jadwal/search', [PerkuliahanDosenController::class, 'search'])->name('jadwal.search');
             Route::get('/praktik/search', [PerkuliahanDosenController::class, 'searchPraktik'])->name('praktik.search');

             // Jadwal Mengajar
            Route::get('/jadwal-praktik', [PerkuliahanDosenController::class, 'PraktikIndex'])->name('jadwal-praktik.index');
            Route::get('/jadwal-praktik/search', [PerkuliahanDosenController::class, 'searchPraktik'])->name('jadwal-praktik.search');


            // Absensi
            // Route::get('/absensi/create', [PerkuliahanDosenController::class, 'createAbsensi'])->name('absensi.create');
            Route::get('/absensi', [PerkuliahanDosenController::class, 'indexAbsensi'])->name('absensi.index');
            Route::get('/absensi/search', [PerkuliahanDosenController::class, 'searchAbsen'])->name('absensi.search');

            //Teori Absensi
            Route::post('/absensi/pertemuan', [PerkuliahanDosenController::class, 'storePertemuan'])->name('absensi.store');
            Route::get('/pertemuan/list/{jadwal_id}', [PerkuliahanDosenController::class, 'listPertemuan']);
            Route::get('/absensi/buat/{pertemuan_id}', [PerkuliahanDosenController::class, 'lihat'])->name('absensi.create');
            Route::post('/absensi/store', [PerkuliahanDosenController::class, 'store'])->name('absensi-store');

            //Praktik Absensi
            Route::post('/absensi-praktik/pertemuan', [PerkuliahanDosenController::class, 'storePertemuanPraktik'])->name('absensi-praktik.store');
            Route::get('/pertemuan-praktik/list/{jadwal_praktik_id}', [PerkuliahanDosenController::class, 'listPertemuanPraktik']);
            Route::get('/absensi-praktik/buat/{pertemuan_praktik_id}', [PerkuliahanDosenController::class, 'lihatPraktik'])->name('absensi-praktik.create');
            Route::post('/absensi-praktik/store', [PerkuliahanDosenController::class, 'storePraktik'])->name('absensi-praktik-store');

            //Cetak Absensi Teori
            Route::get('/absensi/cetak', [LaporanAbsensiController::class, 'index'])->name('absensi.cetak.index');
            Route::post('/absensi/generate-pdf', [LaporanAbsensiController::class, 'generatePDF'])->name('absensi.generate-pdf');
            Route::post('/pertemuan/generate-pdf', [LaporanAbsensiController::class, 'generatePertemuan'])->name('generate-pdf');

            //Cetak Absensi Praktik
            Route::post('/absensi-praktik/generate-pdf', [LaporanAbsensiController::class, 'generatePraktikPDF'])->name('absensi-praktik.generate-pdf');
            Route::post('/pertemuan-praktik/generate-pdf', [LaporanAbsensiController::class, 'generatePraktikPertemuan'])->name('praktik.generate-pdf');

            // Input Nilai
            Route::get('/nilai/input', [ModulAkademikController::class, 'inputNilai'])->name('nilai.input');
            Route::get('/nilai', [ModulAkademikController::class, 'index'])->name('nilai.index');

            // ===== Modul Akademik =====
            // Materi Kuliah
            Route::get('/materi', [ModulAkademikController::class, 'indexRps'])->name('materi.index');
            Route::get('/materi/create', [ModulAkademikController::class, 'tambahRps'])->name('materi.create');

            Route::get('/api/berita', [BeritaController::class, 'getBerita'])->name('getBerita');
            Route::get('/api/index/berita', [BeritaController::class, 'index'])->name('index.berita');
            Route::get('/api/berita-kampus', [BeritaController::class, 'getBeritaKampus'])->name('getBeritaKampus');
            Route::get('/berita/{id}', [BeritaController::class, 'getDetailBeritaDosen'])->name('berita.detail');
            Route::get('profile', [ProfileDosenController::class, 'index'])->name('profile.index');
            Route::post('profile', [ProfileDosenController::class, 'update'])->name('profile.update');
        });
    });
});
