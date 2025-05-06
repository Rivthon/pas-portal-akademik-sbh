<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">

    <!-- Menu Toggle -->
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    <!-- Navbar Right -->
    <div class="navbar-nav-right d-flex align-items-center w-100" id="navbar-collapse">
        <h6 class="mb-0 text-primary fw-semibold me-auto">{{ $settings->name }}</h6>

        <ul class="navbar-nav flex-row align-items-center">

            <!-- Search -->
            <li class="nav-item mx-2 mt-auto mb-auto flex-nowrap">
                <form class="d-none d-md-block">
                    <div class="input-group input-group-dynamic flex-nowrap">
                        <span class="input-group-text bg-white"><i class="bx bx-search"></i></span>
                        <input type="text" class="form-control" placeholder="Cari data..." />
                    </div>
                </form>
            </li>

            <!-- Notifications -->
            <li class="nav-item dropdown mx-2">
                <a class="nav-link dropdown-toggle hide-arrow" href="#" data-bs-toggle="dropdown" title="Notifikasi">
                    <i class="bx bx-bell bx-sm"></i>
                    <span class="badge bg-danger rounded-pill badge-notifications">3</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#">Notifikasi 1</a></li>
                    <li><a class="dropdown-item" href="#">Notifikasi 2</a></li>
                </ul>
            </li>

            <!-- User Menu -->
            <li class="nav-item dropdown dropdown-user">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown"
                    title="Profil">
                    <div class="avatar avatar-online">
                        <img src="{{ auth()->user()->getProfileImageURL() }}"
                            onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                            alt="Admin Avatar" class="w-px-40 h-auto rounded-circle bg-light shadow-sm" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="avatar avatar-online">
                                        <img src="{{ auth()->user()->getProfileImageURL() }}"
                                            onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                                            alt="Admin Avatar" class="w-px-40 h-auto rounded-circle bg-light" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ auth()->user()->name }}</span>
                                    @foreach (auth()->user()->getRoleNames() as $role)
                                    <small class="text-muted">{{ $role }}</small><br>
                                    @endforeach
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('admin.profile.index') }}">
                            <i class="bx bx-user"></i> <span class="align-middle">Profile</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="bx bx-power-off me-2"></i> <span class="align-middle">Log Out</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </li>

        </ul>
    </div>

</nav>