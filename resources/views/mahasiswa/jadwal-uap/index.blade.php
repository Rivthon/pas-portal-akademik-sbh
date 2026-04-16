@extends('layouts.mahasiswa')
@section('title', 'Jadwal Ujian Akhir Program')
@section('content')

<div class="row mt-4">
    <div class="col-md-12">
        @php
        $user = auth()->guard('mahasiswa')->user();
        $status_jadwal_uap = $user->status_uap;
        @endphp
        @if($status_jadwal_uap == 0)
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 200px;">
                    </div>
                </div>
                @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Gagal!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Jadwal Ujian Akhir Program
                        </h5>
                        <!-- Conditional Alert -->
                        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3"
                            role="alert">
                            <div>
                                <strong>Perhatian:</strong> Status jadwal UAP belum aktif.
                            </div>
                            <i class="bx bx-info-circle fs-4 text-warning"></i>
                        </div>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal ujian akhir program Anda dengan mudah. Jadwal ini mencakup informasi
                            mata kuliah,
                            hari, waktu, dan ruang kelas untuk semester aktif.
                        </p>
                        <!-- Additional Message -->
                        <p class="text-danger fw-bold mb-4">
                            Silahkan untuk melakukan verifikasi pembayaran kepada BAUK agar bisa mencetak dan
                            melihat jadwal
                            UAP.
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="#" class="btn btn-primary disabled" aria-disabled="true">
                                Cetak Jadwal Ujian Akhir Program
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @else($status_jadwal_uap == 1)
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">Jadwal Ujian Akhir Program</h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal ujian akhir program Anda dengan mudah. Jadwal ini mencakup informasi
                            mata kuliah,
                            hari, waktu, dan ruang kelas untuk semester aktif.
                        </p>

                        <!-- Conditional CTA Button -->

                        <div class="mb-3">
                            <a href="{{ route('mahasiswa.cetak.kartu.uap') }}" class="btn btn-primary "
                                aria-disabled="false">
                                Cetak Jadwal Ujian Akhir Program
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">

                        <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                            alt="Illustration for morning schedule" style="max-height: 200px;">

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
                                <th>#</th>
                                <th>Tahun Akademik</th>
                                <th>Program Studi</th>
                                <th>Nama</th>
                                <th>Jam Mulai</th>
                                <th>Jam Selesai</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jadwal as $index => $jadwal)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $jadwal->tahunAkademik->nama ?? '-' }}</td>
                                <td>{{ $jadwal->programStudi->nama ?? '-' }}</td>
                                <td>{{ $jadwal->nama }}</td>
                                <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data jadwal UAP.</td>
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