@extends('layouts.dosen')
@section('title', 'Berkas Program Studi')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#174ea6,#5677d8)">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">DOKUMEN PROGRAM STUDI</span>
                <h3 class="text-white fw-bold mb-1">Berkas Program Studi</h3>
                <p class="text-white-50 mb-0">Unduh dokumen untuk dosen. Sekprodi dapat mengunggah dan mengelola berkas.</p>
            </div>
            <i class="bx bxs-folder-open d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

@if (session('success'))
<div class="alert alert-success alert-dismissible fade show">
    <i class="bx bx-check-circle me-1"></i>{{ session('success') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif
@if ($errors->any())
<div class="alert alert-danger">
    <strong>Berkas belum dapat disimpan.</strong>
    <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

@if ($managedPrograms->isNotEmpty())
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold mb-1"><i class="bx bx-cloud-upload me-1 text-primary"></i>Unggah Berkas</h5>
            <small class="text-muted">Panel ini hanya tersedia untuk dosen yang ditetapkan sebagai Sekprodi.</small>
        </div>
        <button class="btn btn-label-primary" type="button" data-bs-toggle="collapse" data-bs-target="#formBerkasProdi">Buka / Tutup Form</button>
    </div>
    <div id="formBerkasProdi" class="collapse {{ $errors->any() ? 'show' : '' }}">
        <div class="card-body">
            <form action="{{ route('dosen.berkas-program-studi.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Program Studi</label>
                        <select name="jurusan_id" class="form-select" required>
                            @foreach ($managedPrograms as $program)
                            <option value="{{ $program->jurusan_id }}" @selected((string) old('jurusan_id') === (string) $program->jurusan_id)>{{ $program->jenjang }} {{ $program->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Ditujukan Untuk</label>
                        <select name="target" class="form-select" required>
                            <option value="mahasiswa" @selected(old('target') === 'mahasiswa')>Mahasiswa</option>
                            <option value="dosen" @selected(old('target') === 'dosen')>Dosen</option>
                            <option value="semua" @selected(old('target', 'semua') === 'semua')>Mahasiswa & Dosen</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Judul Berkas</label>
                        <input name="judul" class="form-control" maxlength="255" value="{{ old('judul') }}" placeholder="Contoh: Formulir Pendaftaran Sidang" required>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">File</label>
                        <input type="file" name="berkas" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" required>
                        <small class="text-muted">PDF/Office/gambar, maksimal 20 MB.</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Keterangan <span class="text-muted">(opsional)</span></label>
                        <textarea name="deskripsi" rows="3" class="form-control" maxlength="2000" placeholder="Jelaskan kegunaan atau cara pengisian berkas.">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="col-12 text-end">
                        <button class="btn btn-primary"><i class="bx bx-upload me-1"></i>Unggah & Publikasikan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="row g-3">
@forelse ($berkas as $item)
    @php($canManage = $managedPrograms->contains(fn ($program) => (string) $program->jurusan_id === (string) $item->jurusan_id))
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between gap-2 mb-3">
                    <div class="rounded p-2 bg-label-primary"><i class="bx bxs-file-blank fs-3"></i></div>
                    <div class="text-end">
                        <span class="badge bg-label-{{ $item->target === 'mahasiswa' ? 'success' : ($item->target === 'dosen' ? 'info' : 'primary') }}">{{ $item->target === 'semua' ? 'Mahasiswa & Dosen' : ucfirst($item->target) }}</span>
                        @if (! $item->aktif)<span class="badge bg-label-secondary">Nonaktif</span>@endif
                    </div>
                </div>
                <h5 class="fw-bold mb-1">{{ $item->judul }}</h5>
                <small class="text-muted mb-2">{{ $item->programStudi?->nama }} · {{ number_format($item->ukuran / 1024, 0, ',', '.') }} KB</small>
                @if ($item->deskripsi)<p class="text-muted small flex-grow-1">{{ $item->deskripsi }}</p>@else<div class="flex-grow-1"></div>@endif
                <small class="text-muted mb-3">Diunggah {{ $item->created_at?->translatedFormat('d M Y H:i') }} oleh {{ $item->pengunggah?->nama }}</small>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('dosen.berkas-program-studi.download', $item) }}" class="btn btn-primary btn-sm flex-grow-1"><i class="bx bx-download me-1"></i>Download</a>
                    @if ($canManage)
                    <form action="{{ route('dosen.berkas-program-studi.toggle', $item) }}" method="POST">@csrf @method('PATCH')
                        <button class="btn btn-label-{{ $item->aktif ? 'warning' : 'success' }} btn-sm" title="Ubah status"><i class="bx {{ $item->aktif ? 'bx-hide' : 'bx-show' }}"></i></button>
                    </form>
                    <form action="{{ route('dosen.berkas-program-studi.destroy', $item) }}" method="POST" onsubmit="return confirm('Hapus berkas ini secara permanen?')">@csrf @method('DELETE')
                        <button class="btn btn-label-danger btn-sm" title="Hapus"><i class="bx bx-trash"></i></button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
        <i class="bx bx-folder-open text-muted" style="font-size:4rem"></i><h5 class="mt-2">Belum ada berkas Program Studi</h5><p class="text-muted mb-0">Berkas yang dibagikan Sekprodi akan muncul di halaman ini.</p>
    </div></div></div>
@endforelse
</div>
<div class="mt-4">{{ $berkas->links('pagination::bootstrap-5') }}</div>
@endsection
