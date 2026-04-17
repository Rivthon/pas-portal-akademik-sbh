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
        @elseif($status_jadwal_uap == 1)
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

        @php
            $jadwalByDate = collect($jadwal)->groupBy('tanggal')->map(function($items, $date) {
                return [
                    'tanggal_str' => \Carbon\Carbon::parse($date)->translatedFormat('l, d F Y'),
                    'tanggal_sort' => $date,
                    'items' => $items
                ];
            })->sortBy('tanggal_sort');
        @endphp

        @if($jadwalByDate->isEmpty())
            <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
                <i class="bx bx-info-circle fs-4 me-3"></i>
                <div>
                    <strong>Belum ada jadwal!</strong><br>
                    Tidak ada jadwal UAP yang tersedia untuk semester ini.
                </div>
            </div>
        @else
            <!-- Timeline based grouped by date -->
            @foreach($jadwalByDate as $group)
                <div class="mb-5">
                    <h5 class="fw-bold mb-3 d-flex align-items-center text-secondary">
                        <i class='bx bx-calendar-star me-2 text-primary fs-4'></i> {{ $group['tanggal_str'] }}
                    </h5>
                    <div class="row">
                        @foreach($group['items'] as $item)
                            <div class="col-lg-6 mb-3">
                                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #696cff !important;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold text-dark mb-0">{{ $item->nama }}</h6>
                                            <span class="badge bg-primary text-white rounded-pill px-2 py-1"><i
                                                    class='bx bx-layer me-1'></i> UAP</span>
                                        </div>

                                        <p class="text-muted small mb-3">
                                            <span class="me-3 fw-medium"><i class='bx bx-time-five me-1'></i>
                                                {{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - {{ \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}</span>
                                        </p>

                                        <hr class="my-2 border-light">

                                        <div class="mt-2 text-muted small d-flex flex-column gap-1">
                                            <span class="d-flex align-items-center"><i class='bx bx-building-house me-2 text-primary'></i> {{ $item->programStudi->nama ?? '-' }}</span>
                                            <span class="d-flex align-items-center"><i class='bx bx-calendar-event me-2 text-primary'></i> {{ $item->tahunAkademik->nama ?? '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
        @endif
    </div>

</div>
@endsection