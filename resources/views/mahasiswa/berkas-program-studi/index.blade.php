@extends('layouts.mahasiswa')
@section('title', 'Berkas Program Studi')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#0f766e,#22a58f)">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-success mb-2">{{ auth('mahasiswa')->user()->programStudi?->nama }}</span>
                <h3 class="text-white fw-bold mb-1">Berkas Program Studi</h3>
                <p class="text-white-50 mb-0">Unduh formulir dan dokumen resmi yang dibagikan Sekprodi untuk program studi Anda.</p>
            </div>
            <i class="bx bxs-download d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

<div class="row g-3">
@forelse ($berkas as $item)
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="rounded p-2 bg-label-success"><i class="bx bxs-file-blank fs-3"></i></div>
                    <span class="badge bg-label-success">{{ $item->programStudi?->nama }}</span>
                </div>
                <h5 class="fw-bold mb-1">{{ $item->judul }}</h5>
                <small class="text-muted mb-3">{{ $item->nama_file }} · {{ number_format($item->ukuran / 1024, 0, ',', '.') }} KB</small>
                @if ($item->deskripsi)<p class="text-muted small flex-grow-1">{{ $item->deskripsi }}</p>@else<div class="flex-grow-1"></div>@endif
                <small class="text-muted mb-3">Diperbarui {{ $item->updated_at?->translatedFormat('d F Y, H:i') }}</small>
                <a href="{{ route('mahasiswa.berkas-program-studi.download', $item) }}" class="btn btn-success"><i class="bx bx-download me-1"></i>Download Berkas</a>
            </div>
        </div>
    </div>
@empty
    <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5">
        <i class="bx bx-folder-open text-muted" style="font-size:4rem"></i><h5 class="mt-2">Belum ada berkas untuk program studi Anda</h5><p class="text-muted mb-0">Silakan periksa kembali setelah Sekprodi mengunggah dokumen.</p>
    </div></div></div>
@endforelse
</div>
<div class="mt-4">{{ $berkas->links('pagination::bootstrap-5') }}</div>
@endsection
