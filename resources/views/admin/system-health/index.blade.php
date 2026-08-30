@extends('layouts.master')

@section('title', 'Kesehatan Sistem')

@section('content')
@php
    $statusUi = [
        'healthy' => ['success', 'Normal', 'bx-check-circle'],
        'warning' => ['warning', 'Peringatan', 'bx-error'],
        'danger' => ['danger', 'Bermasalah', 'bx-x-circle'],
    ];
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Kesehatan Sistem PAS</h4>
        <p class="text-muted mb-0">Pemeriksaan production dibuat {{ $report['generated_at']->format('d M Y H:i:s') }}.</p>
    </div>
    @can('system-backup-create')
        <form method="POST" action="{{ route('admin.system.backups.create') }}"
            onsubmit="return confirm('Buat backup database dan seluruh file upload sekarang? Proses dapat memerlukan beberapa menit.')">
            @csrf
            <button class="btn btn-primary">
                <i class="bx bx-archive-in me-1"></i>Buat Backup Sekarang
            </button>
        </form>
    @endcan
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3 mb-4">
    @foreach ($report['checks'] as $check)
        @php($ui = $statusUi[$check['status']] ?? $statusUi['warning'])
        <div class="col-md-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="rounded-circle bg-label-{{ $ui[0] }} p-2">
                            <i class="bx {{ $ui[2] }} fs-4"></i>
                        </div>
                        <span class="badge bg-label-{{ $ui[0] }}">{{ $ui[1] }}</span>
                    </div>
                    <h6 class="fw-bold">{{ $check['name'] }}</h6>
                    <p class="text-muted small mb-0 text-wrap">{{ $check['message'] }}</p>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-xl-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bx bx-git-branch me-2"></i>Versi Deployment</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5">Environment</dt>
                    <dd class="col-7"><span class="badge bg-label-info">{{ $report['version']['environment'] }}</span></dd>
                    <dt class="col-5">Branch</dt>
                    <dd class="col-7">{{ $report['version']['branch'] }}</dd>
                    <dt class="col-5">Commit</dt>
                    <dd class="col-7"><code>{{ $report['version']['commit'] }}</code></dd>
                    <dt class="col-5">Laravel</dt>
                    <dd class="col-7">{{ $report['version']['laravel'] }}</dd>
                    <dt class="col-5">PHP</dt>
                    <dd class="col-7">{{ $report['version']['php'] }}</dd>
                    <dt class="col-5">Deploy Terakhir</dt>
                    <dd class="col-7">
                        {{ $report['version']['deployed_at']
                            ? \Carbon\Carbon::parse($report['version']['deployed_at'])->format('d M Y H:i:s')
                            : 'Belum dicatat' }}
                    </dd>
                </dl>
            </div>
        </div>
    </div>

    @can('system-backup-list')
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="bx bx-archive me-2"></i>Backup Terbaru</h5>
                </div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>File</th>
                                <th>Waktu</th>
                                <th>Ukuran</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($backups as $backup)
                                <tr>
                                    <td class="text-wrap"><code>{{ $backup['name'] }}</code></td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($backup['updated_at'])->format('d M Y H:i') }}</td>
                                    <td>{{ number_format($backup['size'] / 1024 / 1024, 2) }} MB</td>
                                    <td class="text-end">
                                        @can('system-backup-download')
                                            <a href="{{ route('admin.system.backups.download', $backup['name']) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="bx bx-download"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Belum ada file backup.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endcan
</div>

@can('system-error-log-list')
    <div class="card shadow-sm border-0 mt-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bx bx-bug me-2"></i>Error Production Terbaru</h5>
            <span class="badge bg-label-secondary">{{ count($errors) }} error</span>
        </div>
        <div class="card-body">
            <div class="accordion" id="systemErrorAccordion">
                @forelse ($errors as $index => $error)
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#systemError{{ $index }}">
                                <span class="badge bg-label-danger me-2">{{ $error['date'] }}</span>
                                <span class="text-truncate">{{ $error['message'] }}</span>
                            </button>
                        </h2>
                        <div id="systemError{{ $index }}" class="accordion-collapse collapse"
                            data-bs-parent="#systemErrorAccordion">
                            <div class="accordion-body text-break"><code>{{ $error['message'] }}</code></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bx bx-check-shield fs-1 d-block mb-2 text-success"></i>
                        Tidak ada error terbaru pada log yang dapat dibaca.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endcan
@endsection
