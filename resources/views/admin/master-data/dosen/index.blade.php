@extends('layouts.master')
@section('title', 'Data Dosen')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Dosen Pengajar
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar dosen yang terdaftar di sistem. Silakan tambahkan dosen baru jika diperlukan.
                </p>
                <!-- CTA Button -->
                @can('dosen-create')
                <div class="mb-3">
                    <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary">
                        Tambah Dosen
                    </a>
                </div>
                @endcan
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/mahasiswa-2.png') }}" class="img-fluid"
                    alt="Illustration of dosen" style="max-height: 200px;" onerror="this.src='{{ asset('dashboard_assets/assets/img/illustrations/man-with-laptop-light.png') }}'">
            </div>
        </div>
    </div>
</div>

@if (session('reset_password_result'))
<div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
    <div class="d-flex align-items-center gap-3 flex-wrap">
        <i class="bx bx-check-shield fs-3"></i>
        <div class="flex-grow-1">
            Password baru untuk <strong>{{ session('reset_password_result.nama') }}</strong>:
            <code class="fs-6 user-select-all">{{ session('reset_password_result.password') }}</code>
            <div class="small mt-1">Salin password ini sekarang. Password hanya ditampilkan satu kali.</div>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
</div>
@endif

<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Dosen</h5>
        <form class="d-flex" action="{{ route('admin.dosen.index') }}" method="GET">
            <input type="text" name="search" class="form-control me-2" placeholder="Cari Dosen..." value="{{ request()->get('search') }}">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Dosen Pengajar
            </caption>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Avatar</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Kode Dosen / NIDN</th>
                    <th>Program Studi</th>
                    <th width="200px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($dosen as $key => $r)
                <tr>
                    <td>{{ $dosen->firstItem() + $key }}</td>
                    <td>
                        <img src="{{ $r->avatar ? asset('storage/' . $r->avatar) : asset('dashboard_assets/assets/img/avatars/1.png') }}" alt="Avatar" class="rounded-circle"
                            style="width: 40px; height: 40px; object-fit: cover;"
                            onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'" />
                    </td>
                    <td>{{ $r->nama }}</td>
                    <td>{{ $r->email }}</td>
                    <td>
                        @if($r->kd_dosen)
                        {{ $r->kd_dosen }}<br>
                        @endif
                        <small class="text-muted">{{ $r->nidn }}</small>
                    </td>
                    <td>{{ $r->programStudi->nama ?? '-' }}</td>
                    <td>
                        <form action="{{ route('admin.dosen.destroy', $r->dosen_id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')

                            @can('dosen-edit')
                            <a class="btn btn-primary btn-sm"
                                href="{{ route('admin.dosen.edit', $r->dosen_id) }}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            @endcan

                            @can('dosen-delete')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus dosen ini?')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                            @endcan
                        </form>

                        @can('dosen-reset-password')
                        <form action="{{ route('admin.dosen.reset-password', $r->dosen_id) }}" method="POST"
                            class="d-inline"
                            onsubmit="return confirm('Reset password dosen ini dengan password acak?')">
                            @csrf
                            <button type="submit" class="btn btn-warning btn-sm"
                                data-bs-toggle="tooltip" data-bs-original-title="Reset Password">
                                <i class="bx bx-reset"></i> Reset Password
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($dosen->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {!! $dosen->appends(request()->query())->links('pagination::bootstrap-5') !!}
    </div>
    @endif
</div>
@endsection
