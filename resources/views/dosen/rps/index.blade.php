@extends('layouts.dosen')
@section('title', 'Rencana Pembelajaran Semester (RPS)')

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        
        <!-- Banner Header (Tema Gradien) -->
        <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <div class="col-md-8 text-white p-3">
                        <h4 class="card-title mb-3 fw-bold text-white">
                            <i class="bx bx-file me-2"></i>Rencana Pembelajaran Semester (RPS)
                        </h4>
                        <p class="mb-0 text-white-50" style="line-height: 1.6;">
                            Kelola dokumen Rencana Pembelajaran Semester (RPS) untuk setiap mata kuliah yang Anda ampu.<br>
                            Pastikan Anda telah mengunggah RPS terbaru untuk kelancaran proses akademik.
                        </p>
                    </div>

                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bx-book-reader text-white" style="font-size: 7rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Data Section -->
        <div class="card shadow-sm mb-4 border-0">
            <!-- Filter / Pencarian -->
            <div class="card-header bg-white pt-4 pb-3 border-bottom">
                <div class="row align-items-center gx-3">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="input-group input-group-merge shadow-sm rounded-pill border">
                            <span class="input-group-text bg-white border-0 rounded-pill-start" id="basic-addon-search">
                                <i class="bx bx-search"></i>
                            </span>
                            <input type="text" id="filterRps" class="form-control border-0 rounded-pill-end ps-0" placeholder="Cari Mata Kuliah, Prodi, atau Kelas..." aria-label="Search...">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-body bg-light mt-0 pt-4">

                <div id="noDataResult" class="alert alert-warning text-center d-none shadow-sm border-0">
                    <i class="bx bx-info-circle fs-3 mb-2 d-block"></i>
                    Pencarian tidak menemukan data mata kuliah yang sesuai.
                </div>

                <div class="table-responsive text-nowrap bg-white rounded-3 shadow-sm border">
                    <table class="table table-hover table-borderless align-middle mb-0" id="rpsTable">
                        <thead class="table-light border-bottom">
                            <tr>
                                <th width="5%" class="text-center">No</th>
                                <th>Mata Kuliah</th>
                                <th>Program Studi</th>
                                <th class="text-center">Semester</th>
                                <th class="text-center">Kelas</th>
                                <th class="text-center">Status</th>
                                <th width="20%" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mataKuliah as $item)
                                <tr class="border-bottom rps-row">
                                    <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                    <td class="fw-bold text-dark">{{ $item->kurikulum->mataKuliah->nama }}</td>
                                    <td>{{ $item->kurikulum->programStudi->nama }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-label-secondary rounded-pill px-3">
                                            SMT {{ $item->kurikulum->mataKuliah->smt ?? $item->kurikulum->mataKuliah->semester }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($item->jenis_kelas == 'reguler')
                                            <span class="badge bg-label-primary rounded-pill px-3">REGULER</span>
                                        @else
                                            <span class="badge bg-label-warning text-dark rounded-pill px-3">KARYAWAN</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->rps)
                                            <span class="badge bg-success rounded-pill px-3">
                                                <i class="bx bx-check-circle me-1"></i> SUDAH UPLOAD
                                            </span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3">
                                                <i class="bx bx-x-circle me-1"></i> BELUM UPLOAD
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            @if($item->rps)
                                                <a href="{{ route('dosen.rps.show', $item->rps) }}?v={{ $item->rps->updated_at?->timestamp }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                                                    <i class="bx bx-show me-1"></i> Lihat
                                                </a>
                                            @endif
                                            <button class="btn {{ $item->rps ? 'btn-warning' : 'btn-primary' }} btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadRps{{ $item->kurikulum_id }}-{{ strtolower($item->jenis_kelas) }}">
                                                <i class="bx bx-upload me-1"></i> {{ $item->rps ? 'Upload Ulang' : 'Upload' }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @include('dosen.rps.modal-upload')
                            @empty
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                                        Belum ada data mata kuliah.
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
        const searchInput = document.getElementById('filterRps');
        const rows = document.querySelectorAll('.rps-row');
        const noDataLabel = document.getElementById('noDataResult');
        const emptyRow = document.getElementById('emptyRow');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase().trim();
                let totalVisibleRows = 0;

                rows.forEach(row => {
                    const textData = row.innerText.toLowerCase();

                    if (textData.includes(query)) {
                        row.style.display = '';
                        totalVisibleRows++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                if (totalVisibleRows === 0 && !emptyRow) {
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