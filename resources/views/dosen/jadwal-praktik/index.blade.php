@extends('layouts.dosen')
@section('title', 'Jadwal Mengajar Praktik Dosen')
@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <!-- Content Section -->
                    <div class="col-md-7 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-calendar-star me-2"></i>Jadwal Praktik</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Berikut adalah jadwal praktik (laboratorium/klinik) yang Anda ampu pada semester <br>
                            <strong class="text-white">{{ $activeTA->nama ?? '' }} - {{ $activeTA->semester ?? '' }}</strong>.<br>
                            Gunakan filter di bawah untuk mempermudah pencarian mata kuliah atau jenis kelas.
                        </p>
                    </div>

                    <!-- Image Section -->
                    <div class="col-md-5 text-center d-none d-md-block">
                        <img src="{{ asset('assets/img/illustrations/calender.png') }}" class="img-fluid"
                            alt="Illustration for schedule" style="max-height: 150px;">
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-searchPractik"><i class="bx bx-search"></i></span>
                            <input type="text" id="filterNamaJadwal" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Praktik atau Asisten..." aria-label="Search..." aria-describedby="basic-addon-searchPractik">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select id="filterJenisKelas" class="form-select shadow-sm rounded-pill cursor-pointer border">
                            <option value="semua">-- Tampilkan Semua Jenis Kelas Praktik --</option>
                            <option value="reguler">Kelas Reguler (Pagi/Siang)</option>
                            <option value="karyawan">Kelas Karyawan (Malam/Eksekutif)</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="card-body bg-light mt-0 pt-4">
                <div id="noDataResult" class="alert alert-warning text-center d-none">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan jadwal praktik yang sesuai.
                </div>
                <div id="jadwalContainer">
                    @include('dosen.jadwal-praktik.partial_list')
                </div>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('filterNamaJadwal');
        const classFilter = document.getElementById('filterJenisKelas');
        const dayContainers = document.querySelectorAll('.jadwal-hari-container');
        const noDataLabel = document.getElementById('noDataResult');

        function filterJadwal() {
            const query = searchInput.value.toLowerCase().trim();
            const selectedClass = classFilter.value.toLowerCase();
            let totalVisibleCards = 0;

            dayContainers.forEach(container => {
                const cardsInDay = container.querySelectorAll('.jadwal-card-item');
                let visibleInDay = 0;

                cardsInDay.forEach(card => {
                    const mkName = (card.getAttribute('data-nama') || "").toLowerCase();
                    const dsnName = (card.getAttribute('data-dosen') || "").toLowerCase();
                    const jenisKelas = (card.getAttribute('data-jenis') || "").toLowerCase();
                    
                    const matchText = mkName.includes(query) || dsnName.includes(query);
                    const matchClass = selectedClass === 'semua' || 
                                       jenisKelas === selectedClass || 
                                       (selectedClass === 'reguler' && jenisKelas !== 'karyawan');

                    if (matchText && matchClass) {
                        card.style.display = 'block';
                        visibleInDay++;
                        totalVisibleCards++;
                    } else {
                        card.style.display = 'none';
                    }
                });

                if (visibleInDay > 0) {
                    container.style.display = 'block';
                } else {
                    container.style.display = 'none';
                }
            });

            if (totalVisibleCards === 0) {
                noDataLabel.classList.remove('d-none');
            } else {
                noDataLabel.classList.add('d-none');
            }
        }

        if (searchInput && classFilter) {
            searchInput.addEventListener('input', filterJadwal);
            classFilter.addEventListener('change', filterJadwal);
        }
    });
</script>
@endpush
@endsection