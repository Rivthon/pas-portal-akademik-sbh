@extends('layouts.mahasiswa')
@section('title', 'Rekap Nilai LMS Mahasiswa')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        <!-- Banner Header (Tema Gradien) -->
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <!-- Content Section -->
                    <div class="col-md-8 text-white p-3">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <a href="{{ route('mahasiswa.lms.index') }}" class="btn btn-sm btn-white text-primary rounded-pill px-3 fw-semibold">
                                <i class="bx bx-arrow-back me-1"></i>Kembali ke LMS
                            </a>
                        </div>
                        <h4 class="card-title mb-2 fw-bold text-white"><i class="bx bx-bar-chart-square me-2"></i>Rekap Nilai LMS</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Pantau capaian nilai sementara dan progress penilaian tugas maupun quiz untuk setiap mata kuliah yang Anda ikuti.
                        </p>
                    </div>

                    <!-- Icon/Image Section -->
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bx-trophy text-white" style="font-size: 7rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-search"><i class="bx bx-search"></i></span>
                            <input type="text" id="filterGradebook" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah atau Program Studi..." aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light mt-0 pt-4">

                <div id="noDataResult" class="alert alert-warning text-center d-none shadow-sm border-0">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan mata kuliah yang sesuai.
                </div>

                <div class="row g-4" id="gradebookContainer">
                    @forelse($jadwalList as $item)
                        @php
                            $mataKuliah = $item->kurikulum?->mataKuliah;
                            $programStudi = $item->kurikulum?->programStudi;
                            $warna = $item->persentase_gradebook >= 75 ? 'success' : ($item->persentase_gradebook >= 60 ? 'warning' : 'primary');
                        @endphp
                        <div class="col-md-6 col-xl-4 gradebook-card-item">
                            <div class="card border-0 shadow-sm h-100 grade-course-card bg-white">
                                <div class="card-body p-4 d-flex flex-column">

                                    <!-- Header Card -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="avatar avatar-md">
                                            <span class="avatar-initial rounded-circle bg-label-primary p-2">
                                                <i class="bx bx-book-open fs-3"></i>
                                            </span>
                                        </div>
                                        <span class="badge bg-label-secondary rounded-pill px-3 text-uppercase fw-semibold">
                                            {{ strtoupper($item->jenis_kelas ?? '-') }}
                                        </span>
                                    </div>

                                    <!-- Meta Info -->
                                    <small class="text-primary fw-semibold">{{ $mataKuliah?->matakuliah_id ?? '-' }}</small>
                                    <h5 class="fw-bold mt-1 mb-1 text-dark">{{ $mataKuliah?->nama ?? '-' }}</h5>
                                    <p class="text-muted small mb-4"><i class="bx bx-buildings me-1"></i>{{ $programStudi?->nama ?? '-' }}</p>

                                    <!-- Progress & Score Box -->
                                    <div class="bg-light p-3 rounded-3 mb-4 mt-auto border">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <span class="text-muted small fw-medium">Nilai Sementara</span>
                                            <strong class="text-{{ $warna }} fs-5">{{ $item->persentase_gradebook }}%</strong>
                                        </div>

                                        <div class="progress mb-3 shadow-none bg-white border" style="height: 8px; border-radius: 10px;">
                                            <div class="progress-bar bg-{{ $warna }}" role="progressbar" style="width: {{ min(100, $item->persentase_gradebook) }}%; border-radius: 10px;" aria-valuenow="{{ $item->persentase_gradebook }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center small text-muted">
                                            <span><i class="bx bx-check-double me-1"></i>{{ $item->tugas_dinilai_gradebook }}/{{ $item->tugas->count() + $item->quiz->count() }} dinilai</span>
                                            <span class="fw-bold text-dark">{{ number_format($item->nilai_gradebook, 0) }} / {{ number_format($item->total_maksimal_gradebook, 0) }}</span>
                                        </div>
                                    </div>

                                    <!-- Button Action -->
                                    <a href="{{ route('mahasiswa.lms.gradebook.show', $item) }}" class="btn btn-primary rounded-pill w-100 shadow-sm">
                                        <i class="bx bx-list-check me-1"></i> Lihat Rincian Nilai
                                    </a>

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12" id="emptyRow">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body text-center py-5">
                                    <i class="bx bx-bar-chart-alt-2 text-muted mb-3" style="font-size: 4rem;"></i>
                                    <h5 class="fw-bold">Belum Ada Rekap Nilai</h5>
                                    <p class="text-muted mb-0">Belum ada mata kuliah aktif pada tahun akademik ini.</p>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

    </div>
</div>

<style>
    .grade-course-card {
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .grade-course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(30, 60, 114, 0.15) !important;
    }
</style>

@push('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('filterGradebook');
        const cards = document.querySelectorAll('.gradebook-card-item');
        const noDataLabel = document.getElementById('noDataResult');
        const emptyRow = document.getElementById('emptyRow');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase().trim();
                let totalVisibleCards = 0;

                cards.forEach(card => {
                    const textData = card.innerText.toLowerCase();

                    if (textData.includes(query)) {
                        card.style.display = '';
                        totalVisibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (totalVisibleCards === 0 && !emptyRow) {
                    noDataLabel.classList.remove('d-none');
                } else {
                    noDataLabel.classList.add('d-none');
                }
            });
        }
    });
</script>
@endpush
@endsection