@extends('layouts.mahasiswa')
@section('title', 'Learning Management System')

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
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2 fw-semibold">
                                <i class="bx bx-calendar me-1"></i>Tahun Akademik {{ $activeTA->nama ?? $activeTA->nama ?? - 'Tahun Akademik Aktif' }} -{{ $activeTA->semester ?? $activeTA->semester ?? - '' }}
                            </span>
                        </div>
                        <h4 class="card-title mb-2 fw-bold text-white"><i class="bx bx-book-reader me-2"></i>Learning Management System (LMS)</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Pilih mata kuliah yang Anda ambil semester ini untuk mengunduh materi perkuliahan, mengerjakan tugas, quiz, serta melihat rekap riwayat pembelajaran.
                        </p>
                    </div>

                    <!-- Icon/Image Section -->
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bxs-graduation text-white" style="font-size: 7rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-4" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <x-lms-calendar
            :events="$calendarEvents"
            :store-route="route('mahasiswa.lms.calendar.notes.store')"
            :update-route-template="route('mahasiswa.lms.calendar.notes.update', ['note' => '__NOTE__'])"
            :destroy-route-template="route('mahasiswa.lms.calendar.notes.destroy', ['note' => '__NOTE__'])"
            calendar-id="mahasiswaLmsCalendar"
            :focus-upcoming="true"
        />

        <!-- Filter & Search Section -->
        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-search"><i class="bx bx-search"></i></span>
                            <input type="text" id="filterLmsMahasiswa" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah, Kode MK, atau Program Studi..." aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light mt-0 pt-4">

                <div id="noDataResult" class="alert alert-warning text-center d-none shadow-sm border-0">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan mata kuliah yang sesuai.
                </div>

                <x-lms-course-list :jadwal-list="$jadwalList" role="mahasiswa" />

            </div>
        </div>

    </div>
</div>

<style>
    .lms-course-card {
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .lms-course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1.5rem rgba(30, 60, 114, 0.15) !important;
    }
</style>

@push('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('filterLmsMahasiswa');
        const cards = document.querySelectorAll('.lms-card-item');
        const semesterGroups = document.querySelectorAll('.lms-semester-group');
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

                semesterGroups.forEach(group => {
                    const hasVisibleCard = Array.from(group.querySelectorAll('.lms-card-item'))
                        .some(card => card.style.display !== 'none');
                    group.style.display = hasVisibleCard ? '' : 'none';

                    if (query && hasVisibleCard) {
                        group.querySelector('.accordion-collapse')?.classList.add('show');
                        group.querySelector('.lms-semester-toggle')?.setAttribute('aria-expanded', 'true');
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
