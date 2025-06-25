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

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manajemen Pengguna</span>
        </li>
        <!-- Users -->
        <li class="menu-item @if(Route::is('admin.users.index') || Route::is('admin.roles.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-group"></i>
                <div data-i18n="Users">Data Pengguna</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('users.admin.users.index*')) active open @endif">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <div data-i18n="Users">Pengguna</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('admin.roles.index*')) active open @endif">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <div data-i18n="Roles">Role</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Manjamen Master Data
            </span>
        </li>
        <li
            class="menu-item @if(Route::is('admin.program-studi.index') || Route::is('admin.calender.index') || Route::is('admin.matakuliah.index') || Route::is('admin.ruangan.index') || Route::is('admin.mahasiswa.index') || Route::is('admin.dosen.index') || Route::is('admin.gelombang.index') || Route::is('admin.evaluasi.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                <div data-i18n="Barang">Data Master</div>
            </a>
            <ul class="menu-sub">
                @can('program-studi-list')
                <li class="menu-item @if(Route::is('admin.program-studi.index')) active @endif">
                    <a href="{{ route('admin.program-studi.index') }}" class="menu-link">
                        <div data-i18n="Program">Program Studi</div>
                    </a>
                </li>
                @endcan

                @can('matakuliah-list')
                <li class="menu-item @if(Route::is('admin.matakuliah.index')) active @endif">
                    <a href="{{ route('admin.matakuliah.index') }}" class="menu-link">
                        <div data-i18n="Matakuliah">Kurikulum</div>
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
                        <div data-i18n="Absensi">Evaluasi Dosen</div>
                    </a>
                </li>
                @endcan
                @can('gelombang-list')
                <li class="menu-item @if(Route::is('admin.gelombang.index')) active @endif">
                    <a href="{{ route('admin.gelombang.index') }}" class="menu-link">
                        <div data-i18n="gelombang">Gelombang</div>
                    </a>
                </li>
                @endcan

                @can('absensi-list')
                <li class="menu-item @if(Route::is('admin.absensi.index')) active @endif">
                    <a href="{{ route('admin.absensi.index') }}" class="menu-link">
                        <div data-i18n="Absensi">Absensi</div>
                    </a>
                </li>
                @endcan
                <li class="menu-item @if(Route::is('admin.calender.index')) active @endif">
                    <a href="{{ route('admin.calender.index') }}" class="menu-link">
                        <div data-i18n="Absensi">Kaleneder Akademik</div>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Mangement Akademik
            </span>
        </li>
        {{-- Perkuliahan --}}
        <li
            class="menu-item @if(Route::is('admin.jadwal.index') || Route::is('admin.jadwal-praktik.index') ||  Route::is('admin.jadwal-uts.index') || Route::is('admin.jadwal-uas.index') ||  Route::is('admin.jadwal-uap.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-calendar"></i>
                <div data-i18n="Barang">Perkuliahan</div>
            </a>
            <ul class="menu-sub">
                @can('jadwal-list')
                <li class="menu-item @if(Route::is('admin.jadwal.index')) active @endif">
                    <a href="{{ route('admin.jadwal.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Jadwal Kuliah</div>
                    </a>
                </li>
                @endcan
                @can('jadwal-praktik-list')
                <li class="menu-item @if(Route::is('admin.jadwal-praktik.index')) active @endif">
                    <a href="{{ route('admin.jadwal-praktik.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Jadwal Praktik</div>
                    </a>
                </li>
                @endcan
                @can('jadwal-uts-list')
                <li class="menu-item @if(Route::is('admin.jadwal-uts.index')) active @endif">
                    <a href="{{ route('admin.jadwal-uts.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Jadwal UTS</div>
                    </a>
                </li>
                @endcan
                @can('jadwal-uas-list')
                <li class="menu-item @if(Route::is('admin.jadwal-uas.index')) active @endif">
                    <a href="{{ route('admin.jadwal-uas.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Jadwal UAS</div>
                    </a>
                </li>
                @endcan
                @can('jadwal-uap-list')
                <li class="menu-item @if(Route::is('admin.jadwal-uap.index')) active @endif">
                    <a href="{{ route('admin.jadwal-uap.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Jadwal UAP</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        {{-- Manajemen Penilaian --}}
        <li
            class="menu-item @if(Route::is('admin.penilaian.index') || Route::is('admin.nilai.index') || Route::is('admin.transkrip.index') || Route::is('admin.input-nilai.index')|| Route::is('admin.uap.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-file-archive"></i>
                <div data-i18n="Barang">Manajemen Penilaian</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('admin.penilaian.index')) active @endif">
                    <a href="{{ route('admin.penilaian.index') }}" class="menu-link">
                        <div data-i18n="Penilaian">Penilain Dosen</div>
                    </a>
                </li>
                @can('input-nilai')
                <li class="menu-item @if(Route::is('admin.input-nilai.index')) active @endif">
                    <a href="{{ route('admin.input-nilai.index') }}" class="menu-link">
                        <div data-i18n="Nilai">Input Nilai</div>
                    </a>
                </li>
                @endcan
                <li class="menu-item @if(Route::is('admin.uap.index')) active @endif">
                    <a href="{{ route('admin.uap.index') }}" class="menu-link">
                        <div data-i18n="Nilai">Input Nilai Uap</div>
                    </a>
                </li>
                @can('list-nilai')
                <li class="menu-item @if(Route::is('admin.transkrip.index')) active @endif">
                    <a href="{{ route('admin.transkrip.index') }}" class="menu-link">
                        <div data-i18n="Nilai">Pengajuan Transkrip</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('admin.nilai.index')) active @endif">
                    <a href="{{ route('admin.nilai.index') }}" class="menu-link">
                        <div data-i18n="Nilai">Transkip Nilai</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>

        <!-- Akademik -->
        <li
            class="menu-item @if(Route::is('admin.index.assign') || Route::is('admin.kurikulum.index')  || Route::is('admin.absensi.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-food-menu"></i>
                <div data-i18n="Barang">Distribusi Mata Kuliah</div>
            </a>
            <ul class="menu-sub">
                @can('kurikulum-list')
                <li class="menu-item @if(Route::is('admin.kurikulum.index')) active @endif">
                    <a href="{{ route('admin.kurikulum.index') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Mata Kuliah</div>
                    </a>
                </li>
                @endcan

                @can('assign-dosen-list')
                <li class="menu-item @if(Route::is('admin.index.assign')) active @endif">
                    <a href="{{ route('admin.index.assign') }}" class="menu-link">
                        <div data-i18n="Kurikulum">Dosen Tambahkan Ke Mata Kuliah</div>
                    </a>
                </li>
                @endcan

                @can('absensi-list')
                <li class="menu-item @if(Route::is('admin.absensi.index')) active @endif">
                    <a href="{{ route('admin.absensi.index') }}" class="menu-link">
                        <div data-i18n="Absensi">Absensi</div>
                    </a>
                </li>
                @endcan
            </ul>
        </li>
        <li
            class="menu-item @if(Route::is('admin.aktivasi.index') || Route::is('admin.tarif.index') || Route::is('admin.tenor-pembayaran.index') || Route::is('admin.tagihan-mahasiswa.index') ) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-book-bookmark"></i>
                <div data-i18n="Barang">Administrasi</div>
            </a>
            <ul class="menu-sub">
                @can('tarif-list')
                <li class="menu-item @if(Route::is('admin.tarif.index')) active @endif">
                    <a href="{{ route('admin.tarif.index') }}" class="menu-link">
                        <div data-i18n="Tarif">Tarif (Semester)</div>
                    </a>
                </li>
                @endcan
                @can('tenor-list')
                <li class="menu-item @if(Route::is('admin.tenor.index')) active @endif">
                    <a href="{{ route('admin.tenor-pembayaran.index') }}" class="menu-link">
                        <div data-i18n="tenor">Tenor Pembayaran</div>
                    </a>
                </li>
                @endcan
                @can('tagihan-list')
                <li class="menu-item @if(Route::is('admin.tagihan.index')) active @endif">
                    <a href="{{ route('admin.tagihan-mahasiswa.index') }}" class="menu-link">
                        <div data-i18n="Tarif">Tagihan Pembayaran</div>
                    </a>
                </li>
                @endcan

                @can('list-aktivasi')
                <li class="menu-item @if(Route::is('admin.aktivasi.index')) active @endif">
                    <a href="{{ route('admin.aktivasi.index') }}" class="menu-link">
                        <div data-i18n="Aktivasi">Aktivasi</div>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        {{--
        Validator Surat Keterangan Pendamping IJazah --}}

        <li class="menu-item @if(Route::is('admin.skpi.index')) active @endif">
            <a href="{{ route('admin.skpi.index') }}" class="menu-link">
                <i class="menu-icon bx bxs-award"></i>
                <span class="menu-text">Validator SKPI</span>
            </a>
        </li>
        <!-- Pengaturan -->
        <li
            class="menu-item @if(Route::is('admin.tahun-ajaran.index') || Route::is('admin.settings.edit') || Route::is('admin.helpdesk.index')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-cog"></i>
                <div data-i18n="Barang">Pengaturan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('admin.tahun-ajaran.index')) active @endif">
                    <a href="{{ route('admin.tahun-ajaran.index') }}" class="menu-link">
                        <div data-i18n="Program">Tahun Ajaran </div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('admin.helpdesk.index')) active @endif">
                    <a href="{{ route('admin.helpdesk.index') }}" class="menu-link">
                        <div data-i18n="Program">Helpdesk </div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('admin.settings.edit')) active @endif">
                    <a href="{{ route('admin.settings.edit') }}" class="menu-link">
                        <span class="menu-text">Setting Aplikasi</span>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</aside>