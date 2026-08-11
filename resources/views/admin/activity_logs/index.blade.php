@extends('layouts.master')
@section('title', 'Activity Logs')
@section('content')
{{-- Header Card --}}
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <div class="col-md-7">
            <div class="card-body">
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Aktivitas Sistem (Activity Log)
                </h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar riwayat aktivitas dari semua role pengguna (Admin, Dosen, Mahasiswa) di dalam sistem.
                </p>
            </div>
        </div>
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/man-with-laptop-light.png') }}" class="img-fluid"
                    alt="Illustration Activity" style="max-height: 150px;">
            </div>
        </div>
    </div>
</div>

{{-- Statistics Cards --}}
<div class="row mb-4">
    {{-- Total Aktivitas --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">Total Log</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($totalLogs) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-primary">
                            <i class="bx bx-bar-chart-alt-2"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Hari Ini --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">Hari Ini</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($todayLogs) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="bx bx-calendar-check"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- 7 Hari Terakhir --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">7 Hari</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($weekLogs) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-time-five"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Admin --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">Admin</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($adminCount) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-info">
                            <i class="bx bx-shield-quarter"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Dosen --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">Dosen</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($dosenCount) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-success">
                            <i class="bx bx-user-voice"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Mahasiswa --}}
    <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <span class="d-block text-muted mb-1" style="font-size: .8rem;">Mahasiswa</span>
                        <h3 class="card-title mb-0 fw-bold">{{ number_format($mahasiswaCount) }}</h3>
                    </div>
                    <div class="avatar flex-shrink-0">
                        <span class="avatar-initial rounded bg-label-warning">
                            <i class="bx bx-group"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Data Table --}}
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4 flex-wrap">
        <h5 class="card-header mb-0">Riwayat Aktivitas Pengguna</h5>
        <form method="GET" action="{{ route('admin.activity-logs.index') }}" class="d-flex gap-2 p-3">
            <select name="user_type" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">Semua Role</option>
                <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="dosen" {{ request('user_type') == 'dosen' ? 'selected' : '' }}>Dosen</option>
                <option value="mahasiswa" {{ request('user_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
            </select>
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari aktivitas..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            @if(request('search') || request('user_type'))
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            @endif
            <a href="{{ route('admin.activity-logs.pdf', request()->all()) }}" target="_blank" class="btn btn-danger btn-sm">
                <i class="bx bxs-file-pdf me-1"></i> Cetak PDF
            </a>
        </form>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th class="text-center" width="50px">No</th>
                    <th>User</th>
                    <th>Role</th>
                    <th>Aktivitas</th>
                    <th>Deskripsi</th>
                    <th>IP Address</th>
                    <th>Waktu</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $key => $log)
                <tr>
                    <td class="text-center">{{ $logs->firstItem() + $key }}</td>
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-primary">{{ optional($log->user)->name ?? optional($log->user)->nama ?? 'User tidak ditemukan' }}</span>
                            <small class="text-muted">ID: {{ $log->user_id }}</small>
                        </div>
                    </td>
                    <td>
                        @if($log->user_type == 'admin')
                            <span class="badge bg-label-info">Admin</span>
                        @elseif($log->user_type == 'dosen')
                            <span class="badge bg-label-success">Dosen</span>
                        @else
                            <span class="badge bg-label-warning">Mahasiswa</span>
                        @endif
                    </td>
                    <td><span class="fw-semibold">{{ str_replace('_', ' ', ucwords($log->aktivitas, '_')) }}</span></td>
                    <td style="white-space: normal; min-width: 200px;" class="text-muted">{{ $log->deskripsi }}</td>
                    <td>{{ $log->ip_address ?? '-' }}</td>
                    <td>{{ $log->created_at->translatedFormat('d M Y, H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">Belum ada data aktivitas pengguna.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center p-3">
        {!! $logs->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
