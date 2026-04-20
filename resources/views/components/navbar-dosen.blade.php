<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme shadow-sm border-0"
    id="layout-navbar">

    <!-- Menu Toggle (Visible on smaller screens) -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <!-- Title / App Name -->
    <div class="navbar-nav-right d-flex align-items-center justify-content-between w-100" id="navbar-collapse">
        <!-- Brand/System Name -->
        <h5 class="mb-0 fw-bold text-primary d-none d-md-block">
            <i class="bx bxs-graduation me-2 align-middle"></i>Portal Dosen
        </h5>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            <!-- Welcome Greeting -->
            <li class="nav-item d-none d-sm-flex align-items-center me-3 border-end pe-3">
                <span class="text-muted fw-light me-1">Hari ini,</span> 
                <span class="fw-semibold text-dark">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</span>
            </li>

            <!-- Dosen User Menu -->
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow d-flex align-items-center" href="javascript:void(0);" data-bs-toggle="dropdown">
                     <div class="me-3 d-none d-sm-block text-end">
                        <span class="d-block fw-bold text-dark mt-1" style="line-height:1;">{{ Auth::guard('dosen')->user()->nama }}</span>
                        <small class="text-muted">Tenaga Pengajar</small>
                    </div>
                    <div class="avatar avatar-online">
                        <img src="{{ auth('dosen')->user()->getProfileImageURL() }}"
                            onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                            alt="Avatar of {{ auth('dosen')->user()->nama }}"
                            class="d-block w-px-40 h-px-40 rounded-circle user-profile-img bg-light shadow-sm" style="object-fit: cover;"
                            id="avatar-profile">
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-2">
                    <li class="dropdown-header bg-light border-bottom mb-2 pb-3 pt-3 text-center">
                        <div class="avatar mx-auto mb-2">
                            <img src="{{ auth('dosen')->user()->getProfileImageURL() }}"
                                onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                                alt="Avatar" class="w-px-50 h-px-50 rounded-circle shadow-sm" style="object-fit: cover;">
                        </div>
                        <h6 class="mb-0 fw-bold">{{ Auth::guard('dosen')->user()->nama }}</h6>
                        <small class="text-muted">{{ Auth::guard('dosen')->user()->nidn ?? 'NIDN Belum Diatur' }}</small>
                    </li>
                    
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('dosen.profile.index') }}">
                            <i class="bx bx-user me-2 text-primary"></i> <span class="align-middle">Pengaturan Profil</span>
                        </a>
                    </li>
                    
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('dosen.index.berita') }}">
                            <i class="bx bx-news me-2 text-info"></i> <span class="align-middle">Berita Kampus</span>
                        </a>
                    </li>

                    <li>
                        <div class="dropdown-divider my-2"></div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 text-danger fw-semibold" href="#" onclick="confirmLogout(event)">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Keluar Sistem</span>
                        </a>
                        <form method="POST" action="{{ route('dosen.logout') }}" id="logout-form-nav" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
            <!-- / Dosen User Menu -->
        </ul>
    </div>
</nav>