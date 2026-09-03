<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme shadow-sm">
    <div class="app-brand demo justify-content-center py-3">
        <a href="#" class="app-brand-link d-flex align-items-center gap-2">
            <img src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('default/logo.ico') }}"
                class="navbar-brand-img" style="height: 40px; width: auto; object-fit: contain;" alt="main_logo">
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-primary"
                style="font-size: 1.25rem;">{{ explode(' ', $settings->name)[0] }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-2">
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Dashboard</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.dashboard')) active @endif">
            <a href="{{ route('dosen.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bxs-dashboard text-info"></i>
                <span class="menu-text fw-medium">Dashboard Utama</span>
            </a>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Perkuliahan</span>
        </li>

        <li class="menu-item @if(Route::is('dosen.rps.*')) active @endif">
            <a href="{{ route('dosen.rps.index') }}" class="menu-link">
                <i class="menu-icon bx bx-file text-primary"></i>
                <div data-i18n="RPS" class="fw-medium">RPS</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('dosen.nilai-dosen.lihat')) active @endif">
            <a href="{{ route('dosen.nilai-dosen.lihat') }}" class="menu-link">
                <i class="menu-icon bx bx-group text-info"></i>
                <div class="fw-medium">Status KRS Bimbingan</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('dosen.kurikulum-krs.*')) active @endif">
            <a href="{{ route('dosen.kurikulum-krs.index') }}" class="menu-link">
                <i class="menu-icon bx bx-book-content text-primary"></i>
                <div class="fw-medium">Kurikulum KRS</div>
            </a>
        </li>

        <!-- <li class="menu-item @if(Route::is('dosen.jadwal*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-book-open text-warning"></i>
                <div data-i18n="Jadwal Mengajar" class="fw-medium">Jadwal Mengajar</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.jadwal.index')) active @endif">
                    <a href="{{ route('dosen.jadwal.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-book-content"></i>
                        <div data-i18n="Teori">Teori</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.jadwal-praktik.index')) active @endif">
                    <a href="{{ route('dosen.jadwal-praktik.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-flask"></i>
                        <div data-i18n="Praktikum">Praktikum</div>
                    </a>
                </li>
            </ul>
        </li> -->

        <li
            class="menu-item @if(Route::is('dosen.absensi.*') or Route::is('dosen.absensi-praktik.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-user-check text-success"></i>
                <div data-i18n="Absensi & BAP" class="fw-medium">Absensi & BAP</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.absensi.index')) active @endif">
                    <a href="{{ route('dosen.absensi.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-book"></i>
                        <div>Absensi Teori</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.absensi-praktik.*')) active @endif">
                    <a href="{{ route('dosen.absensi-praktik.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-test-tube"></i>
                        <div>Absensi Praktik</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.absensi.cetak.index')) active @endif">
                    <a href="{{ route('dosen.absensi.cetak.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-printer"></i>
                        <div data-i18n="Cetak Laporan">Rekaptulasi Absensi</div>
                    </a>
                </li>
            </ul>
        <li class="menu-item @if(Route::is('dosen.lms.*')) active @endif">
            <a href="{{ route('dosen.lms.index') }}" class="menu-link">
                <i class="menu-icon bx bx-book-reader text-primary"></i>
                <div class="fw-medium">LMS</div>
            </a>
        </li>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Modul Penilaian</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.nilai-dosen.input') || Route::is('dosen.edom.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-bar-chart-alt-2" style="color: #696cff;"></i>
                <div data-i18n="Manajemen Nilai" class="fw-medium">Manajemen Nilai</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.nilai-dosen.input')) active @endif">
                    <a href="{{ route('dosen.nilai-dosen.input') }}" class="menu-link">
                        <i class="menu-icon bx bx-edit"></i>
                        <div data-i18n="Input KHS Mahasiswa">Input Nilai Mahasiswa</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.edom.hasil')) active @endif">
                    <a href="{{ route('dosen.edom.hasil') }}" class="menu-link">
                        <i class="menu-icon bx bx-star"></i>
                        <div data-i18n="Hasil Evaluasi (EDOM)">Hasil Evaluasi (EDOM)</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Lainnya</span>
        </li>
        <li
            class="menu-item @if(Route::is('dosen.settings.*') || Route::is('dosen.index.berita') || Route::is('dosen.profile.index') || Route::is('dosen.permintaan.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-cog text-secondary"></i>
                <div data-i18n="Pengaturan & Akun" class="fw-medium">Pengaturan & Akun</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.permintaan.*')) active @endif">
                    <a href="{{ route('dosen.permintaan.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-support"></i>
                        <div>Helpdesk</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.index.berita')) active @endif">
                    <a href="{{ route('dosen.index.berita') }}" class="menu-link">
                        <i class="menu-icon bx bx-news"></i>
                        <div data-i18n="Berita Kampus">Berita Kampus</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.profile.index')) active @endif">
                    <a href="{{ route('dosen.profile.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-user-circle"></i>
                        <span class="menu-text">Profil Saya</span>
                    </a>
                </li>
                <li class="menu-item mt-2 pt-2 border-top">
                    <a href="javascript:void(0);" class="menu-link" onclick="confirmLogout(event)">
                        <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                        <span class="menu-text text-danger fw-bold">Keluar Sistem</span>
                    </a>
                    <form method="POST" action="{{ route('dosen.logout') }}" id="logout-form" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</aside>