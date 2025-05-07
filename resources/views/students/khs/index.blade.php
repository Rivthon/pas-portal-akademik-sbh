@extends('layouts.mahasiswa')
@section('title', 'Kartu Hasil Studi')
@section('content')

<div class="item mt-4">
    @php
    $user = auth()->guard('mahasiswa')->user();
    $edom = $user->status_edom;
    @endphp
    @if($edom == 0)
    <div class="card shadow-sm mb-4">
        <div class="d-flex align-items-center row g-0">
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-3">
                    <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="img-fluid"
                        alt="Illustration of a schedule" style="max-height: 200px;">
                </div>
            </div>
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="card-title text-primary mb-3 fw-bold">
                        Evaluasi Dosen Mengajar (EDOM)
                    </h5>
                    <!-- Conditional Alert -->
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3"
                        role="alert">
                        <div>
                            <strong>Perhatian:</strong> Belum Bisa Melihat Kartu Hasil Studi. Silakan mengisi EDOM
                            terlebih
                            dahulu.
                        </div>
                        <i class="bx bx-info-circle fs-4 text-warning"></i>
                    </div>
                    <!-- Description -->
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Evaluasi Dosen Mengajar (EDOM) adalah proses penting untuk menilai kinerja dosen dalam
                        mengajar. Pastikan Anda telah mengisi EDOM untuk semua mata kuliah yang Anda ambil
                        sebelum melanjutkan ke tahap berikutnya.
                    </p>
                    <!-- Additional Message -->

                    <!-- CTA Button -->
                    <div class="mb-3">
                        <a href="{{ route('mahasiswa.edom.index') }}" class="btn btn-primary" aria-disabled="true">
                            Mulai Mengis EDOM
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @elseif($edom == 1)
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center item g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Kartu Hasil Studi (Mahasiswa)
                        </h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            List Kartu Hasil Studi (Semester {{ $mahasiswa->semester }}) - Tahun Ajaran: {{
                            $ta->nama }}
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="{{route('mahasiswa.khs.cetak')}}" class="btn btn-primary">
                                Cetak Kartu Hasil Studi
                            </a>
                            <a href="{{ route('mahasiswa.edom.index') }}" class="btn btn-warning" aria-disabled="true">
                                Riwayat EDOM
                            </a>
                        </div>

                    </div>
                </div>
                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-light">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th align="center">NO</th>
                                <th>MATA KULIAH</th>
                                <th>SKS</th>
                                <th>HM</th>
                                <th>AM</th>
                                <th>SKS X AM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            // Function to calculate grade weight
                            function calculateWeight($grade) {
                            return match ($grade) {
                            'A' => 4.00,
                            'AB' => 3.75,
                            'BA' => 3.50,
                            'B' => 3.00,
                            'BC' => 2.75,
                            'C' => 2.00,
                            'D' => 1.00,
                            'E' => 0,
                            default => 0,
                            };
                            }
                            @endphp
                            @forelse ($khs as $index => $item)
                            @php
                            $bobot = calculateWeight($item->khs);
                            $bobot2 = $item->kurikulum->mataKuliah->sks * $bobot;
                            @endphp
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->sks }}</td>
                                <td>{{ $item->khs }}</td>
                                <td>{{ $bobot }}</td>
                                <td>{{ $bobot2 }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada mata kuliah yang diambil.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="5" align="center">Jumlah SKS</th>
                                <th colspan="2">{{ $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks) }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" align="center">Jumlah SKS x AM</th>
                                <th colspan="2">{{ $khs->sum(fn($item) => $item->kurikulum->mataKuliah->sks *
                                    calculateWeight($item->khs)) }}
                                </th>
                            </tr>
                            <tr>
                                <th colspan="5" align="center">IPS (Indeks Prestasi Semester)</th>
                                <th colspan="2">{{ number_format($ips, 2) }}</th>
                            </tr>
                            <tr>
                                <th colspan="5" align="center">IPK (Indeks Prestasi Kumulatif)</th>
                                <th colspan="2">{{ number_format($ipk, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>

@endsection