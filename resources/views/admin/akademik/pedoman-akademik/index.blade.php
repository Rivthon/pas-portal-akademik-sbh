@extends('layouts.master')
@section('title', 'Pedoman Akademik')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#4338ca,#6366f1)">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">BAAK / ADMIN</span>
                <h3 class="text-white fw-bold mb-1">Pedoman Akademik</h3>
                <p class="text-white-50 mb-0">Kelola dokumen PDF Pedoman Akademik yang dapat dibaca mahasiswa.</p>
            </div>
            <i class="bx bxs-book-open d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success"><i class="bx bx-check-circle me-2"></i>{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger">
        <strong>Pedoman gagal diunggah.</strong>
        <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

@can('pedoman-akademik-create')
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bx bx-upload text-primary me-2"></i>Upload Pedoman Akademik</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.pedoman-akademik.store') }}" method="POST" enctype="multipart/form-data" class="row g-3">
            @csrf
            <div class="col-md-5">
                <label class="form-label">Judul Pedoman <span class="text-danger">*</span></label>
                <input type="text" name="judul" value="{{ old('judul') }}" class="form-control" required
                    placeholder="Contoh: Pedoman Akademik Tahun 2026">
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Berlaku</label>
                <input type="text" name="tahun_berlaku" value="{{ old('tahun_berlaku') }}" class="form-control"
                    placeholder="Contoh: 2026/2027">
            </div>
            <div class="col-md-4">
                <label class="form-label">File PDF <span class="text-danger">*</span></label>
                <input type="file" name="file" class="form-control" accept="application/pdf,.pdf" required>
                <small class="text-muted">Hanya PDF, maksimal 20 MB.</small>
            </div>
            <div class="col-12 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="form-check form-switch">
                    <input type="hidden" name="status" value="0">
                    <input class="form-check-input" type="checkbox" name="status" value="1" id="statusPedoman"
                        @checked(old('status', true))>
                    <label class="form-check-label" for="statusPedoman">Langsung aktifkan untuk mahasiswa</label>
                    <small class="d-block text-muted">Pedoman aktif sebelumnya otomatis menjadi arsip.</small>
                </div>
                <button class="btn btn-primary"><i class="bx bx-cloud-upload me-1"></i>Upload PDF</button>
            </div>
        </form>
    </div>
</div>
@endcan

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bx bx-library text-primary me-2"></i>Daftar Pedoman</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Pedoman</th>
                    <th>File</th>
                    <th>Pengunggah</th>
                    <th class="text-center">Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pedoman as $item)
                    <tr>
                        <td>{{ $pedoman->firstItem() + $loop->index }}</td>
                        <td>
                            <strong class="d-block">{{ $item->judul }}</strong>
                            <small class="text-muted">Berlaku: {{ $item->tahun_berlaku ?: '-' }} &bull; {{ $item->created_at?->format('d/m/Y H:i') }}</small>
                        </td>
                        <td><i class="bx bxs-file-pdf text-danger me-1"></i>{{ $item->nama_file }}</td>
                        <td>{{ $item->pengunggah?->name ?? $item->pengunggah?->nama ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-label-{{ $item->status ? 'success' : 'secondary' }}">
                                {{ $item->status ? 'Aktif' : 'Arsip' }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button class="btn btn-sm btn-label-primary dropdown-toggle" data-bs-toggle="dropdown">Aksi</button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="{{ route('admin.pedoman-akademik.preview', $item) }}" target="_blank"><i class="bx bx-show me-2"></i>Lihat PDF</a></li>
                                    <li><a class="dropdown-item" href="{{ route('admin.pedoman-akademik.download', $item) }}"><i class="bx bx-download me-2"></i>Download</a></li>
                                    @can('pedoman-akademik-edit')
                                        <li><a class="dropdown-item" href="{{ route('admin.pedoman-akademik.edit', $item) }}"><i class="bx bx-edit me-2"></i>Edit</a></li>
                                    @endcan
                                    @can('pedoman-akademik-delete')
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form action="{{ route('admin.pedoman-akademik.destroy', $item) }}" method="POST"
                                                onsubmit="return confirm('Hapus Pedoman Akademik ini?')">
                                                @csrf @method('DELETE')
                                                <button class="dropdown-item text-danger"><i class="bx bx-trash me-2"></i>Hapus</button>
                                            </form>
                                        </li>
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5"><i class="bx bx-file-blank fs-1 d-block mb-2"></i>Belum ada Pedoman Akademik.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pedoman->hasPages())
        <div class="card-footer bg-white">{{ $pedoman->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
