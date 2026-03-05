@extends('layouts.mahasiswa')
@section('title', 'Nilai Ujian Tengah Semester')
@section('content')

<!-- Title Card -->
<div class="row mt-4">
    <div class="col-md-12">
        @php
        $user = auth()->guard('mahasiswa')->user();
        $status_nilai_mhs = $user->status_nilai_uts;
        @endphp
        @if($status_nilai_mhs == 0)
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
                            Nilai Ujian Tengah Semester
                        </h5>
                        <!-- Conditional Alert -->
                        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3"
                            role="alert">
                            <div>
                                <strong>Perhatian:</strong> Status UTS belum aktif.
                            </div>
                            <i class="bx bx-info-circle fs-4 text-warning"></i>
                        </div>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan nilai ujian tengah semester Anda dengan mudah. Nilai ini mencakup informasi
                            mata kuliah,
                            hari, waktu, dan ruang kelas untuk semester aktif.
                        </p>
                        <!-- Additional Message -->
                        <p class="text-danger fw-bold mb-4">
                            Silahkan untuk melakukan verifikasi pembayaran kepada BAUK agar bisa mencetak dan
                            melihat nilai
                            UAS.
                        </p>
                        <!-- CTA Button -->
                        {{-- <div class="mb-3">
                            <a href="#" class="btn btn-primary disabled" aria-disabled="true">
                                Cetak Nilai Ujian Akhir Semester
                            </a>
                        </div> --}}
                    </div>
                </div>

            </div>
        </div>
        @else($status_nilai_mhs == 1)
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="col-md-7">
                        <div class="card-body">
                            <h5 class="card-title text-primary fw-bold mb-3">
                                Nilai UTS (Mahasiswa)
                            </h5>
                            <p class="text-muted mb-4" style="line-height: 1.6;">
                                <strong>{{ $mahasiswa->nama }} <br>
                                    {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun
                                    Ajaran: {{
                                    $ta->nama }}</strong>
                                <hr>
                                Nilai UTS ini merupakan nilai yang diperoleh dari ujian tengah semester yang diambil
                                oleh
                                mahasiswa.
                            </p>
                        </div>
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
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th style="text-align: center;">NO</th>
                                <th>Kode Kuliah</th>
                                <th>Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($nilai as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                                <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                                <td class="text-center">{{ $item->kurikulum->mataKuliah->sks }}</td>
                                <td class="text-center">{{ $item->uts }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted">Tidak ada mata kuliah yang diambil.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
