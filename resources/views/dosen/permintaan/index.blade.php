@extends('layouts.dosen')
@section('title', 'Helpdesk')

@section('content')
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div>
                        <span class="badge bg-label-primary mb-2">Layanan ICT</span>
                        <h4 class="mb-2">Helpdesk Dosen</h4>
                        <p class="text-muted mb-0">Laporkan kendala akses, bug, atau kebutuhan fitur Portal Akademik.</p>
                    </div>
                    <a href="{{ route('dosen.permintaan.create') }}" class="btn btn-primary text-nowrap">
                        <i class="bx bx-plus me-1"></i>Buat Permintaan
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card bg-label-info border-0 h-100">
            <div class="card-body p-4 d-flex align-items-center">
                <div class="avatar avatar-lg me-3">
                    <span class="avatar-initial rounded bg-info"><i class="bx bx-user bx-md"></i></span>
                </div>
                <div>
                    <small class="text-muted">Pemohon</small>
                    <h6 class="mb-1">{{ $dosen->nama }}</h6>
                    <span class="text-muted small">NIDN: {{ $dosen->nidn ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between">
        <h5 class="mb-0"><i class="bx bx-history me-2 text-primary"></i>Riwayat Permintaan</h5>
        <span class="badge bg-label-secondary">{{ $permintaan->total() }} tiket</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Permintaan</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th>Tanggapan Admin</th>
                    <th>Tanggal</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($permintaan as $item)
                    @php
                        $statusBadge = [
                            'menunggu' => 'warning', 'disetujui' => 'success', 'ditolak' => 'danger',
                            'revisi' => 'info', 'selesai' => 'primary',
                        ][$item->status] ?? 'secondary';
                        $prioritasBadge = [
                            'rendah' => 'secondary', 'sedang' => 'info', 'tinggi' => 'warning', 'urgen' => 'danger',
                        ][$item->prioritas] ?? 'secondary';
                    @endphp
                    <tr>
                        <td>{{ $permintaan->firstItem() + $loop->index }}</td>
                        <td>
                            <div class="fw-semibold">{{ $item->judul }}</div>
                            <small class="text-muted">{{ ucfirst($item->jenis_permintaan) }}</small>
                        </td>
                        <td><span class="badge bg-label-{{ $prioritasBadge }}">{{ ucfirst($item->prioritas) }}</span></td>
                        <td><span class="badge bg-label-{{ $statusBadge }}">{{ ucfirst($item->status) }}</span></td>
                        <td style="min-width: 180px">{{ $item->komentar_admin ?: '-' }}</td>
                        <td class="text-nowrap">{{ $item->created_at->format('d M Y H:i') }}</td>
                        <td class="text-end">
                            @if (in_array($item->status, ['menunggu', 'revisi'], true))
                                <form action="{{ route('dosen.permintaan.destroy', $item->id) }}" method="POST"
                                    onsubmit="return confirm('Hapus permintaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" type="submit"><i class="bx bx-trash"></i></button>
                                </form>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="bx bx-message-square-detail bx-lg text-muted mb-2"></i>
                            <p class="text-muted mb-0">Belum ada permintaan Helpdesk.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($permintaan->hasPages())
        <div class="card-footer">{{ $permintaan->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
