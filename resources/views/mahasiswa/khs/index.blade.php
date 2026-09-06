@extends('layouts.mahasiswa')
@section('title', 'Kartu Hasil Studi')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3 0%,#5a72d8 100%)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">SEMESTER AKTIF</span>
                <h4 class="text-white mb-1">Kartu Hasil Studi</h4>
                <p class="text-white-50 mb-0">KHS tahun akademik yang sedang berjalan.</p>
            </div>
            <div class="text-md-end">
                <strong>{{ $ta?->nama ?? '-' }}</strong>
                <small class="d-block text-white-50">{{ $ta?->semester ? ucfirst($ta->semester) : '' }}</small>
            </div>
        </div>
    </div>
</div>

@if((int) auth('mahasiswa')->user()->status_edom !== 1)
    <div class="card shadow-sm mb-4 border-top border-5 border-warning">
        <div class="card-body text-center py-5">
            <i class="bx bx-message-square-edit text-warning" style="font-size:4rem"></i>
            <h4 class="mt-3">Selesaikan EDOM Terlebih Dahulu</h4>
            <p class="text-muted mx-auto" style="max-width:620px">
                KHS semester aktif sudah tersedia, tetapi baru dapat dilihat setelah seluruh EDOM selesai dan dikonfirmasi.
            </p>
            <a href="{{ route('mahasiswa.edom.index') }}" class="btn btn-warning">
                <i class="bx bx-edit me-1"></i>Buka EDOM
            </a>
        </div>
    </div>
@else
    @include('mahasiswa.khs._hasil', [
        'judulHasil' => 'Kartu Hasil Studi Semester Aktif',
        'tampilkanRiwayatEdom' => true,
    ])
@endif
@endsection
