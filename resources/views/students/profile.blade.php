@extends('layouts.mahasiswa')

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
                    <img src="{{ auth('mahasiswa')->user()->getProfileImageURL() }}"
                        onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"
                        alt="Avatar of {{ auth('mahasiswa')->user()->name }}"
                        class="d-block h-100 ms-0 ms-sm-4 rounded user-profile-img bg-light shadow-sm"
                        id="avatar-profile">
                </div>
                <div class="flex-grow-1 mt-3 mt-sm-5">
                    <div
                        class="d-flex align-items-md-end align-items-sm-start align-items-center justify-content-md-between justify-content-start mx-4 flex-md-row flex-column gap-4">
                        <div class="user-profile-info">
                            <h4>{{ auth()->user()->name }}</h4>
                            <ul
                                class="list-inline mb-0 d-flex align-items-center flex-wrap justify-content-sm-start justify-content-center gap-2">
                                {{-- <li class="list-inline-item fw-medium" style="text-transform: capitalize;">
                                    <i class='bx bx-pen'></i> @foreach (auth()->user()->getRoleNames() as $role)
                                    {{ $role }}
                                    @endforeach
                                </li> --}}
                                {{-- <li class="list-inline-item fw-medium">
                                    <i class='bx bx-calendar-alt'></i> Terdaftar {{
                                    auth()->user()->created_at?->translatedFormat('d F Y') }}
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /User Profile Header -->

    <!-- Edit Profile Form -->
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5>Edit Profile</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('mahasiswa.profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" name="nama" id="name" class="form-control"
                            value="{{ old('name', auth('mahasiswa')->user()->nama) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control"
                            value="{{ old('email', auth('mahasiswa')->user()->email) }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control">
                        <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                            class="form-control">
                    </div>

                    <div class="mb-3">
                        <label for="avatar" class="form-label">Avatar</label>
                        <input type="file" name="avatar" id="avatar" class="form-control">
                        <small class="text-muted">Unggah PNG, JPG, JPEG. Ukuran maks: 500 KB.</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    </div>
    <!-- /Edit Profile Form -->
</div>
{{--
<h3>Profil Saya</h3>

@if (session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<form action="{{ route('mahasiswa.profile.update') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label">Nama</label>
        <input type="text" name="nama" id="nama" class="form-control" value="{{ $mahasiswa->nama }}" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" id="email" class="form-control" value="{{ $mahasiswa->email }}" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password Baru (Opsional)</label>
        <input type="password" name="password" id="password" class="form-control">
    </div>
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
</form> --}}
@endsection