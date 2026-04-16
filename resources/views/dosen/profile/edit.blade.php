@extends('layouts.dosen')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <!-- User Profile Header -->
    <div class="col-12">
        <div class="card mb-4">
            <div class="user-profile-header-banner">
                <img src="{{ asset('dashboard_assets/assets/img/front-pages/backgrounds/cta-bg-light.png') }}"
                    alt="Banner image" class="rounded-top w-100">
            </div>
            <div class="user-profile-header d-flex flex-column flex-sm-row text-sm-start text-center mb-4">
                <div class="flex-shrink-0 mt-n2 mx-sm-0 mx-auto">
                    <img src="{{ auth('dosen')->user()->getProfileImageURL() }}"
                        onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                        alt="Avatar of {{ auth('dosen')->user()->nama    }}"
                        class="d-block h-100 ms-0 ms-sm-4 rounded user-profile-img bg-light shadow-sm"
                        id="avatar-profile">
                </div>
                <div class="flex-grow-1 mt-3 mt-sm-5">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4>{{ auth('dosen')->user()->nama }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                <li class="list-inline-item fw-medium" style="text-transform: capitalize;">
                                    <i class='bx bx-pen'></i>
                                    {{ auth('dosen')->user()->programStudi->pluck('nama')->implode(', ') }}
                                </li>
                                <li class="list-inline-item fw-medium">
                                    <i class='bx bx-calendar-alt'></i> Terdaftar
                                    {{ auth('dosen')->user()->created_at?->translatedFormat('d F Y') }}
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Edit Profile Tes</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('dosen.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-3">
                        <label for="nama" class="form-label">Name</label>
                        <input type="text" name="nama" id="nama"
                            class="form-control @error('nama') is-invalid @enderror"
                            value="{{ old('nama', auth('dosen')->user()->nama) }}" required>
                        @error('nama')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email"
                            class="form-control @error('email') is-invalid @enderror"
                            value="{{ old('email', auth('dosen')->user()->email) }}" required>
                        @error('email')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password"
                            class="form-control @error('password') is-invalid @enderror">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                        @error('password')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control">
                    </div>

                    <!-- Avatar Field -->
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" name="avatar" id="avatar"
                            class="form-control @error('avatar') is-invalid @enderror">
                        <small class="text-muted">Unggah PNG, JPG, JPEG. Ukuran maks: 500 KB.</small>
                        @error('avatar')
                        <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Current Avatar Preview -->
                    @if (auth('dosen')->user()->avatar)
                    <div class="mb-3">
                        <p>Avatar Saat Ini:</p>
                        <img src="{{ asset('storage/' . auth('dosen')->user()->avatar) }}" alt="Avatar"
                            class="img-thumbnail" width="150">
                    </div>
                    @endif

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection