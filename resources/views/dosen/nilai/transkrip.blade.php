@extends('layouts.dosen')

@section('title', 'Transkrip Nilai Mahasiswa')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        
        {{-- Back Button --}}
        <div class="mb-4">
            <a href="{{ route('dosen.nilai-dosen.lihat') }}" class="btn btn-outline-secondary d-inline-flex align-items-center shadow-sm rounded-pill px-3">
                <i class="bx bx-left-arrow-alt me-2"></i> Kembali ke Daftar Bimbingan
            </a>
        </div>

        {{-- Hero Profile Card --}}
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #696cff 0%, #5f61f4 50%, #8b8eff 100%);">
            <div class="card-body position-relative overflow-hidden">
                {{-- Background Decoration --}}
                <div class="position-absolute" style="right: -5%; top: -40%; width: 250px; height: 250px; background: rgba(255,255,255,0.07); border-radius: 50%; filter: blur(40px);"></div>
                
                <div class="d-flex flex-column flex-sm-row align-items-center gap-4 position-relative" style="z-index: 1;">
                    <div class="avatar avatar-xl" style="width: 100px; height: 100px;">
                        @if(isset($mahasiswa->avatar) && $mahasiswa->avatar)
                        <img src="{{ asset('storage/' . $mahasiswa->avatar) }}" alt="{{ $mahasiswa->nama }}"
                            class="rounded-circle object-fit-cover shadow-sm border border-3 border-white" style="width: 100%; height: 100%;">
                        @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($mahasiswa->nama) }}&background=ffffff&color=696cff&size=128"
                            alt="{{ $mahasiswa->nama }}" class="rounded-circle shadow-sm border border-3 border-white" style="width: 100%; height: 100%;">
                        @endif
                    </div>
                    <div class="d-flex flex-column text-center text-sm-start text-white">
                        <h3 class="mb-1 fw-bold text-white">{{ $mahasiswa->nama }}</h3>
                        <p class="mb-2 opacity-75" style="font-size: 1.05rem;"><i class="bx bx-id-card me-1"></i>{{ $mahasiswa->nim }}</p>
                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-start">
                            <span class="badge bg-white text-primary px-3 py-2 shadow-sm rounded-pill" style="font-size: 0.85rem;">
                                <i class="bx bx-bar-chart-alt-2 me-1"></i> IPK: <strong>{{ $ipk }}</strong>
                            </span>
                            <span class="badge bg-white text-dark px-3 py-2 shadow-sm rounded-pill" style="font-size: 0.85rem;">
                                <i class="bx bx-book-content me-1"></i> Total SKS: <strong>{{ $totalSks }}</strong>
                            </span>
                             <span class="badge bg-white text-info px-3 py-2 shadow-sm rounded-pill" style="font-size: 0.85rem;">
                                <i class="bx bx-buildings me-1"></i> {{ $mahasiswa->programStudi->nama_program_studi ?? 'Program Studi' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Transcript Table Card --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-dark"><i class="bx bx-list-check text-primary me-2"></i>Rincian Nilai Akademik</h5>
            </div>

            @if(!$transkrip->isEmpty())
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">NO</th>
                            <th style="width: 120px;">KODE MK</th>
                            <th>NAMA MATA KULIAH</th>
                            <th class="text-center" style="width: 80px;">SMT</th>
                            <th class="text-center" style="width: 80px;">SKS</th>
                            <th class="text-center" style="width: 100px;">NILAI / HURUF</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @foreach ($transkrip as $nilai)
                        <tr>
                            <td class="text-center text-muted">{{ $loop->iteration }}</td>
                            <td><span class="fw-semibold text-primary">{{ $nilai->kurikulum->mataKuliah->matakuliah_id ?? '-' }}</span></td>
                            <td><span class="fw-semibold text-dark">{{ $nilai->kurikulum->mataKuliah->nama ?? '-' }}</span></td>
                            <td class="text-center">
                                <span class="badge bg-label-secondary">Semester {{ $nilai->kurikulum->mataKuliah->smt ?? '-' }}</span>
                            </td>
                            <td class="text-center fw-bold text-muted">{{ $nilai->kurikulum->mataKuliah->sks ?? '0' }}</td>
                            <td class="text-center">
                                @php
                                $badgeColor = 'secondary';
                                if (in_array($nilai->khs, ['A', 'AB', 'BA'])) {
                                    $badgeColor = 'success';
                                } elseif (in_array($nilai->khs, ['B', 'BC'])) {
                                    $badgeColor = 'primary';
                                } elseif ($nilai->khs == 'C') {
                                    $badgeColor = 'warning';
                                } elseif ($nilai->khs == 'D') {
                                    $badgeColor = 'danger';
                                } elseif ($nilai->khs == 'E') {
                                    $badgeColor = 'dark';
                                }
                                @endphp
                                <span class="badge bg-{{ $badgeColor }} shadow-sm px-3 rounded-pill" style="font-size: 0.85rem;">{{ $nilai->khs }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            {{-- Tampilan jika transkrip kosong --}}
             <div class="card-body text-center py-5">
                <div class="mb-3">
                    <i class="bx mb-2 bx-folder-open text-muted" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
                <h5 class="fw-semibold text-dark">Data Transkrip Belum Tersedia</h5>
                <p class="text-muted mb-0">Belum ada rekam jejak penilaian akademik yang terdata untuk mahasiswa ini.</p>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection