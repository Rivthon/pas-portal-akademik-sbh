@extends('layouts.master')

@section('title', 'Laporan Error Production')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1">Laporan Error Production</h4>
        <p class="text-muted mb-0">Khusus Super Admin. Password, token, secret, dan API key disamarkan otomatis.</p>
    </div>
    <a href="{{ route('admin.home') }}" class="btn btn-label-secondary">
        <i class="bx bx-arrow-back me-1"></i>Kembali ke Dashboard
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-7">
                <label class="form-label">Cari error atau pengguna</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Pesan error, nama, NIM/NIDN, atau nama route">
            </div>
            <div class="col-md-3">
                <label class="form-label">Jenis pengguna</label>
                <select name="guard" class="form-select">
                    <option value="">Semua pengguna</option>
                    <option value="web" @selected(request('guard') === 'web')>Admin</option>
                    <option value="dosen" @selected(request('guard') === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected(request('guard') === 'mahasiswa')>Mahasiswa</option>
                </select>
            </div>
            <div class="col-md-2 d-grid">
                <button class="btn btn-primary"><i class="bx bx-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Terakhir</th>
                    <th>Pengguna</th>
                    <th>Fitur / Konteks</th>
                    <th>Error</th>
                    <th class="text-center">Jumlah</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($errors as $error)
                    @php
                        $academic = data_get($error->context, 'akademik', []);
                    @endphp
                    <tr>
                        <td class="text-nowrap">
                            <div class="fw-semibold">{{ $error->last_seen_at?->format('d M Y') }}</div>
                            <small class="text-muted">{{ $error->last_seen_at?->format('H:i:s') }}</small>
                        </td>
                        <td>
                            <div class="fw-semibold">{{ $error->actor_name ?: 'Tidak diketahui' }}</div>
                            <small class="text-muted">{{ ucfirst($error->actor_guard ?: 'guest') }}
                                @if($error->actor_identifier)&bull; {{ $error->actor_identifier }}@endif
                            </small>
                        </td>
                        <td style="min-width:220px;">
                            @if($academic)
                                <div class="fw-semibold">{{ data_get($academic, 'mata_kuliah', '-') }}</div>
                                <small class="text-muted">
                                    {{ data_get($academic, 'kelas', '-') }} &bull; {{ data_get($academic, 'program_studi', '-') }}
                                </small>
                            @else
                                <div class="fw-semibold">{{ $error->route_name ?: '-' }}</div>
                                <small class="text-muted">{{ $error->method }} {{ $error->path }}</small>
                            @endif
                        </td>
                        <td style="min-width:300px; max-width:480px;">
                            <span class="badge bg-label-danger mb-1">HTTP {{ $error->http_status }}</span>
                            <div class="text-truncate" title="{{ $error->message }}">{{ $error->message }}</div>
                        </td>
                        <td class="text-center"><span class="badge bg-label-warning">{{ number_format($error->occurrences) }}x</span></td>
                        <td class="text-end">
                            <a href="{{ route('admin.system.errors.show', $error) }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-show me-1"></i>Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada error yang sesuai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($errors->hasPages())
        <div class="card-footer bg-white">{{ $errors->links() }}</div>
    @endif
</div>
@endsection
