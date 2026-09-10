@extends('layouts.mahasiswa')
@section('title', 'Evaluasi Dosen Mengajar')
@section('content')
<div class="container">
    <div class="item mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="d-flex align-items-center item g-0">
                    <!-- Content Section -->
                    <div class="col-md-7">
                        <div class="card-body">
                            <!-- Title -->
                            <h5 class="card-title text-primary mb-3 fw-bold">
                                Evaluasi Dosen Mengajar (EDOM)
                            </h5>
                            <!-- Description -->
                            <p class="mb-4 text-muted" style="line-height: 1.6;">
                                {{ $mahasiswa->nama }} / Tahun Ajaran: {{
                                $activeTA->nama }} ({{ $activeTA->semester }})
                            </p>
                            <!-- CTA Button -->
                            <div class="mb-3">
                                Selamat datang di halaman Evaluasi Dosen Mengajar. Mohon untuk memilih dosen terlebih
                                dahulu sebelum mengisi EDOM.
                                Setelah semua selesai diisi, klik tombol submit untuk menyelesaikan pengisian EDOM.
                            </div>
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Image Section -->
                    <div class="col-md-5 text-center">
                        <div class="p-3">
                            <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid"
                                alt="Illustration of a schedule" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-light">
                <div class="card-body">

                    <div class="table-responsive text-nowrap">
                        @if ($isHistorical && $allFilled)
                        <div class="container mt-4 mb-4">
                            <a href="{{ route('mahasiswa.khs.riwayat', ['ta_id' => $selectedTaId]) }}" class="btn btn-primary">
                                <i class="bx bx-history me-1"></i>Lihat Riwayat KHS
                            </a>
                        </div>
                        @elseif (! $isHistorical && $mahasiswa->status_edom == 1)
                        <div class="container mt-4 mb-4">
                            <a href="{{ route('mahasiswa.kartu-hasil.index') }}" class="btn btn-primary">Lihat KHS</a>
                        </div>
                        @endif
                        @if ($krsList->isEmpty())
                        <div class="alert alert-warning text-center">
                            <i class="bx bxs-error-circle"></i>
                            <strong>Perhatian!</strong> Anda belum mengisi KRS untuk semester ini.
                            Silakan isi KRS terlebih dahulu sebelum mengakses EDOM.
                        </div>
                        @elseif ($allFilled)
                        <div class="alert alert-success text-center">
                            <i class="bx bxs-check-circle"></i>
                            Terima kasih, Anda telah mengisi semua EDOM.
                            Untuk melihat Kartu Hasil Studi (KHS) Anda, silakan konfirmasi pengisian EDOM.
                        </div>
                        @else
                        <div class="alert alert-info text-center my-3">
                            <i class="bx bxs-info-circle"></i>
                            Silakan isi EDOM untuk setiap dosen yang dipilih.
                            Setelah semua EDOM terisi, Anda dapat mengonfirmasi pengisian untuk melihat Kartu Hasil
                            Studi (KHS).
                        </div>
                        @endif

                        @php
                            $totalDosen = 0;
                            $ratedDosen = 0;
                            foreach($krsList as $krs) {
                                foreach($krs['dosen'] as $dosen) {
                                    $totalDosen++;
                                    if($dosen['is_rated']) {
                                        $ratedDosen++;
                                    }
                                }
                            }
                            $progressPercentage = $totalDosen > 0 ? round(($ratedDosen / $totalDosen) * 100) : 0;
                        @endphp

                        @if(!$krsList->isEmpty() && $totalDosen > 0)
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold">Progress Pengisian EDOM</span>
                                <span class="badge bg-primary">{{ $ratedDosen }} / {{ $totalDosen }} Dosen</span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar progress-bar-striped progress-bar-animated {{ $progressPercentage == 100 ? 'bg-success' : 'bg-primary' }}"
                                     role="progressbar"
                                     style="width: {{ $progressPercentage }}%"
                                     aria-valuenow="{{ $progressPercentage }}"
                                     aria-valuemin="0"
                                     aria-valuemax="100"></div>
                            </div>
                        </div>
                        @endif
                        <div class="row g-4 mb-4">
                            @forelse ($krsList as $index => $kurikulum)
                            <div class="col-12">
                                <div class="card h-100 shadow-sm border border-primary">
                                    <div class="card-header bg-label-primary d-flex align-items-center justify-content-between p-3">
                                        <h6 class="mb-0 text-primary fw-bold d-flex align-items-center">
                                            <i class="bx bx-book-open me-2 fs-5"></i>
                                            {{ $kurikulum['kode_matakuliah'] }} — {{ $kurikulum['nama_matakuliah'] }}
                                        </h6>
                                        <span class="badge bg-primary rounded-pill">#{{ $index + 1 }}</span>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row">
                                            <!-- Dosen Teori -->
                                            <div class="col-md-6 mb-4 mb-md-0">
                                                <small class="text-muted text-uppercase fw-bold d-block mb-3"><i class="bx bx-chalkboard me-1"></i> Dosen Teori</small>
                                                @php
                                                    $dosenTeori = collect($kurikulum['dosen'])->where('jenis_dosen', 'teori');
                                                @endphp

                                                @if ($dosenTeori->isNotEmpty())
                                                    <div class="d-flex flex-column gap-3">
                                                    @foreach ($dosenTeori as $dosen)
                                                        @if ($dosen['is_rated'])
                                                            <div class="p-3 border rounded border-success bg-label-success d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center text-success">
                                                                    <i class="bx bx-user-check me-2 fs-4"></i>
                                                                    <span class="fw-semibold">{{ $dosen['nama'] }}</span>
                                                                </div>
                                                                <i class="bx bxs-check-circle text-success fs-3 flex-shrink-0"></i>
                                                            </div>
                                                        @else
                                                            <a href="{{ route('mahasiswa.edom.form', ['krs_id' => $kurikulum['krs_id'], 'dosen_id' => $dosen['id'], 'ta_id' => $selectedTaId]) }}" class="p-3 border rounded border-warning bg-label-warning d-flex justify-content-between align-items-center text-decoration-none" style="transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                                                <div class="d-flex align-items-center text-warning">
                                                                    <i class="bx bx-user me-2 fs-4"></i>
                                                                    <span class="fw-bold">{{ $dosen['nama'] }}</span>
                                                                </div>
                                                                <span class="badge bg-warning px-3 py-2 shadow-sm flex-shrink-0"><i class="bx bx-edit-alt me-1"></i> NILAI</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-3 border rounded border-secondary bg-label-secondary text-muted text-center fst-italic">
                                                        - Tidak ada dosen teori -
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- Dosen Praktik -->
                                            <div class="col-md-6">
                                                <small class="text-muted text-uppercase fw-bold d-block mb-3"><i class="bx bx-laptop me-1"></i> Dosen Praktik</small>
                                                @php
                                                    $dosenPraktik = collect($kurikulum['dosen'])->where('jenis_dosen', 'praktik');
                                                @endphp

                                                @if ($dosenPraktik->isNotEmpty())
                                                    <div class="d-flex flex-column gap-3">
                                                    @foreach ($dosenPraktik as $dosen)
                                                        @if ($dosen['is_rated'])
                                                            <div class="p-3 border rounded border-success bg-label-success d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center text-success">
                                                                    <i class="bx bx-user-check me-2 fs-4"></i>
                                                                    <span class="fw-semibold">{{ $dosen['nama'] }}</span>
                                                                </div>
                                                                <i class="bx bxs-check-circle text-success fs-3 flex-shrink-0"></i>
                                                            </div>
                                                        @else
                                                            <a href="{{ route('mahasiswa.edom.form', ['krs_id' => $kurikulum['krs_id'], 'dosen_id' => $dosen['id'], 'ta_id' => $selectedTaId]) }}" class="p-3 border rounded border-warning bg-label-warning d-flex justify-content-between align-items-center text-decoration-none" style="transition: transform 0.2s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                                                                <div class="d-flex align-items-center text-warning">
                                                                    <i class="bx bx-user me-2 fs-4"></i>
                                                                    <span class="fw-bold">{{ $dosen['nama'] }}</span>
                                                                </div>
                                                                <span class="badge bg-warning px-3 py-2 shadow-sm flex-shrink-0"><i class="bx bx-edit-alt me-1"></i> NILAI</span>
                                                            </a>
                                                        @endif
                                                    @endforeach
                                                    </div>
                                                @else
                                                    <div class="p-3 border rounded border-secondary bg-label-secondary text-muted text-center fst-italic">
                                                        - Tidak ada dosen praktik -
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="col-12">
                                <div class="alert alert-secondary text-center p-4">
                                    <i class="bx bx-info-circle fs-3 mb-2"></i><br>
                                    Tidak ada data mata kuliah yang ditemukan.
                                </div>
                            </div>
                            @endforelse
                        </div>

                    @if ($allFilled && ! $isHistorical && $mahasiswa->status_edom != 1 && !$krsList->isEmpty() && $totalDosen > 0)
                    <div class="d-flex justify-content-center mt-4 mb-4">
                        <form action="{{ route('mahasiswa.edom.konfirmasi') }}" method="POST" id="formKonfirmasiEdom">
                            @csrf
                            <input type="hidden" name="ta_id" value="{{ $selectedTaId }}">
                            <button type="button" class="btn btn-success btn-lg" onclick="konfirmasiEdomSubmit()">
                                <i class="bx bx-check-double me-2"></i>Konfirmasi Pengisian EDOM
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push(('script'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    function konfirmasiEdomSubmit() {
        Swal.fire({
            title: 'Konfirmasi EDOM?',
            text: "Setelah dikonfirmasi, Anda akan dapat melihat Kartu Hasil Studi (KHS). Anda yakin semua data sudah benar?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Konfirmasi!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Memproses...',
                    text: 'Mohon tunggu sebentar',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                document.getElementById('formKonfirmasiEdom').submit();
            }
        });
    }
</script>
@endpush