@section('title', 'Login Admin')

<head>
    <meta charset="utf-8" />
    @php
    $settings = \App\Models\Setting::first();
    @endphp
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->name }} - @yield('title')</title>

    <meta name="description" content="{{ $settings->name }}">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="{{ $settings->name }}">
    <meta itemprop="description" content="{{ $settings->name }}">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="{{ $settings->website_url }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $settings->name }}">
    <meta property="og:description" content="{{ $settings->name }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $settings->name }}">
    <meta name="twitter:description" content="{{ $settings->name }}">

    <!-- Favicon -->
    <link rel="icon"
        href="{{ $settings->favicon ? asset('storage/' . $settings->favicon) : asset('default/favicon.ico') }}"
        type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/pages/page-profile.css') }}">
    <!-- Vendors CSS -->
    <link rel="stylesheet"
        href="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    {{-- Datatables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
    {{-- Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/select2/css/select2.min.css') }}">
    <!-- Page CSS -->
    <!-- Helpers -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('dashboard_assets/assets/js/config.js') }}"></script>
    @stack('head')

</head>

<body class="d-flex justify-content-center align-items-center min-h-100vh">
    <div class="container-fluid d-flex justify-content-center align-items-center vh-100">
        <div class="row shadow-lg rounded overflow-hidden" style="max-width: 1100px; width: 100%;">

            <!-- Kolom Kiri (Gambar) -->
            <div class="col-md-6 d-none d-md-flex bg-light p-0">
                <img src="{{ asset('assets/img/illustrations/login-img.jpg') }}" alt="Login Image"
                    class="img-fluid w-100 h-100" style="object-fit: cover;">
            </div>

            <!-- Kolom Kanan (Form Login) -->
            <div class="col-md-6 bg-white p-5 d-flex flex-column align-items-center">
                <!-- Logo -->
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="img-fluid mb-3"
                    style="max-height: 100px;">

                <!-- Welcome Text -->
                <h4 class="text-center fw-bold mt-3 mb-2">Selamat datang di {{ $settings->name }}.</h4>
                <span class="text-center fw-light mb-4">Silakan login dengan email dan password Anda.</span>

                <!-- Toast Notification -->
                @if(session('error'))
                <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                    <div class="toast align-items-center text-white bg-danger border-0 show" role="alert"
                        aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">{{ session('error') }}</div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                        </div>
                    </div>
                </div>
                @endif

                @include('auth.partials.session-notice')

                <form method="POST" action="{{ route('admin.login') }}" class="w-100">
                    @csrf
                    <!-- Email Input -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autofocus>
                        @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <!-- Password Input -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <input id="password" type="password"
                                class="form-control @error('password') is-invalid @enderror" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <box-icon name='low-vision'></box-icon>
                                <i class="bx bxs-low-vision" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                    </div>

                    <script>
                        document.getElementById('togglePassword').addEventListener('click', function () {
                                            const passwordField = document.getElementById('password');
                                            const passwordIcon = document.getElementById('togglePasswordIcon');
                                            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                                            passwordField.setAttribute('type', type);
                                            passwordIcon.classList.toggle('bi-eye');
                                            passwordIcon.classList.toggle('bi-eye-slash');
                                        });
                    </script>

                    <!-- Remember Me -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" {{ old('remember')
                            ? 'checked' : '' }}>
                        <label class="form-check-label" for="remember">
                            Tetap masuk sampai logout manual
                        </label>
                        <div class="form-text">Gunakan hanya pada perangkat pribadi.</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid gap-2">
                        <button id="admin-login-submit" type="submit" class="btn btn-primary">
                            <span data-login-label>Login</span>
                        </button>
                    </div>
                </form>
                @include('auth.partials.login-cooldown', [
                    'buttonId' => 'admin-login-submit',
                    'storageKey' => 'admin',
                ])

                <!-- Footer -->
                <div class="text-center mt-4">
                    <small>Belum punya akun? Hubungi admin kampus.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Other JS -->
    @include('sweetalert::alert', ['cdn' => "https://cdn.jsdelivr.net/npm/sweetalert2@9"])
</body>
