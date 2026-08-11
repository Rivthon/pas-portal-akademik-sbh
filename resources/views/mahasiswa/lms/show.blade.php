@extends('layouts.mahasiswa')
@section('title', 'LMS - ' . ($jadwal->kurikulum?->mataKuliah?->nama ?? 'Mata Kuliah'))

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
    $programStudi = $jadwal->kurikulum?->programStudi;
    $totalMateri = $materiList->count();
    $totalTugas = $tugasList->count();
@endphp

<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        {{-- Navigasi Atas --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <a href="{{ route('mahasiswa.lms.index') }}" class="btn btn-outline-primary rounded-pill shadow-sm btn-sm px-3">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke LMS
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('mahasiswa.lms.quiz.index', $jadwal) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bx bx-question-mark me-1"></i> Quiz
                </a>
                <a href="{{ route('mahasiswa.lms.gradebook.show', $jadwal) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="bx bx-bar-chart-square me-1"></i> Nilai
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Hero Banner Detail Kelas --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden lms-hero" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="bx bx-code-alt me-1"></i>{{ $mataKuliah?->matakuliah_id ?? $mataKuliah?->kode_mk ?? $mataKuliah?->kode ?? 'Mata Kuliah' }}
                        </span>
                        <h3 class="text-white fw-bold mt-1 mb-2">{{ $mataKuliah?->nama ?? '-' }}</h3>
                        <p class="text-white-50 mb-3">
                            <i class="bx bx-buildings me-1"></i>{{ $programStudi?->nama ?? '-' }}
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-layer me-1"></i>{{ $pertemuan->count() }} Pertemuan</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-file me-1"></i>{{ $totalMateri }} Materi</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-task me-1"></i>{{ $totalTugas }} Tugas</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-calendar me-1"></i>{{ $jadwal->hari ?? '-' }}, {{ $jadwal->jam_mulai ?? '-' }} - {{ $jadwal->jam_selesai ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center d-none d-md-block">
                        <i class="bx bxs-graduation text-white" style="font-size: 7rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ringkasan Materi & Tugas --}}
        <div class="row g-4 mb-4">
            <!-- Box Semua Materi -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-file text-success me-2"></i>Semua Materi</h5>
                        <span class="badge bg-label-success rounded-pill px-3">{{ $materiList->count() }} Materi</span>
                    </div>
                    <div class="card-body pt-3">
                        @forelse($materiList as $materi)
                            <a href="{{ route('mahasiswa.lms.materi.show', $materi) }}" target="_blank"
                               class="d-flex align-items-center justify-content-between border rounded-3 p-3 mb-2 text-body bg-light text-decoration-none item-hover-card">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <span class="avatar-initial rounded-circle bg-label-{{ $materi->tipe === 'youtube' ? 'danger' : 'primary' }} me-3 p-2">
                                        <i class="bx {{ $materi->tipe === 'youtube' ? 'bxl-youtube' : ($materi->tipe === 'gambar' ? 'bx-image' : 'bx-file') }} fs-4"></i>
                                    </span>
                                    <div class="text-truncate">
                                        <div class="fw-semibold text-dark text-truncate">{{ $materi->judul }}</div>
                                        <small class="text-muted">
                                            Pertemuan {{ $nomorPertemuan->get($materi->pertemuan_id, '-') }}
                                            &bull; {{ strtoupper($materi->tipe ?: 'FILE') }}
                                        </small>
                                    </div>
                                </div>
                                <i class="bx bx-link-external text-primary ms-2 fs-5"></i>
                            </a>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bx bx-file-blank fs-1 d-block mb-2"></i>
                                Belum ada materi untuk kelas ini.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Box Semua Tugas -->
            <div class="col-xl-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center py-3">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bx bx-task text-warning me-2"></i>Semua Tugas</h5>
                        <span class="badge bg-label-warning rounded-pill px-3">{{ $tugasList->count() }} Tugas</span>
                    </div>
                    <div class="card-body pt-3">
                        @forelse($tugasList as $tugas)
                            @php
                                $pengumpulanTugas = $tugas->pengumpulan->first();
                                $deadlineLewat = $tugas->deadline && now()->greaterThan($tugas->deadline);
                            @endphp
                            <div class="border rounded-3 p-3 mb-2 bg-light">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $tugas->judul }}</div>
                                        <small class="text-muted">Pertemuan {{ $nomorPertemuan->get($tugas->pertemuan_id, '-') }}</small>
                                    </div>
                                    @if($pengumpulanTugas)
                                        <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-check me-1"></i>Dikumpulkan</span>
                                    @elseif($deadlineLewat)
                                        <span class="badge bg-label-danger rounded-pill px-3"><i class="bx bx-x me-1"></i>Terlambat</span>
                                    @else
                                        <span class="badge bg-label-warning rounded-pill px-3"><i class="bx bx-time me-1"></i>Belum</span>
                                    @endif
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3 pt-2 border-top">
                                    <small class="text-muted"><i class="bx bx-time-five me-1 text-danger"></i>{{ $tugas->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                                    <a href="{{ route('mahasiswa.lms.tugas.show', $tugas) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Buka Tugas
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted py-4">
                                <i class="bx bx-task-x fs-1 d-block mb-2"></i>
                                Belum ada tugas untuk kelas ini.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Title Riwayat --}}
        <div class="d-flex align-items-center mb-3">
            <i class="bx bx-history text-primary fs-3 me-2"></i>
            <div>
                <h5 class="fw-bold mb-0 text-dark">Riwayat Pembelajaran</h5>
                <small class="text-muted">Materi dan tugas disusun berdasarkan pertemuan.</small>
            </div>
        </div>

        {{-- Accordion List Pertemuan (Collapse Didefinisikan Di Sini) --}}
        @forelse($pertemuan as $item)
            <div class="card border-0 shadow-sm mb-3 meeting-card bg-white">

                <!-- TOMBOL COLLAPSE HEADER -->
                <button type="button"
                        class="card-header bg-white border-0 w-100 d-flex flex-wrap justify-content-between align-items-center gap-2 py-3 text-start meeting-toggle {{ $loop->first ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        data-bs-target="#meetingStudent{{ $item->pertemuan_id }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="meetingStudent{{ $item->pertemuan_id }}">
                    <div class="d-flex align-items-center">
                        <span class="avatar-initial rounded-circle bg-label-primary me-3 meeting-number fw-bold">
                            {{ $loop->iteration }}
                        </span>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">{{ $item->topik ?: 'Pertemuan '.$loop->iteration }}</h6>
                            <small class="text-muted">
                                <i class="bx bx-calendar me-1"></i>{{ $item->tanggal_pertemuan ? \Carbon\Carbon::parse($item->tanggal_pertemuan)->translatedFormat('d F Y') : 'Tanggal belum ditentukan' }}
                            </small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-file me-1"></i>{{ $item->materi->count() }} Materi</span>
                        <span class="badge bg-label-warning rounded-pill px-3"><i class="bx bx-task me-1"></i>{{ $item->tugas->count() }} Tugas</span>
                        <span class="meeting-chevron ms-2"><i class="bx bx-chevron-down fs-3"></i></span>
                    </div>
                </button>

                <!-- KONTEN COLLAPSE -->
                <div id="meetingStudent{{ $item->pertemuan_id }}" class="collapse {{ $loop->first ? 'show' : '' }}">
                    <div class="card-body pt-3 border-top bg-light">

                        @if($item->sub_topik)
                            <div class="alert alert-light border p-3 rounded-3 mb-3 text-dark">
                                <strong>Sub Topik:</strong> {{ $item->sub_topik }}
                            </div>
                        @endif

                        <!-- SECTION MATERI PERTEMUAN -->
                        <h6 class="fw-bold text-dark mb-2"><i class="bx bx-file text-success me-1"></i>Materi Perkuliahan</h6>
                        <div class="row g-2 mb-4">
                            @forelse($item->materi as $materi)
                                <div class="col-lg-6">
                                    <a href="{{ route('mahasiswa.lms.materi.show', $materi) }}" target="_blank"
                                       class="d-flex align-items-center justify-content-between border rounded-3 p-3 text-body bg-white text-decoration-none shadow-sm item-hover-card">
                                        <div class="d-flex align-items-center overflow-hidden">
                                            <i class="bx {{ $materi->tipe === 'youtube' ? 'bxl-youtube text-danger' : 'bx-file text-primary' }} fs-3 me-3"></i>
                                            <div class="text-truncate">
                                                <div class="fw-semibold text-dark text-truncate">{{ $materi->judul }}</div>
                                                <small class="text-muted text-uppercase">{{ $materi->tipe ?: 'file' }}</small>
                                            </div>
                                        </div>
                                        <i class="bx bx-link-external text-primary ms-2 fs-5"></i>
                                    </a>
                                </div>
                            @empty
                                <div class="col-12"><small class="text-muted d-block p-2"><i class="bx bx-info-circle me-1"></i>Belum ada materi pada pertemuan ini.</small></div>
                            @endforelse
                        </div>

                        <!-- SECTION TUGAS PERTEMUAN -->
                        <h6 class="fw-bold text-dark mb-2"><i class="bx bx-task text-warning me-1"></i>Tugas Perkuliahan</h6>
                        <div class="row g-2">
                            @forelse($item->tugas as $tugas)
                                @php
                                    $pengumpulan = $tugas->pengumpulan->first();
                                    $lewat = $tugas->deadline && now()->greaterThan($tugas->deadline);
                                @endphp
                                <div class="col-lg-6">
                                    <div class="border rounded-3 p-3 bg-white shadow-sm h-100">
                                        <div class="d-flex justify-content-between gap-2 mb-2">
                                            <strong class="text-dark">{{ $tugas->judul }}</strong>
                                            @if($pengumpulan)
                                                <span class="badge bg-label-success rounded-pill px-3">Dikumpulkan</span>
                                            @elseif($lewat)
                                                <span class="badge bg-label-danger rounded-pill px-3">Terlambat</span>
                                            @else
                                                <span class="badge bg-label-warning rounded-pill px-3">Belum</span>
                                            @endif
                                        </div>
                                        <small class="text-muted d-block mb-3"><i class="bx bx-time-five me-1 text-danger"></i>Deadline: {{ $tugas->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small>
                                        <a href="{{ route('mahasiswa.lms.tugas.show', $tugas) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Tugas</a>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12"><small class="text-muted d-block p-2"><i class="bx bx-info-circle me-1"></i>Belum ada tugas pada pertemuan ini.</small></div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-layer text-muted mb-2" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold">Belum Ada Riwayat Pembelajaran</h5>
                    <p class="text-muted mb-0">Materi dan tugas akan muncul setelah dosen membuat pertemuan.</p>
                </div>
            </div>
        @endforelse

    </div>
</div>

<style>
    .lms-hero {
        border-radius: .9rem;
    }
    .meeting-card {
        border-radius: .85rem;
        overflow: hidden;
        transition: transform .2s ease, box-shadow .2s ease;
    }
    .meeting-card:hover {
        box-shadow: 0 .5rem 1.35rem rgba(67, 89, 113, .13) !important;
    }
    .meeting-toggle {
        color: inherit;
        cursor: pointer;
        transition: background-color .2s ease;
    }
    .meeting-toggle:hover {
        background-color: rgba(30, 60, 114, .045) !important;
    }
    .meeting-toggle:focus {
        outline: 0;
        box-shadow: inset 0 0 0 2px rgba(30, 60, 114, .18);
    }
    .meeting-number {
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .meeting-chevron {
        color: #1e3c72;
        line-height: 1;
        transition: transform .25s ease;
    }
    .meeting-toggle:not(.collapsed) .meeting-chevron {
        transform: rotate(180deg);
    }
    .item-hover-card {
        transition: transform .2s ease, background-color .2s ease;
    }
    .item-hover-card:hover {
        transform: translateX(4px);
        background-color: #fff !important;
    }
    @media (max-width: 575.98px) {
        .meeting-toggle { align-items: flex-start !important; }
        .meeting-toggle > .d-flex:last-child { width: 100%; padding-left: 3.55rem; }
    }
</style>
@endsection