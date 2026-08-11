@extends('layouts.mahasiswa')
@section('title', 'Rencana Pembelajaran Semester (RPS)')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col-md-8 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-book-reader me-2"></i>Rencana Pembelajaran Semester (RPS)</h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Berikut adalah daftar dokumen RPS untuk mata kuliah yang Anda kontrak pada semester ini.<br>
                            Silakan unduh atau lihat RPS sebagai panduan pembelajaran Anda selama satu semester ke depan.
                        </p>
                    </div>

                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bxs-graduation text-white" style="font-size: 7rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4 border-0">
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-search"><i class="bx bx-search"></i></span>
                            <input type="text" id="filterRpsMahasiswa" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah atau Program Studi..." aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light mt-0 pt-4">

                <div id="noDataResult" class="alert alert-warning text-center d-none">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan mata kuliah yang sesuai.
                </div>

                <div class="table-responsive text-nowrap bg-white rounded shadow-sm border">
                    <table class="table table-hover table-borderless align-middle mb-0" id="rpsTable">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Mata Kuliah</th>
                                <th>Program Studi</th>
                                <th class="text-center">Semester</th>
                                <th class="text-center">Kelas</th>
                                <th width="15%" class="text-center">RPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($krs as $item)
                                <tr class="border-bottom rps-row">
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="fw-medium text-primary">{{ $item->mataKuliah->nama }}</td>
                                    <td>{{ $item->programStudi->nama }}</td>
                                    <td class="text-center">{{ $item->mataKuliah->semester }}</td>
                                    <td class="text-center">
                                        @if($jenisKelas === 'reguler')
                                            <span class="badge bg-label-primary rounded-pill px-3">REGULER</span>
                                        @else
                                            <span class="badge bg-label-warning text-dark rounded-pill px-3">KARYAWAN</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->rps)
                                            <a href="{{ route('mahasiswa.rps.show', $item->rps) }}"
                                               target="_blank"
                                               class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                                                <i class="bx bx-show me-1"></i> Lihat RPS
                                            </a>
                                        @else
                                            <span class="badge bg-label-danger rounded-pill px-3">
                                                <i class="bx bx-x-circle me-1"></i> Belum Tersedia
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bx bx-folder-open fs-2 mb-2 d-block"></i>
                                        Tidak ada data mata kuliah (KRS).
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
        const searchInput = document.getElementById('filterRpsMahasiswa');
        const rows = document.querySelectorAll('.rps-row');
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
