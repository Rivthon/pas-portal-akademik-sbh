<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="#" class="app-brand-link">
            <img src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('default/logo.ico') }}"
                class="navbar-brand-img" width="26" height="26" alt="main_logo">
            <span class="app-brand-text demo menu-text fw-bold ms-2 fs-6 text-uppercase">{{ $settings->name }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Dashboard</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.dashboard')) active @endif">
            <a href="{{ route('dosen.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bx-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <!-- Perkuliahan -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Perkuliahan</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.jadwal*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-calendar"></i>
                <div data-i18n="Absensi">Jadwal Mengajar</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.jadwal.index')) active @endif">
                    <a href="{{ route('dosen.jadwal.index') }}" class="menu-link">
                        <div data-i18n="Buat Absensi">Teori</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.jadwal-praktik.index')) active @endif">
                    <a href="{{ route('dosen.jadwal-praktik.index') }}" class="menu-link">
                        <div data-i18n="Buat Absensi">Praktik</div>
                    </a>
                </li>


            </ul>
        </li>

        <li class="menu-item @if(Route::is('dosen.absensi.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-check-square"></i>
                <div data-i18n="Absensi">Absensi</div>
            </a>
            <ul class="menu-sub">
                {{-- <li class="menu-item @if(Route::is('dosen.absensi.index')) active @endif">
                    <a href="{{ route('dosen.absensi.index') }}" class="menu-link">
                        <div data-i18n="Buat Absensi">Buat Absensi</div>
                    </a>
                </li> --}}
                <li class="menu-item @if(Route::is('dosen.absensi.cetak.index')) active @endif">
                    <a href="{{ route('dosen.absensi.cetak.index') }}" class="menu-link">
                        <div data-i18n="Buat Absensi">Rekap Absensi</div>
                    </a>
                </li>

            </ul>
        </li>
        <li class="menu-item @if(Route::is('dosen.nilai.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-file"></i>
                <div data-i18n="Input Nilai">Input Nilai</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.nilai.input')) active @endif">
                    <a href="{{ route('dosen.nilai.input') }}" class="menu-link">
                        <div data-i18n="Input Nilai">Input Nilai</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.nilai.index')) active @endif">
                    <a href="{{ route('dosen.nilai.index') }}" class="menu-link">
                        <div data-i18n="Lihat Nilai">Lihat Nilai</div>
                    </a>
                </li>
            </ul>
        </li>

        <!-- Modul Akademik -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Modul Akademik</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.settings.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Barang">Informasi dan Akun</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.index.berita')) active @endif">
                    <a href="{{ route('dosen.index.berita') }}" class="menu-link">
                        <div data-i18n="Berita Kampus">Berita Kampus</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.profile.index')) active @endif">
                    <a href="{{ route('dosen.profile.index') }}" class="menu-link">
                        <span class="menu-text">Pengaturan Profile</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                        <span class="menu-text">Keluar</span>
                    </a>
                    <form method="POST" action="{{ route('dosen.logout') }}" id="logout-form" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>

    </ul>
</aside>