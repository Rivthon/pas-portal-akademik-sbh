@extends('layouts.mahasiswa')
@section('title', 'Jadwal Ujian Tengah Semester')
@section('content')

<div class="row mt-4">
    <div class="col-md-12">
        @php
        $user = auth()->guard('mahasiswa')->user();
        $status_jadwal_uts = $user->status_uts;
        $kelas = $user->kelas;
        @endphp
        @if($status_jadwal_uts == 0)
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
                            Jadwal Ujian Tengah Semester
                        </h5>
                        <!-- Conditional Alert -->
                        <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3"
                            role="alert">
                            <div>
                                <strong>Perhatian:</strong> Status jadwal UTS belum aktif.
                            </div>
                            <i class="bx bx-info-circle fs-4 text-warning"></i>
                        </div>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal ujian tengah semester Anda dengan mudah. Jadwal ini mencakup informasi
                            mata kuliah,
                            hari, waktu, dan ruang kelas untuk semester aktif.
                        </p>
                        <!-- Additional Message -->
                        <p class="text-danger fw-bold mb-4">
                            Silahkan untuk melakukan verifikasi pembayaran kepada BAUK agar bisa mencetak dan
                            melihat jadwal
                            UTS.
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="#" class="btn btn-primary disabled" aria-disabled="true">
                                Cetak Jadwal Ujian Tengah Semester
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        @elseif($status_jadwal_uts == 1)
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">Jadwal Ujian Tengah Semester</h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal ujian tengah semester Anda dengan mudah. Jadwal ini mencakup informasi
                            mata kuliah,
                            hari, waktu, dan ruang kelas untuk semester aktif.
                        </p>

                        <!-- Conditional CTA Button -->

                        <div class="mb-3">
                            <a href="{{ route('mahasiswa.cetak.kartu.uts') }}" class="btn btn-primary "
                                aria-disabled="false">
                                Cetak Jadwal Ujian Tengah Semester
                            </a>
                        </div>

                    </div>
                </div>

                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        @if($kelas == 'pagi')
                        <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                            alt="Illustration for morning schedule" style="max-height: 200px;">
                        @elseif($kelas == 'karyawan')
                        <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                            alt="Illustration for evening schedule" style="max-height: 200px;">
                        @endif
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
                                <th>No</th>
                                <th>Nama Mata Kuliah</th>
                                <th>SKS</th>
                                <th>Tanggal</th>
                                <th>Jam Mulai - Jam Selesai</th>
                                <th>Ruangan</th>
                                <th>Jenis Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwalUts as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->mataKuliah->nama }}</td>
                                <td>{{ $item->mataKuliah->sks }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('l, d F Y') }}</td>
                                <td>{{ date('H:i', strtotime($item->jam_mulai)) }} - {{ date('H:i',
                                    strtotime($item->jam_selesai)) }}</td>
                                <td>{{ $item->ruangan->nama ?? 'Tidak ada data ruangan' }}</td>
                                <Td>{{ $item->jenis_kelas }}</Td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada jadwal tersedia.</td>
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