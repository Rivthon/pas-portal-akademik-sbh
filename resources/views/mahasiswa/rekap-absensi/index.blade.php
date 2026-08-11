@extends('layouts.mahasiswa')
@section('title', 'Rekap Absensi Perkuliahan')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        <!-- Banner Header (Tema Gradien) -->
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <!-- Content Section -->
                    <div class="col-md-8 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-calendar-check me-2"></i>Rekap Absensi Perkuliahan</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Pantau tingkat kehadiran Anda pada setiap mata kuliah Semester {{ $selectedSemester }}.<br>
                            Pastikan persentase kehadiran memenuhi syarat untuk dapat mengikuti Ujian Akhir Semester (UAS).
                        </p>
                    </div>

                    <!-- Icon/Image Section -->
                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bx-bar-chart-alt-2 text-white" style="font-size: 7rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Section -->
        <div class="card shadow-sm mb-4 border-0">
            <!-- Filter / Pencarian -->
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-lg-5 mb-3 mb-lg-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-search"><i class="bx bx-search"></i></span>
                            <input type="text" id="filterAbsensi" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah..." aria-label="Search...">
                        </div>
                    </div>
                    <div class="col-lg-7">
                        <div class="d-flex flex-wrap justify-content-lg-end align-items-center gap-2">
                            <span class="small fw-semibold text-muted me-1">Pilih Semester:</span>
                            @foreach($semesterList as $semester)
                                <a href="{{ route('mahasiswa.rekap.absensi', ['semester' => $semester]) }}"
                                    class="btn btn-sm rounded-pill {{ $selectedSemester === $semester ? 'btn-primary' : 'btn-outline-primary' }}">
                                    Semester {{ $semester }}
                                    @if($semester === $semesterBerjalan)
                                        <span class="badge bg-white text-primary ms-1">Berjalan</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light mt-0 pt-4">

                <div id="noDataResult" class="alert alert-warning text-center d-none">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan data yang sesuai.
                </div>

                <div class="table-responsive text-nowrap bg-white rounded shadow-sm border">
                    <table class="table table-hover table-borderless align-middle mb-0" id="absensiTable">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Mata Kuliah</th>
                                <th>Program Studi</th>
                                <th class="text-center">Hadir</th>
                                <th class="text-center">Izin</th>
                                <th class="text-center">Sakit</th>
                                <th class="text-center">Alpha</th>
                                <th class="text-center">Persentase</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($krs as $item)
                                <tr class="border-bottom absensi-row">
                                    <td class="text-center">{{ $loop->iteration }}</td>

                                    <td>
                                        @if($item->detail_id)
                                            <a href="{{ route('mahasiswa.rekap.absensi.detail', ['jadwalId' => $item->detail_id, 'semester' => $selectedSemester]) }}" class="fw-medium text-primary text-decoration-none">
                                                <i class="bx bx-link-external me-1"></i> {{ $item->kurikulum->mataKuliah->nama ?? '-' }}
                                            </a>
                                        @else
                                            <span class="fw-medium text-dark">{{ $item->kurikulum->mataKuliah->nama ?? '-' }}</span>
                                            <br>
                                            <small class="text-muted"><i class="bx bx-time-five me-1"></i> Jadwal belum tersedia</small>
                                        @endif
                                    </td>

                                    <td>{{ $item->kurikulum->programStudi->nama ?? '-' }}</td>

                                    <td class="text-center">
                                        <span class="badge bg-label-success rounded-pill px-3">{{ $item->hadir }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-warning rounded-pill px-3">{{ $item->izin }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-info rounded-pill px-3">{{ $item->sakit }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-label-danger rounded-pill px-3">{{ $item->alpha }}</span>
                                    </td>

                                    <td class="text-center fw-bold text-primary fs-6">
                                        {{ $item->persentase }}%
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bx bx-folder-open fs-2 mb-2 d-block"></i>
                                        Belum ada data rekap absensi untuk Semester {{ $selectedSemester }}.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

@push('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.getElementById('filterAbsensi');
        const rows = document.querySelectorAll('.absensi-row');
        const noDataLabel = document.getElementById('noDataResult');
        const emptyRow = document.getElementById('emptyRow');

        searchInput.addEventListener('input', function () {
            const query = searchInput.value.toLowerCase().trim();
            let totalVisibleRows = 0;

            rows.forEach(row => {
                // Mengambil seluruh teks dalam baris (td) untuk difilter
                const textData = row.innerText.toLowerCase();

                if (textData.includes(query)) {
                    row.style.display = '';
                    totalVisibleRows++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Menyembunyikan tabel / memunculkan alert jika tidak ada hasil
            if (totalVisibleRows === 0 && !emptyRow) {
                noDataLabel.classList.remove('d-none');
            } else {
                noDataLabel.classList.add('d-none');
            }
        });
    });
</script>
@endpush
@endsection
