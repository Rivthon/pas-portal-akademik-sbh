@extends('layouts.mahasiswa')
@section('title', 'Riwayat KHS')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#566a7f 0%,#8592a3 100%)">
        <div class="d-flex align-items-center gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25" style="width:54px;height:54px">
                <i class="bx bx-archive fs-2"></i>
            </span>
            <div>
                <h4 class="text-white mb-1">Riwayat KHS</h4>
                <p class="text-white-50 mb-0">Arsip Kartu Hasil Studi dari tahun akademik sebelumnya.</p>
            </div>
        </div>
    </div>
</div>

@if($tahunAjaranOptions->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-archive-out text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3">Belum Ada Riwayat KHS</h5>
            <p class="text-muted mb-0">KHS tahun akademik sebelumnya belum tersedia atau belum diterbitkan.</p>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mahasiswa.khs.riwayat') }}" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="ta_id" class="form-label fw-semibold"><i class="bx bx-history text-primary me-1"></i>Tahun Akademik Sebelumnya</label>
                    <select name="ta_id" id="ta_id" class="form-select" onchange="this.form.submit()">
                        @foreach($tahunAjaranOptions as $tahunAjaran)
                            <option value="{{ $tahunAjaran->ta_id }}" @selected((int) $selectedTaId === (int) $tahunAjaran->ta_id)>
                                {{ $tahunAjaran->nama }}{{ $tahunAjaran->semester ? ' - '.ucfirst($tahunAjaran->semester) : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button class="btn btn-outline-primary"><i class="bx bx-search-alt me-1"></i>Tampilkan Riwayat</button>
                </div>
            </form>
            <div class="alert alert-info py-2 mt-3 mb-0">
                <i class="bx bx-info-circle me-1"></i>Riwayat KHS tidak mengikuti status aktivasi KHS semester aktif.
            </div>
        </div>
    </div>

    @if($edomLocked ?? false)
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-label-warning mb-3" style="width:72px;height:72px">
                    <i class="bx bx-lock-alt text-warning" style="font-size:2.5rem"></i>
                </span>
                <h5>Riwayat KHS Belum Dapat Dibuka</h5>
                <p class="text-muted mb-3">{{ $edomMessage }}</p>
                <a href="{{ route('mahasiswa.edom.index', ['ta_id' => $selectedTaId]) }}" class="btn btn-primary">
                    <i class="bx bx-edit-alt me-1"></i>Buka EDOM
                </a>
            </div>
        </div>
    @else
        @include('mahasiswa.khs._hasil', [
            'judulHasil' => 'Arsip Kartu Hasil Studi',
            'tampilkanRiwayatEdom' => false,
        ])
    @endif
@endif
@endsection
