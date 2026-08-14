@extends('layouts.mahasiswa')
@section('title', 'Pedoman Akademik')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#4338ca,#6366f1)">
        <div class="d-flex justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">DOKUMEN AKADEMIK</span>
                <h3 class="text-white fw-bold mb-1">Pedoman Akademik</h3>
                <p class="text-white-50 mb-0">Baca dan unduh pedoman resmi pelaksanaan kegiatan akademik.</p>
            </div>
            <i class="bx bxs-book-open d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

@forelse($pedoman as $item)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-1">{{ $item->judul }}</h5>
                <small class="text-muted"><i class="bx bx-calendar me-1"></i>Tahun berlaku: {{ $item->tahun_berlaku ?: '-' }}</small>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('mahasiswa.pedoman-akademik.preview', $item) }}" target="_blank" class="btn btn-label-primary">
                    <i class="bx bx-show me-1"></i>Lihat PDF
                </a>
                <a href="{{ route('mahasiswa.pedoman-akademik.download', $item) }}" class="btn btn-primary">
                    <i class="bx bx-download me-1"></i>Download
                </a>
            </div>
        </div>
        <div class="card-body p-0 d-none d-md-block">
            <iframe src="{{ route('mahasiswa.pedoman-akademik.preview', $item) }}#toolbar=1"
                title="{{ $item->judul }}" style="width:100%;height:72vh;border:0"></iframe>
        </div>
    </div>
@empty
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-file-blank text-muted mb-2" style="font-size:4rem"></i>
            <h5>Pedoman Akademik belum tersedia</h5>
            <p class="text-muted mb-0">BAAK belum memublikasikan dokumen Pedoman Akademik.</p>
        </div>
    </div>
@endforelse
@endsection
