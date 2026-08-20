@if (Auth::guard('dosen')->check())
<script>
    window.location.href = "{{ route('dosen.dashboard') }}";
</script>
@endif

@extends('auth.auth-login')
@section('title', 'Login Dosen')

@section('content')
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "error-container": "#ffdad6", "on-primary-fixed": "#001a41", "on-secondary-fixed-variant": "#3a485b", "tertiary-fixed": "#ffdbcb", "surface": "#f8f9fa", "tertiary": "#9e4300", "inverse-primary": "#adc7ff", "outline": "#727785", "primary-fixed": "#d8e2ff", "on-surface": "#191c1d", "secondary-container": "#d5e3fc", "on-primary": "#ffffff", "primary": "#005bbf", "secondary": "#515f74", "surface-container-highest": "#e1e3e4", "outline-variant": "#c1c6d6", "secondary-fixed-dim": "#b9c7df", "on-primary-container": "#ffffff", "tertiary-fixed-dim": "#ffb691", "surface-variant": "#e1e3e4", "primary-fixed-dim": "#adc7ff", "surface-container-low": "#f3f4f5", "on-tertiary-fixed": "#341100", "surface-bright": "#f8f9fa", "on-tertiary": "#ffffff", "surface-tint": "#005bc0", "background": "#f8f9fa", "surface-dim": "#d9dadb", "inverse-on-surface": "#f0f1f2", "on-error": "#ffffff", "on-error-container": "#93000a", "on-surface-variant": "#414754", "primary-container": "#1a73e8", "error": "#ba1a1a", "on-secondary-fixed": "#0d1c2e", "on-tertiary-fixed-variant": "#783100", "tertiary-container": "#c55500", "on-secondary": "#ffffff", "secondary-fixed": "#d5e3fc", "surface-container-high": "#e7e8e9", "on-secondary-container": "#57657a", "on-tertiary-container": "#0e0200", "surface-container-lowest": "#ffffff", "surface-container": "#edeeef", "inverse-surface": "#2e3132", "on-primary-fixed-variant": "#004493", "on-background": "#191c1d"
                },
                "spacing": {
                    "lg": "1.5rem", "xl": "2.5rem", "sm": "0.5rem", "container-max": "1280px", "md": "1rem", "base": "4px", "xs": "0.25rem", "gutter": "1.5rem"
                },
                "fontFamily": {
                    "body-sm": ["Inter"], "body-md": ["Inter"], "body-lg": ["Inter"], "h2": ["Inter"], "h1": ["Inter"], "h3": ["Inter"], "label-md": ["Inter"], "label-sm": ["Inter"]
                }
            }
        }
    }
</script>

