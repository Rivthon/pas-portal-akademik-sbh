<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo justify-content-center">
        <a href="#" class="app-brand-link d-flex align-items-center">
            <img src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('default/logo.ico') }}"
                class="navbar-brand-img" style="height: 36px; width: auto;" alt="main_logo">
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>
    <ul class="menu-inner py-1">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>

        <li class="menu-item @if(Route::is('admin.home')) active @endif">
            <a href="{{ route('admin.home') }}" class="menu-link">
                <i class="menu-icon bx bx-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        @canany(['lms-list', 'rps-list', 'absensi-list', 'pedoman-akademik-list'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Portal Pembelajaran</span>
            </li>
            <li class="menu-item @if(Route::is('admin.lms.*') || Route::is('admin.rps.*') || Route::is('admin.absensi.*') || Route::is('admin.pedoman-akademik.*')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book-reader"></i>
                    <div>Monitoring Perkuliahan</div>
                </a>
                <ul class="menu-sub">
                    @can('lms-list')
                        <li class="menu-item @if(Route::is('admin.lms.*')) active @endif">
                            <a href="{{ route('admin.lms.index') }}" class="menu-link"><div>LMS Semua Matkul</div></a>
                        </li>
                    @endcan
                    @can('rps-list')
                        <li class="menu-item @if(Route::is('admin.rps.*')) active @endif">
                            <a href="{{ route('admin.rps.index') }}" class="menu-link"><div>RPS Semua Matkul</div></a>
                        </li>
                    @endcan
                    @can('absensi-list')
                        <li class="menu-item @if(Route::is('admin.absensi.*')) active @endif">
                            <a href="{{ route('admin.absensi.index') }}" class="menu-link"><div>Absensi Semua Matkul</div></a>
                        </li>
                    @endcan
                    @can('pedoman-akademik-list')
                        <li class="menu-item @if(Route::is('admin.pedoman-akademik.*')) active @endif">
                            <a href="{{ route('admin.pedoman-akademik.index') }}" class="menu-link"><div>Pedoman Akademik</div></a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @can('bap-pengajaran-list')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Administrasi BAUK</span>
            </li>
            <li class="menu-item @if(Route::is('admin.bap-pengajaran.*')) active @endif">
                <a href="{{ route('admin.bap-pengajaran.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-clipboard"></i>
                    <div>BAP Pengajaran</div>
                </a>
            </li>
        @endcan

        @canany(['users-list', 'role-list'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Pengguna</span>
            </li>
            <li class="menu-item @if(Route::is('admin.users.index') || Route::is('admin.roles.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-group"></i>
                    <div data-i18n="Users">Data Pengguna</div>
                </a>
                <ul class="menu-sub">
                    @can('users-list')
                        <li class="menu-item @if(Route::is('admin.users.index*')) active open @endif">
                            <a href="{{ route('admin.users.index') }}" class="menu-link">
                                <div data-i18n="Users">Pengguna</div>
                            </a>
                        </li>
                    @endcan
                    @can('role-list')
                        <li class="menu-item @if(Route::is('admin.roles.index*')) active open @endif">
                            <a href="{{ route('admin.roles.index') }}" class="menu-link">
                                <div data-i18n="Roles">Role</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        @canany(['program-studi-list', 'matakuliah-list', 'mahasiswa-list', 'dosen-list', 'ruangan-list', 'evaluasi-list', 'gelombang-list', 'absensi-list', 'kalender-list'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Master Data</span>
            </li>
            <li
                class="menu-item @if(Route::is('admin.program-studi.index') || Route::is('admin.calender.index') || Route::is('admin.matakuliah.index') || Route::is('admin.ruangan.index') || Route::is('admin.mahasiswa.index') || Route::is('admin.dosen.index') || Route::is('admin.gelombang.index') || Route::is('admin.evaluasi.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                    <div data-i18n="Master Data">Data Master</div>
                </a>
                <ul class="menu-sub">
                    @can('program-studi-list')
                        <li class="menu-item @if(Route::is('admin.program-studi.index')) active @endif">
                            <a href="{{ route('admin.program-studi.index') }}" class="menu-link">
                                <div data-i18n="Program Studi">Program Studi</div>
                            </a>
                        </li>
                    @endcan

                    @can('matakuliah-list')
                        <li class="menu-item @if(Route::is('admin.matakuliah.index')) active @endif">
                            <a href="{{ route('admin.matakuliah.index') }}" class="menu-link">
                                <div data-i18n="Kurikulum">Kurikulum</div>
                            </a>
                        </li>
                    @endcan

                    @can('mahasiswa-list')
                        <li class="menu-item @if(Route::is('admin.mahasiswa.index')) active @endif">
                            <a href="{{ route('admin.mahasiswa.index') }}" class="menu-link">
                                <div data-i18n="Mahasiswa">Mahasiswa</div>
                            </a>
                        </li>
                    @endcan

                    @can('dosen-list')
                        <li class="menu-item @if(Route::is('admin.dosen.index')) active @endif">
                            <a href="{{ route('admin.dosen.index') }}" class="menu-link">
                                <div data-i18n="Dosen">Dosen</div>
                            </a>
                        </li>
                    @endcan

                    @can('ruangan-list')
                        <li class="menu-item @if(Route::is('admin.ruangan.index')) active @endif">
                            <a href="{{ route('admin.ruangan.index') }}" class="menu-link">
                                <div data-i18n="Ruangan">Ruangan</div>
                            </a>
                        </li>
                    @endcan

                    @can('evaluasi-list')
                        <li class="menu-item @if(Route::is('admin.evaluasi.index')) active @endif">
                            <a href="{{ route('admin.evaluasi.index') }}" class="menu-link">
                                <div data-i18n="Evaluasi Dosen">Evaluasi Dosen</div>
                            </a>
                        </li>
                    @endcan

                    @can('gelombang-list')
                        <li class="menu-item @if(Route::is('admin.gelombang.index')) active @endif">
                            <a href="{{ route('admin.gelombang.index') }}" class="menu-link">
                                <div data-i18n="Gelombang">Gelombang</div>
                            </a>
                        </li>
                    @endcan

                    @can('kalender-list')
                        <li class="menu-item @if(Route::is('admin.calender.index')) active @endif">
                            <a href="{{ route('admin.calender.index') }}" class="menu-link">
                                <div data-i18n="Kalender Akademik">Kalender Akademik</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- MANAJEMEN AKADEMIK --}}
        @canany(['jadwal-list', 'jadwal-praktik-list', 'jadwal-uts-list', 'jadwal-uas-list', 'jadwal-uap-list', 'kurikulum-list', 'assign-dosen-list', 'krs-list', 'krs-archive-list'])
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Manajemen Akademik</span>
            </li>

            {{-- Perkuliahan --}}
            <li
                class="menu-item @if(Route::is('admin.jadwal.index') || Route::is('admin.jadwal-praktik.index') || Route::is('admin.jadwal-uts.index') || Route::is('admin.jadwal-uas.index') || Route::is('admin.jadwal-uap.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bxs-calendar"></i>
                    <div data-i18n="Perkuliahan">Perkuliahan</div>
                </a>
                <ul class="menu-sub">
                    @can('jadwal-list')
                        <li class="menu-item @if(Route::is('admin.jadwal.index')) active @endif">
                            <a href="{{ route('admin.jadwal.index') }}" class="menu-link">
                                <div data-i18n="Jadwal Kuliah">Jadwal Kuliah</div>
                            </a>
                        </li>
                    @endcan
                    @can('jadwal-praktik-list')
                        <li class="menu-item @if(Route::is('admin.jadwal-praktik.index')) active @endif">
                            <a href="{{ route('admin.jadwal-praktik.index') }}" class="menu-link">
                                <div data-i18n="Jadwal Praktik">Jadwal Praktik</div>
                            </a>
                        </li>
                    @endcan
                    @can('jadwal-uts-list')
                        <li class="menu-item @if(Route::is('admin.jadwal-uts.index')) active @endif">
                            <a href="{{ route('admin.jadwal-uts.index') }}" class="menu-link">
                                <div data-i18n="Jadwal UTS">Jadwal UTS</div>
                            </a>
                        </li>
                    @endcan
                    @can('jadwal-uas-list')
                        <li class="menu-item @if(Route::is('admin.jadwal-uas.index')) active @endif">
                            <a href="{{ route('admin.jadwal-uas.index') }}" class="menu-link">
                                <div data-i18n="Jadwal UAS">Jadwal UAS</div>
                            </a>
                        </li>
                    @endcan
                    @can('jadwal-uap-list')
                        <li class="menu-item @if(Route::is('admin.jadwal-uap.index')) active @endif">
                            <a href="{{ route('admin.jadwal-uap.index') }}" class="menu-link">
                                <div data-i18n="Jadwal UAP">Jadwal UAP</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>

            {{-- Manajemen Penilaian --}}
            @canany(['evaluasi-list', 'input-nilai', 'list-nilai', 'list-uap'])
                <li
                    class="menu-item @if(Route::is('admin.penilaian.index') || Route::is('admin.cek-nilai.*') || Route::is('admin.nilai.index') || Route::is('admin.transkrip.index') || Route::is('admin.input-nilai.index') || Route::is('admin.uap.index')) active open @endif">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bxs-file-archive"></i>
                        <div data-i18n="Manajemen Penilaian">Manajemen Penilaian</div>
                    </a>
                    <ul class="menu-sub">
                        @can('evaluasi-list')
                            <li class="menu-item @if(Route::is('admin.penilaian.index')) active @endif">
                                <a href="{{ route('admin.penilaian.index') }}" class="menu-link">
                                    <div data-i18n="Penilaian Dosen">Penilaian Dosen</div>
                                </a>
                            </li>
                        @endcan
                        @can('input-nilai')
                            <li class="menu-item @if(Route::is('admin.input-nilai.index')) active @endif">
                                <a href="{{ route('admin.input-nilai.index') }}" class="menu-link">
                                    <div data-i18n="Input Nilai">Input Nilai</div>
                                </a>
                            </li>
                        @endcan
                        @can('list-uap')
                            <li class="menu-item @if(Route::is('admin.uap.index')) active @endif">
                                <a href="{{ route('admin.uap.index') }}" class="menu-link">
                                    <div data-i18n="Input Nilai UAP">Input Nilai UAP</div>
                                </a>
                            </li>
                        @endcan
                        @can('pengajuan-transkrip-list')
                            <li class="menu-item @if(Route::is('admin.transkrip.index') || Route::is('admin.pengajuan.*')) active @endif">
                                <a href="{{ route('admin.transkrip.index') }}" class="menu-link">
                                    <div data-i18n="Pengajuan Transkrip">Pengajuan Transkrip</div>
                                </a>
                            </li>
                        @endcan
                        @can('list-nilai')
                            <li class="menu-item @if(Route::is('admin.cek-nilai.index') || Route::is('admin.cek-nilai.*')) active @endif">
                                <a href="{{ route('admin.cek-nilai.index') }}" class="menu-link">
                                    <div data-i18n="Cek Nilai Mahasiswa">Cek Nilai Mahasiswa</div>
                                </a>
                            </li>
                            <li class="menu-item @if(Route::is('admin.nilai.index')) active @endif">
                                <a href="{{ route('admin.nilai.index') }}" class="menu-link">
                                    <div data-i18n="Transkrip Nilai">Transkrip Nilai</div>
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcanany <li
                class="menu-item @if(Route::is('admin.index.assign') || Route::is('admin.kurikulum.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-food-menu"></i>
                    <div data-i18n="Distribusi Mata Kuliah">Distribusi Mata Kuliah</div>
                </a>
                <ul class="menu-sub">
                    @can('kurikulum-list')
                        <li class="menu-item @if(Route::is('admin.kurikulum.index')) active @endif">
                            <a href="{{ route('admin.kurikulum.index') }}" class="menu-link">
                                <div data-i18n="Mata Kuliah">Mata Kuliah</div>
                            </a>
                        </li>
                    @endcan

                    @can('assign-dosen-list')
                        <li class="menu-item @if(Route::is('admin.index.assign')) active @endif">
                            <a href="{{ route('admin.index.assign') }}" class="menu-link">
                                <div data-i18n="Penugasan Dosen">Penugasan Dosen</div>
                            </a>
                        </li>
                    @endcan

                </ul>
            </li>

            {{-- Manajemen KRS --}}
            @can('krs-list')
            <li class="menu-item @if(Route::is('admin.krs-admin.index') || Route::is('admin.krs-admin.*')) active @endif">
                <a href="{{ route('admin.krs-admin.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-spreadsheet"></i>
                    <div data-i18n="Manajemen KRS">Manajemen KRS</div>
                </a>
            </li>
            @endcan
            @can('krs-archive-list')
                <li class="menu-item @if(Route::is('admin.krs-archive.*')) active @endif">
                    <a href="{{ route('admin.krs-archive.index') }}" class="menu-link">
                        <i class="menu-icon tf-icons bx bx-archive"></i>
                        <div data-i18n="Arsip KRS">Arsip KRS</div>
                    </a>
                </li>
            @endcan
        @endcanany {{-- ADMINISTRASI --}}
        @canany(['tarif-list', 'tenor-list', 'tagihan-list', 'aktivasi-list'])
            <li
                class="menu-item @if(Route::is('admin.aktivasi.index') || Route::is('admin.tarif.index') || Route::is('admin.tenor-pembayaran.index') || Route::is('admin.tagihan-mahasiswa.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                    <div data-i18n="Administrasi">Administrasi</div>
                </a>
                <ul class="menu-sub">
                    @can('tarif-list')
                        <li class="menu-item @if(Route::is('admin.tarif.index')) active @endif">
                            <a href="{{ route('admin.tarif.index') }}" class="menu-link">
                                <div data-i18n="Tarif (Semester)">Tarif (Semester)</div>
                            </a>
                        </li>
                    @endcan
                    @can('tenor-list')
                        <li class="menu-item @if(Route::is('admin.tenor-pembayaran.index')) active @endif">
                            <a href="{{ route('admin.tenor-pembayaran.index') }}" class="menu-link">
                                <div data-i18n="Tenor Pembayaran">Tenor Pembayaran</div>
                            </a>
                        </li>
                    @endcan
                    @can('tagihan-list')
                        <li class="menu-item @if(Route::is('admin.tagihan-mahasiswa.index')) active @endif">
                            <a href="{{ route('admin.tagihan-mahasiswa.index') }}" class="menu-link">
                                <div data-i18n="Tagihan Pembayaran">Tagihan Pembayaran</div>
                            </a>
                        </li>
                    @endcan

                    @can('aktivasi-list')
                        <li class="menu-item @if(Route::is('admin.aktivasi.index')) active @endif">
                            <a href="{{ route('admin.aktivasi.index') }}" class="menu-link">
                                <div data-i18n="Aktivasi">Aktivasi</div>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

        {{-- Validator Surat Keterangan Pendamping Ijazah --}}
        @can('skpi-list')
            <li class="menu-item @if(Route::is('admin.skpi.index')) active @endif">
                <a href="{{ route('admin.skpi.index') }}" class="menu-link">
                    <i class="menu-icon bx bxs-award"></i>
                    <span class="menu-text">Validator SKPI</span>
                </a>
            </li>
        @endcan

        @can('activity-log-list')
            <li class="menu-item @if(Route::is('admin.activity-logs.index')) active @endif">
                <a href="{{ route('admin.activity-logs.index') }}" class="menu-link">
                    <i class="menu-icon bx bx-history"></i>
                    <span class="menu-text">Log Aktivitas</span>
                </a>
            </li>
        @endcan

        @canany(['tahun-ajaran-list', 'permintaan-list', 'settings-edit'])
            <li
                class="menu-item @if(Route::is('admin.tahun-ajaran.index') || Route::is('admin.settings.edit') || Route::is('admin.helpdesk.index')) active open @endif">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div data-i18n="Pengaturan">Pengaturan</div>
                </a>
                <ul class="menu-sub">
                    @can('tahun-ajaran-list')
                        <li class="menu-item @if(Route::is('admin.tahun-ajaran.index')) active @endif">
                            <a href="{{ route('admin.tahun-ajaran.index') }}" class="menu-link">
                                <div data-i18n="Tahun Ajaran">Tahun Ajaran </div>
                            </a>
                        </li>
                    @endcan
                    @can('permintaan-list')
                        <li class="menu-item @if(Route::is('admin.helpdesk.index')) active @endif">
                            <a href="{{ route('admin.helpdesk.index') }}" class="menu-link">
                                <div data-i18n="Helpdesk">Helpdesk </div>
                            </a>
                        </li>
                    @endcan
                    @can('settings-edit')
                        <li class="menu-item @if(Route::is('admin.settings.edit')) active @endif">
                            <a href="{{ route('admin.settings.edit') }}" class="menu-link">
                                <span class="menu-text">Pengaturan Aplikasi</span>
                            </a>
                        </li>
                    @endcan
                </ul>
            </li>
        @endcanany

    </ul>
</aside>
