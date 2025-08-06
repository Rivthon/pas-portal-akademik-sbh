@php
use Illuminate\Support\Facades\Auth;
$user = Auth::guard('mahasiswa')->user();
// dd($user?->jurusan_id);
@endphp
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

        <li class="menu-item @if(Route::is('mahasiswa.dashboard.index')) active @endif">
            <a href="{{ route('mahasiswa.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bx-home"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Perkuliahan</span>
        </li>
        <!-- Users -->
        <li
            class="menu-item @if(Route::is('mahasiswa.jadwal-kuliah.index') ||Route::is('mahasiswa.jadwal-uas.index') ||Route::is('mahasiswa.jadwal-praktik.index') ||Route::is('mahasiswa.jadwal-uap.index') || Route::is('mahasiswa.jadwal-uts.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-user-account"></i>
                <div data-i18n="Users">Jadwal Perkuliahan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-kuliah.index*')) active open @endif">
                    <a href="{{ route('mahasiswa.jadwal-kuliah.index') }}" class="menu-link">
                        <div data-i18n="Users">Jadwal Kuliah</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-praktik.index*')) active open @endif">
                    <a href="{{ route('mahasiswa.jadwal-praktik.index') }}" class="menu-link">
                        <div data-i18n="Users">Jadwal Praktik</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uts.index*')) active open @endif">
                    <a href="{{ route('mahasiswa.jadwal-uts.index') }}" class="menu-link">
                        <div data-i18n="Users">Jadwal UTS</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uas.index*')) active open @endif">
                    <a href="{{ route('mahasiswa.jadwal-uas.index') }}" class="menu-link">
                        <div data-i18n="Users">Jadwal UAS</div>
                    </a>
                </li>

                @if($user && $user->jurusan_id == 15401)
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uap.index*')) active open @endif">
                    <a href="{{ route('mahasiswa.jadwal-uap.index') }}" class="menu-link">
                        <div data-i18n="Users">Jadwal UAP {{ $user->jurusan_id }}</div>
                    </a>
                </li>
                @endif
                {{-- <li class="menu-item @if(Route::is('admin.roles.index*')) active open @endif">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <div data-i18n="Roles">Jadwal UAS</div>
                    </a>
                </li> --}}
            </ul>
        </li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Modul Akademik
            </span>
        </li>
        <li
            class="menu-item @if(Route::is('mahasiswa.krs.index') || Route::is('mahasiswa.status.krs.index') || Route::is('admin.ruangan.index') || Route::is('admin.mahasiswa.index') || Route::is('admin.dosen.index') || Route::is('admin.evaluasi.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                <div data-i18n="Kartu Rencan Studi">Kartu Rencan Studi (KRS)</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.krs.index')) active @endif">
                    <a href="{{ route('mahasiswa.krs.index') }}" class="menu-link">
                        <div data-i18n="Pengajuan KRS">Pengajuan KRS</div>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('mahasiswa.status.krs.index')) active @endif">
                    <a href="{{ route('mahasiswa.status.krs.index') }}" class="menu-link">
                        <div data-i18n="Status KRS">Status KRS</div>
                    </a>
                </li>
            </ul>
            <!-- Akademik -->
        <li class="menu-item @if(Route::is('mahasiswa.khs.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-food-menu"></i>
                <div data-i18n="Barang">Kartu Hasil Studi(KHS)</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.kartu-hasil.index')) active @endif">
                    <a href="{{ route('mahasiswa.kartu-hasil.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Lihat Kartu Hasil Studi</div>
                    </a>
                </li>
                {{-- <li class="menu-item @if(Route::is('admin.index.assign')) active @endif">
                    <a href="{{ route('admin.index.assign') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Download Kartu Hasil Studi</div>
                    </a>
                </li> --}}
            </ul>
        </li>
        <li
            class="menu-item @if(Route::is('mahasiswa.nilai-uts.index') || Route::is('mahasiswa.nilai-uas.index') || Route::is('mahasiswa.nilai-akhir.index') || Route::is('mahasiswa.uap.index') ) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-file-doc"></i>
                <div data-i18n="Barang">Manajemen Nilai</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.nilai-uts.index')) active @endif">
                    <a href="{{ route('mahasiswa.nilai-uts.index') }}" class="menu-link">
                        <div data-i18n="Nilai UTS">Nilai UTS</div>
                    </a>
                </li>


                <li class="menu-item @if(Route::is('mahasiswa.nilai-uas.index')) active @endif">
                    <a href="{{ route('mahasiswa.nilai-uas.index') }}" class="menu-link">
                        <div data-i18n="Nilai UAS">Nilai UAS</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.nilai-akhir.index')) active @endif">
                    <a href="{{ route('mahasiswa.nilai-akhir.index') }}" class="menu-link">
                        <div data-i18n="Nilai Akhir">Nilai Akhir</div>
                    </a>
                </li>
                @php
                $user = Auth::guard('mahasiswa')->user();
                @endphp

                @if ($user && $user->jurusan_id == 15401 && $user->semester == 6)
                <li class="menu-item @if(Route::is('mahasiswa.uap.index')) active @endif">
                    <a href="{{ route('mahasiswa.uap.index') }}" class="menu-link">
                        <div data-i18n="Nilai Uap">Nilai Uap {{ $user->jurusan_id }}</div>
                    </a>
                </li>
                @endif

                <li class="menu-item @if(Route::is('mahasiswa.pengajuan.index')) active @endif">
                    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="menu-link">
                        <div data-i18n="Pengajuan">Transkrip</div>
                    </a>
                </li>
            </ul>
        </li>


        <li class="menu-item @if(Route::is('mahasiswa.skpi.index')) active @endif">
            <a href="{{ route('mahasiswa.skpi.index') }}" class="menu-link">
                <i class="menu-icon bx bxs-award"></i>
                <span class="menu-text">Aktivitas Dan Prestasi</span>
            </a>
        </li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Pengaturan & Informasi
            </span>
        </li>
        <li
            class="menu-item @if(Route::is('mahasiswa.profile.index') || Route::is('mahasiswa.index.berita')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Barang">Informasi dan Akun</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.permintaan.index')) active @endif">
                    <a href="{{ route('mahasiswa.permintaan.index') }}" class="menu-link">
                        <div data-i18n="permintaan">Feedback </div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.index.berita')) active @endif">
                    <a href="{{ route('mahasiswa.index.berita') }}" class="menu-link">
                        <span class="menu-text">Berita Kampus</span>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.administrasi.index')) active @endif">
                    <a href="{{ route('mahasiswa.administrasi.index') }}" class="menu-link">
                        <span class="menu-text">Administrasi</span>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.profile.index')) active @endif">
                    <a href="{{ route('mahasiswa.profile.index') }}" class="menu-link">
                        <div data-i18n="Program">Edit Profile </div>
                    </a>
                </li>

            </ul>
        </li>
    </ul>
</aside>