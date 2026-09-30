@extends('layouts.mahasiswa')
@section('title', 'EDOM Belum Dibuka')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm overflow-hidden">
        <div class="card-body text-center py-5 px-4">
            <div class="avatar avatar-xl bg-label-warning rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center">
                <i class="bx bx-lock-alt text-warning" style="font-size:2.75rem"></i>
            </div>
            <h3 class="mb-2">EDOM Belum Dibuka Admin</h3>
            <p class="text-muted mx-auto mb-2" style="max-width:620px">
                Pengisian EDOM untuk tahun akademik aktif belum dapat dilakukan. Silakan menunggu jadwal pembukaan EDOM dari admin atau BAAK.
            </p>
            @if($tahunAkademik)
                <span class="badge bg-label-primary mb-4">
                    {{ $tahunAkademik->nama }} · {{ ucfirst($tahunAkademik->semester) }}
                </span>
            @endif

            <div class="d-flex flex-wrap justify-content-center gap-2">
                @if((int) auth('mahasiswa')->user()?->status_akhir === 1)
                    <a href="{{ route('mahasiswa.kartu-hasil.index') }}" class="btn btn-outline-primary">
                        <i class="bx bx-arrow-back me-1"></i>Kembali ke Status KHS
                    </a>
                @endif
                <a href="{{ route('mahasiswa.dashboard') }}" class="btn btn-primary">
                    <i class="bx bx-home me-1"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
