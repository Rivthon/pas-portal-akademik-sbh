@php
use Illuminate\Support\Facades\Auth;
$user = Auth::guard('mahasiswa')->user();
@endphp
<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme shadow-sm">
    <div class="app-brand demo justify-content-center py-3">
        <a href="#" class="app-brand-link d-flex align-items-center gap-2">
            <img src="{{ $settings->logo ? asset('storage/' . $settings->logo) : asset('default/logo.ico') }}"
                class="navbar-brand-img" style="height: 40px; width: auto; object-fit: contain;" alt="main_logo">
            <span class="app-brand-text demo menu-text fw-bolder ms-2 text-primary" style="font-size: 1.25rem;">{{ explode(' ', $settings->name)[0] }}</span>
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

        <li class="menu-item @if(Route::is('mahasiswa.dashboard*')) active @endif">
            <a href="{{ route('mahasiswa.dashboard') }}" class="menu-link">
                <i class="menu-icon bx bxs-dashboard text-info"></i>
                <span class="menu-text fw-medium">Dashboard Utama</span>
            </a>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Perkuliahan</span>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.rps*')) active @endif">
            <a href="{{ route('mahasiswa.rps.index') }}" class="menu-link">
                <i class="menu-icon bx bx-file text-primary"></i>
                <div class="fw-medium">RPS</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.lms.*') && !Route::is('mahasiswa.lms.gradebook.*')) active @endif">
            <a href="{{ route('mahasiswa.lms.index') }}" class="menu-link">
                <i class="menu-icon bx bx-book-reader text-primary"></i>
                <div class="fw-medium">LMS</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.lms.gradebook.*')) active @endif">
            <a href="{{ route('mahasiswa.lms.gradebook.index') }}" class="menu-link">
                <i class="menu-icon bx bx-bar-chart-square text-success"></i>
                <div class="fw-medium">Nilai LMS</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.rekap.absensi*')) active open @endif">
            <a href="{{ route('mahasiswa.rekap.absensi') }}" class="menu-link">
                <i class="menu-icon bx bxs-report text-info"></i>
                <div class="fw-medium">Rekap Absensi</div>
            </a>
        </li>
        <li class="menu-item @if(Route::is('mahasiswa.absensi-praktik.*')) active @endif">
            <a href="{{ route('mahasiswa.absensi-praktik.index') }}" class="menu-link">
                <i class="menu-icon bx bx-test-tube text-success"></i>
                <div class="fw-medium">Riwayat Praktik</div>
            </a>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.jadwal*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-calendar-event text-warning"></i>
                <div class="fw-medium">Jadwal Perkuliahan</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-kuliah.*')) active @endif">
                    <a href="{{ route('mahasiswa.jadwal-kuliah.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-book-reader"></i>
                        <div>Jadwal Kuliah</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-praktik.*')) active @endif">
                    <a href="{{ route('mahasiswa.jadwal-praktik.index') }}" class="menu-link">
                        <i class="menu-icon bx bxs-book-bookmark"></i>
                        <div>Jadwal Praktik</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uts.*')) active @endif">
                    <a href="{{ route('mahasiswa.jadwal-uts.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-pencil"></i>
                        <div>Jadwal UTS</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uas.*')) active @endif">
                    <a href="{{ route('mahasiswa.jadwal-uas.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-edit-alt"></i>
                        <div>Jadwal UAS</div>
                    </a>
                </li>

                @if($user && $user->jurusan_id == 15401)
                <li class="menu-item @if(Route::is('mahasiswa.jadwal-uap.*')) active @endif">
                    <a href="{{ route('mahasiswa.jadwal-uap.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-highlight"></i>
                        <div>Jadwal UAP {{ $user->jurusan_id }}</div>
                    </a>
                </li>
                @endif
            </ul>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Modul Akademik</span>
        </li>
        <li class="menu-item @if(Route::is('mahasiswa.krs.*') || Route::is('mahasiswa.status.krs.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-book-bookmark text-success"></i>
                <div class="fw-medium">Kartu Rencana Studi</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.krs.index')) active @endif">
                    <a href="{{ route('mahasiswa.krs.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-edit"></i>
                        <div>Pengajuan KRS</div>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('mahasiswa.status.krs.index')) active @endif">
                    <a href="{{ route('mahasiswa.status.krs.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-check-shield"></i>
                        <div>Status KRS</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Route::is('mahasiswa.kartu-hasil.index') ? 'active' : '' }}">
            <a href="{{ $user && $user->status_akhir == 1 ? route('mahasiswa.kartu-hasil.index') : '#' }}"
                class="menu-link {{ $user && $user->status_akhir == 0 ? 'disabled opacity-50' : '' }}"
                @if($user && $user->status_akhir == 0)
                data-bs-toggle="modal"
                data-bs-target="#blockedModal"
                @endif
                >
                <i class="menu-icon bx bxs-certification text-danger"></i>
                <span class="menu-text fw-medium">Kartu Hasil Studi</span>

                @if($user && $user->status_akhir == 0)
                <i class="bx bxs-lock-alt ms-auto text-danger" title="Akses Dikunci"></i>
                @endif
            </a>
        </li>

        <li class="menu-item {{ Route::is('mahasiswa.khs.riwayat') ? 'active' : '' }}">
            <a href="{{ route('mahasiswa.khs.riwayat') }}" class="menu-link">
                <i class="menu-icon bx bx-archive text-secondary"></i>
                <span class="menu-text fw-medium">Riwayat KHS</span>
            </a>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.nilai-uts.index') || Route::is('mahasiswa.nilai-uas.index') || Route::is('mahasiswa.uap.index') || Route::is('mahasiswa.pengajuan.index') ) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-bar-chart-alt-2" style="color: #696cff;"></i>
                <div class="fw-medium">Manajemen Nilai</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.nilai-uts.index')) active @endif">
                    <a href="{{ route('mahasiswa.nilai-uts.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-chart"></i>
                        <div>Nilai UTS</div>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('mahasiswa.nilai-uas.index')) active @endif">
                    <a href="{{ route('mahasiswa.nilai-uas.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-line-chart"></i>
                        <div>Nilai UAS</div>
                    </a>
                </li>

                @if ($user && $user->jurusan_id == 15401 && $user->semester == 6)
                <li class="menu-item @if(Route::is('mahasiswa.uap.index')) active @endif">
                    <a href="{{ route('mahasiswa.uap.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-scatter-chart"></i>
                        <div>Nilai Uap {{ $user->jurusan_id }}</div>
                    </a>
                </li>
                @endif

                <li class="menu-item @if(Route::is('mahasiswa.pengajuan.index')) active @endif">
                    <a href="{{ route('mahasiswa.pengajuan.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-file"></i>
                        <div>Transkrip Nilai</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item @if(Route::is('mahasiswa.skpi.index')) active @endif">
            <a href="{{ route('mahasiswa.skpi.index') }}" class="menu-link">
                <i class="menu-icon bx bxs-medal text-warning"></i>
                <span class="menu-text fw-medium">Aktivitas & Prestasi</span>
            </a>
        </li>

        <li class="menu-header small text-uppercase mt-3">
            <span class="menu-header-text fw-bold text-primary" style="letter-spacing: 0.5px;">Informasi & Akun</span>
        </li>
        <li class="menu-item @if(Route::is('mahasiswa.pedoman-akademik.*')) active @endif">
            <a href="{{ route('mahasiswa.pedoman-akademik.index') }}" class="menu-link">
                <i class="menu-icon bx bxs-book-open text-primary"></i>
                <div class="fw-medium">Pedoman Akademik</div>
            </a>
        </li>
        <li class="menu-item @if(Route::is('mahasiswa.permintaan.*') || Route::is('mahasiswa.index.berita') || Route::is('mahasiswa.administrasi.*') || Route::is('mahasiswa.profile.*')) active open @endif">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon bx bxs-cog text-secondary"></i>
                <div class="fw-medium">Layanan Mahasiswa</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item @if(Route::is('mahasiswa.permintaan.index')) active @endif">
                    <a href="{{ route('mahasiswa.permintaan.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-message-square-detail"></i>
                        <div>Feedback & Saran</div>
                    </a>
                </li>

                <li class="menu-item @if(Route::is('mahasiswa.administrasi.index')) active @endif">
                    <a href="{{ route('mahasiswa.administrasi.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-wallet"></i>
                        <div>Administrasi</div>
                    </a>
                </li>
                <li class="menu-item @if(Route::is('mahasiswa.profile.index')) active @endif">
                    <a href="{{ route('mahasiswa.profile.index') }}" class="menu-link">
                        <i class="menu-icon bx bx-user-circle"></i>
                        <div>Edit Profil</div>
                    </a>
                </li>
                <li class="menu-item mt-2 pt-2 border-top">
                    <a href="javascript:void(0);" class="menu-link" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                        <i class="menu-icon bx bx-power-off text-danger"></i>
                        <span class="menu-text text-danger fw-bold">Keluar Sistem</span>
                    </a>
                    <form method="POST" action="{{ route('mahasiswa.logout') }}" id="logout-form-sidebar" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</aside>

<div class="modal fade" id="blockedModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold">
                    <i class="bx bxs-error-circle me-2"></i> Akses Ditolak
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bx bxs-lock-alt text-danger" style="font-size: 4rem;"></i>
                </div>
                <h5 class="fw-bold mb-2">Kunci Akademik Aktif</h5>
                <p class="mb-0 text-muted">
                    Anda belum menyelesaikan seluruh kewajiban administrasi atau akademik.<br>
                    Silahkan berkoordinasi dengan bagian administrasi untuk membuka akses KHS Anda.
                </p>
            </div>
            <div class="modal-footer bg-light border-0">
                <button type="button" class="btn btn-secondary shadow-sm rounded-pill px-4" data-bs-dismiss="modal">
                    Mengerti
                </button>
            </div>
        </div>
    </div>
</div>
