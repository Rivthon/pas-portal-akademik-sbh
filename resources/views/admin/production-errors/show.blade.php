@extends('layouts.master')

@section('title', 'Detail Error Production')

@section('content')
@php
    $academic = data_get($error->context, 'akademik', []);
@endphp
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Detail Error Production #{{ $error->id }}</h4>
        <p class="text-muted mb-0">Fingerprint: <code>{{ $error->fingerprint }}</code></p>
    </div>
    <a href="{{ route('admin.system.errors.index') }}" class="btn btn-label-secondary">
        <i class="bx bx-arrow-back me-1"></i>Kembali
    </a>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="bx bx-error-circle text-danger me-2"></i>Informasi Error</h5>
            </div>
            <div class="card-body">
                <dl class="row mb-0 g-2">
                    <dt class="col-md-3">Status</dt><dd class="col-md-9"><span class="badge bg-label-danger">HTTP {{ $error->http_status }}</span></dd>
                    <dt class="col-md-3">Exception</dt><dd class="col-md-9 text-break"><code>{{ $error->exception_class ?: 'Respons HTTP tanpa exception' }}</code></dd>
                    <dt class="col-md-3">Pesan</dt><dd class="col-md-9 text-break">{{ $error->message }}</dd>
                    <dt class="col-md-3">Lokasi</dt><dd class="col-md-9 text-break"><code>{{ $error->file ?: '-' }}{{ $error->line ? ':'.$error->line : '' }}</code></dd>
                    <dt class="col-md-3">Route</dt><dd class="col-md-9"><code>{{ $error->route_name ?: '-' }}</code></dd>
                    <dt class="col-md-3">Request</dt><dd class="col-md-9 text-break"><code>{{ $error->method }} {{ $error->path }}</code></dd>
                    <dt class="col-md-3">Pertama terjadi</dt><dd class="col-md-9">{{ $error->first_seen_at?->format('d M Y H:i:s') }}</dd>
                    <dt class="col-md-3">Terakhir terjadi</dt><dd class="col-md-9">{{ $error->last_seen_at?->format('d M Y H:i:s') }}</dd>
                    <dt class="col-md-3">Total kejadian</dt><dd class="col-md-9"><span class="badge bg-label-warning">{{ number_format($error->occurrences) }} kali</span></dd>
                </dl>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="bx bx-code-alt me-2"></i>Stack Trace</h5></div>
            <div class="card-body">
                @if($error->trace)
                    <pre class="bg-dark text-white rounded p-3 mb-0" style="max-height:520px; overflow:auto; white-space:pre-wrap; font-size:.78rem;">{{ $error->trace }}</pre>
                @else
                    <p class="text-muted mb-0">Stack trace tidak tersedia karena error berasal dari respons 500 yang ditangani oleh aplikasi.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="bx bx-user me-2"></i>Pengguna Terakhir</h5></div>
            <div class="card-body">
                <h6 class="fw-bold mb-1">{{ $error->actor_name ?: 'Tidak diketahui' }}</h6>
                <p class="text-muted mb-3">{{ $error->actor_identifier ?: 'Tanpa identitas tambahan' }}</p>
                <span class="badge bg-label-primary">{{ ucfirst($error->actor_guard ?: 'guest') }}</span>
                @if(data_get($error->context, 'impersonator_admin_id'))
                    <div class="alert alert-warning mt-3 mb-0 py-2">
                        Diakses melalui impersonasi Admin ID {{ data_get($error->context, 'impersonator_admin_id') }}.
                    </div>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="bx bx-book-open me-2"></i>Konteks Akademik</h5></div>
            <div class="card-body">
                @if($academic)
                    <dl class="mb-0">
                        <dt>Mata Kuliah</dt><dd>{{ data_get($academic, 'mata_kuliah', '-') }} ({{ data_get($academic, 'kode_mata_kuliah', '-') }})</dd>
                        <dt>Program Studi</dt><dd>{{ data_get($academic, 'program_studi', '-') }}</dd>
                        <dt>Kelas</dt><dd>{{ data_get($academic, 'kelas', '-') }}</dd>
                        <dt>Semester</dt><dd>{{ data_get($academic, 'semester', '-') }}</dd>
                        <dt>Jenis / Jadwal</dt><dd>{{ ucfirst(data_get($academic, 'jenis_jadwal', '-')) }} / #{{ data_get($academic, 'jadwal_id', '-') }}</dd>
                    </dl>
                @else
                    <p class="text-muted mb-0">Request ini tidak berkaitan langsung dengan jadwal mata kuliah.</p>
                @endif
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom"><h5 class="mb-0"><i class="bx bx-info-circle me-2"></i>Ringkasan Request</h5></div>
            <div class="card-body">
                @forelse(data_get($error->context, 'input_summary', []) as $key => $value)
                    <div class="mb-2"><small class="text-muted d-block">{{ $key }}</small><span>{{ $value }}</span></div>
                @empty
                    <span class="text-muted">Tidak ada input aman yang perlu ditampilkan.</span>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mt-4">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1"><i class="bx bx-history me-2"></i>Riwayat Kejadian Terbaru</h5>
            <small class="text-muted">Maksimal 50 kejadian terakhir untuk error sejenis.</small>
        </div>
        <span class="badge bg-label-secondary">{{ $error->events->count() }} tersimpan</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>Waktu</th><th>Pengguna</th><th>Mata Kuliah / Fitur</th><th>Input Ringkas</th></tr>
            </thead>
            <tbody>
                @forelse($error->events as $event)
                    @php
                        $eventAcademic = data_get($event->context, 'akademik', []);
                    @endphp
                    <tr>
                        <td class="text-nowrap">{{ $event->occurred_at?->format('d M Y H:i:s') }}</td>
                        <td>
                            <div class="fw-semibold">{{ $event->actor_name ?: 'Tidak diketahui' }}</div>
                            <small class="text-muted">{{ ucfirst($event->actor_guard ?: 'guest') }}
                                @if($event->actor_identifier)&bull; {{ $event->actor_identifier }}@endif
                            </small>
                        </td>
                        <td>
                            @if($eventAcademic)
                                <div class="fw-semibold">{{ data_get($eventAcademic, 'mata_kuliah', '-') }}</div>
                                <small class="text-muted">
                                    {{ data_get($eventAcademic, 'kelas', '-') }} &bull;
                                    {{ data_get($eventAcademic, 'program_studi', '-') }}
                                </small>
                            @else
                                <span class="text-muted">Tidak terkait jadwal akademik</span>
                            @endif
                        </td>
                        <td>
                            @forelse(data_get($event->context, 'input_summary', []) as $key => $value)
                                <div><small class="text-muted">{{ $key }}:</small> {{ $value }}</div>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">Riwayat kejadian belum tersedia.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