<div class="bg-background text-on-background min-h-screen flex items-center justify-center font-body-md text-body-md antialiased p-4 md:p-0">

    @if(session('error'))
    <div id="loginToast" class="fixed top-5 right-5 z-50 flex items-center w-full max-w-xs p-4 space-x-3 text-on-error-container bg-error-container rounded-lg shadow-lg transition-opacity duration-500" role="alert">
        <span class="material-symbols-outlined text-error">error</span>
        <div class="font-body-sm text-body-sm flex-1">{{ session('error') }}</div>
        <button type="button" class="ml-auto -mx-1.5 -my-1.5 text-on-error-container hover:bg-error/20 rounded-lg focus:ring-2 focus:ring-error p-1.5 inline-flex h-8 w-8 transition-colors" onclick="document.getElementById('loginToast').style.display='none'">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>
    @endif

    <div class="w-full max-w-container-max min-h-[1024px] md:min-h-[870px] bg-surface rounded-xl shadow-[0_4px_6px_rgba(0,0,0,0.07)] border border-surface-container-high overflow-hidden flex flex-col md:flex-row">

        <div class="hidden md:flex flex-col md:w-1/2 bg-primary-fixed relative p-xl overflow-hidden justify-between" style="background-image: url('{{ asset('assets/img/illustrations/login-dosen2.webp') }}'); background-size: cover; background-position: center;">
            <div class="absolute inset-0 bg-primary-fixed/80 mix-blend-multiply"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-on-primary-fixed/90 via-on-primary-fixed/40 to-transparent"></div>

            <div class="relative z-10">
                <div class="flex items-center gap-sm text-on-primary">
                    @if(isset($settings->logo))
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="h-12 w-auto object-contain">
                    @else
                        <span class="material-symbols-outlined text-[32px]">school</span>
                    @endif
                    <!-- <span class="font-h3 text-h3 tracking-tight">{{ $settings->name ?? 'Portal Akademik' }}</span> -->
                </div>
            </div>

            <div class="relative z-10 text-on-primary max-w-md">
                <h2 class="font-h2 text-h2 mb-sm text-on-primary">Sistem Informasi Akademik Dosen</h2>
                <p class="font-body-lg text-body-lg text-primary-fixed-dim">Kelola perkuliahan, jadwal mengajar, serta penilaian mahasiswa STIKes Bogor Husada dalam satu platform terpadu.</p>
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center p-lg md:p-xl lg:p-[4rem] bg-surface">
            <div class="w-full max-w-[400px]">

                <div class="flex md:hidden items-center justify-center gap-sm mb-xl text-primary">
                    @if(isset($settings->logo))
                        <img src="{{ asset('storage/' . $settings->logo) }}" alt="Logo" class="h-10 w-auto object-contain">
                    @else
                        <span class="material-symbols-outlined text-[32px]">school</span>
                    @endif
                    <span class="font-h3 text-h3 tracking-tight text-on-surface">{{ $settings->name ?? 'Portal Akademik' }}</span>
                </div>

                <div class="mb-xl text-center md:text-left">
                    <h1 class="font-h2 text-h2 text-on-surface mb-xs">Selamat datang di {{ $settings->name ?? 'Portal Akademik' }}.</h1>
                    <p class="font-body-md text-body-md text-on-surface-variant">Silakan login dengan NIDN atau Email serta Password Anda.</p>
                </div>

                @include('auth.partials.session-notice')

                <form method="POST" action="{{ route('dosen.login') }}" class="space-y-lg">
                    @csrf

                    <div class="space-y-sm">
                        <label class="block font-label-md text-label-md text-on-surface" for="email">NIDN atau Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-sm flex items-center pointer-events-none text-outline">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <input class="w-full pl-xl pr-md py-md bg-surface border @error('email') border-error @else border-outline-variant @enderror rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-fixed transition-shadow placeholder:text-outline-variant"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Masukkan NIDN atau email"
                                type="text"
                                required autofocus/>
                        </div>
                        @error('email')
                            <span class="font-body-sm text-body-sm text-error mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="space-y-sm">
                        <label class="block font-label-md text-label-md text-on-surface" for="password">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-sm flex items-center pointer-events-none text-outline">
                                <span class="material-symbols-outlined">lock</span>
                            </div>
                            <input class="w-full pl-xl pr-xl py-md bg-surface border @error('password') border-error @else border-outline-variant @enderror rounded-lg font-body-md text-body-md text-on-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary-fixed transition-shadow placeholder:text-outline-variant"
                                id="password"
                                name="password"
                                placeholder="Masukkan password"
                                type="password"
                                required/>
                            <button id="togglePassword" class="absolute inset-y-0 right-0 pr-sm flex items-center text-outline hover:text-on-surface-variant transition-colors" type="button">
                                <span id="togglePasswordIcon" class="material-symbols-outlined">visibility_off</span>
                            </button>
                        </div>
                        @error('password')
                            <span class="font-body-sm text-body-sm text-error mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-xs cursor-pointer group">
                            <input class="w-4 h-4 rounded border-outline-variant text-primary focus:ring-primary focus:ring-offset-surface cursor-pointer"
                                type="checkbox"
                                name="remember"
                                id="remember"
                                value="1"
                                {{ old('remember') ? 'checked' : '' }}/>
                            <span class="font-body-sm text-body-sm text-on-surface-variant group-hover:text-on-surface transition-colors">Tetap masuk sampai logout manual</span>
                        </label>
                        <a class="font-label-md text-label-md text-primary hover:text-on-primary-fixed-variant transition-colors" href="#">Lupa Password?</a>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant -mt-md">Gunakan hanya pada perangkat pribadi.</p>

                    <button id="dosen-login-submit" class="w-full py-md bg-primary text-on-primary rounded-lg font-label-md text-label-md hover:bg-on-primary-fixed-variant focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 focus:ring-offset-surface transition-all active:scale-[0.98] shadow-[0_1px_3px_rgba(0,0,0,0.1)] hover:shadow-[0_4px_6px_rgba(0,0,0,0.15)] flex justify-center items-center gap-xs" type="submit">
                        <span data-login-label>Login</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </button>
                </form>
                @include('auth.partials.login-cooldown', [
                    'buttonId' => 'dosen-login-submit',
                    'storageKey' => 'dosen',
                ])

                <div class="mt-xl text-center font-body-sm text-body-sm text-on-surface-variant">
                    Belum punya akun? <a class="font-label-md text-label-md text-primary hover:underline hover:text-on-primary-fixed-variant transition-colors" href="#">Hubungi admin kampus.</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Toggle Password Logic
        const toggleBtn = document.getElementById('togglePassword');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const passwordField = document.getElementById('password');
                const passwordIcon = document.getElementById('togglePasswordIcon');

                if (passwordField.getAttribute('type') === 'password') {
                    passwordField.setAttribute('type', 'text');
                    passwordIcon.textContent = 'visibility';
                } else {
                    passwordField.setAttribute('type', 'password');
                    passwordIcon.textContent = 'visibility_off';
                }
            });
        }

        // Auto hide toast error message setelah 5 detik
        const toast = document.getElementById('loginToast');
        if(toast) {
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.style.display = 'none', 500);
            }, 5000);
        }
    });
</script>
@endsection
