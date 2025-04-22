@if (Auth::check())
<script>
    window.location.href = "{{ route('mahasiswa.dashboard') }}";
</script>
@endif

@extends('auth.auth-login')
@section('title', 'Login Mahasiswa')
@section('content')
<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="row shadow-lg rounded overflow-hidden" style="max-width: 1100px; width: 100%;">
        <!-- Kolom Kiri (Gambar) -->
        <!-- Kolom Kiri (Gambar Full) -->
        <div class="col-md-6 d-none d-md-flex bg-light p-0">
            <img src="{{ asset('assets/img/illustrations/login-img.jpg') }}" alt="Login Image"
                class="img-fluid w-100 h-100" style="object-fit: cover;">
        </div>

        <!-- Kolom Kanan (Form Login) -->
        <div class="col-md-6 bg-white p-5 d-flex flex-column align-items-center">
            <!-- Logo -->
            <!-- Logo -->
            <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="img-fluid mb-3"
                style="max-height: 100px;">
            <!-- Welcome Text -->
            <h4 class="text-center fw-bold mt-3 mb-2">
                Selamat datang di {{ $settings->name }}.
            </h4>
            <span class="text-center fw-light mb-4">Silakan login dan masukkan username serta password Anda.</span>
            <!-- Toast Notification -->
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 1050">
                <div id="loginToast" class="toast align-items-center text-white bg-danger border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('error') }}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
            <form method="POST" action="{{ route('mahasiswa.mhs.login') }}" class="w-100">
                @csrf
                <!-- Email Input -->
                <div class="mb-3">
                    <label for="email" class="form-label">NIM atau Email</label>
                    <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email"
                        value="{{ old('email') }}" required autofocus>
                    @error('email')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
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
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember')
                        ? 'checked' : '' }}>
                    <label class="form-check-label" for="remember">
                        Remember Me
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>

            <!-- Footer -->
            <div class="text-center mt-4">
                <small>Belum punya akun? Hubungi admin kampus.</small>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk Menampilkan Toast -->
@if(session('error'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var loginToast = new bootstrap.Toast(document.getElementById('loginToast'));
        loginToast.show();
    });
</script>
@endif
@endsection