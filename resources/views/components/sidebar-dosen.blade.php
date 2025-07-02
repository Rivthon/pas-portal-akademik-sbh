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


        <!-- Modul Akademik -->
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Modul Akademik</span>
        </li>
        <li class="menu-item @if(Route::is('dosen.nilai-dosen.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bx-file"></i>
                <div data-i18n="Input Nilai">Manajemen Nilai</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('dosen.nilai-dosen.input')) active @endif">
                    <a href="{{ route('dosen.nilai-dosen.input') }}" class="menu-link">
                        <div data-i18n="Input Nilai">Input Nilai</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('dosen.nilai-dosen.lihat')) active @endif">
                    <a href="{{ route('dosen.nilai-dosen.lihat') }}" class="menu-link">
                        <div data-i18n="Input Nilai">Lihat Nilai</div>
                    </a>
                </li>

            </ul>
        </li>
        {{-- <li class="menu-item @if(Route::is('dosen.dashboard')) active @endif">
            <a href="{{ route('dosen.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bx-user-plus"></i>
                <span class="menu-text">Mahasiswa Bimbingan</span>
            </a>
        </li> --}}
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
                    {{-- <a href="javascript:void(0);" class="menu-link"
                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="menu-icon tf-icons bx bx-power-off text-danger"></i>
                        <span class="menu-text">Keluar</span>
                    </a> --}}
                    <form method="POST" action="{{ route('dosen.logout') }}" id="logout-form" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>

    </ul>
</aside>