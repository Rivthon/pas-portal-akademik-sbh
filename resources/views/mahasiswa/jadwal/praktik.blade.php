@extends('layouts.mahasiswa')
@section('title', 'Jadwal Praktik')
@section('content')

    @php
        // Group schedules by day and define a static sorting order for the days
        $jadwalByDay = collect($jadwals)->groupBy('hari');
        $daysOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Sort keys based on daysOrder
        $jadwalByDay = $jadwalByDay->sortBy(function ($val, $key) use ($daysOrder) {
            $index = array_search(ucfirst($key), $daysOrder);
            return $index !== false ? $index : 999;
        });
    @endphp

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4 border-0">
                <div class="d-flex flex-column flex-md-row align-items-center g-0">
                    <!-- Content Section -->
                    <div class="col-md-7 p-4">
                        <h4 class="text-primary mb-3 fw-bold">
                            Jadwal Praktik / Lab
                        </h4>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal praktik Anda dengan mudah. Jadwal ini mencakup seluruh informasi mata kuliah,
                            hari, waktu, dan ruang lab untuk semester aktif saat ini.
                        </p>

                        <!-- Toggle Switcher -->
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('mahasiswa.jadwal-kuliah.index') }}" class="btn btn-outline-primary px-4">
                                <i class='bx bx-book-reader me-1'></i> Kuliah Teori
                            </a>
                            <a href="{{ route('mahasiswa.jadwal-praktik.index') }}" class="btn btn-primary px-4 shadow-sm">
                                <i class='bx bx-laptop me-1'></i> Praktik / Lab
                            </a>
                        </div>
                    </div>
                    <!-- Image Section -->
                    <div class="col-md-5 text-center d-none d-md-block p-4">
                        <img src="{{ asset('assets/img/illustrations/calender.png') }}" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 180px;">
                    </div>
                </div>
            </div>

            @if($jadwalByDay->isEmpty())
                <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
                    <i class="bx bx-info-circle fs-4 me-3"></i>
                    <div>
                        <strong>Belum ada jadwal!</strong><br>
                        Tidak ada jadwal praktik yang tersedia untuk semester ini.
                    </div>
                </div>
            @else
                <!-- Timeline based grouped by day -->
                @foreach($jadwalByDay as $hari => $items)
                    <div class="mb-5">
                        <h5 class="fw-bold mb-3 d-flex align-items-center text-secondary">
                            <i class='bx bx-calendar-star me-2 text-primary fs-4'></i> {{ ucfirst($hari) }}
                        </h5>
                        <div class="row">
                            @foreach($items as $item)
                                <div class="col-lg-6 mb-3">
                                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #03c3ec !important;">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="fw-bold text-dark mb-0">{{ $item['nama_matakuliah'] ?? 'Tidak ada data' }}
                                                </h6>
                                                <span class="badge bg-info text-white rounded-pill px-2 py-1"><i
                                                        class='bx bx-layer me-1'></i> SMT {{ $item['semester'] ?? '-' }}</span>
                                            </div>

                                            <p class="text-muted small mb-3">
                                                <span class="me-3 fw-medium"><i class='bx bx-time-five me-1'></i>
                                                    {{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai'] ?? '-' }}</span>
                                                <span class="fw-medium"><i class='bx bx-building me-1'></i>
                                                    {{ $item['ruangan'] ?? '-' }}</span>
                                            </p>

                                            <hr class="my-2 border-light">

                                            <div class="mt-2">
                                                <span class="small fw-semibold text-muted d-block mb-1">Dosen Pengajar:</span>
                                                @forelse ($item['dosen'] as $dosen)
                                                    <div class="d-flex align-items-center small mt-1">
                                                        <i class="bx bx-user-circle text-info me-2 fs-5"></i>
                                                        <span class="text-dark fw-medium">{{ $dosen['nama'] ?? 'Tidak ada data' }}</span>
                                                    </div>
                                                @empty
                                                    <span class="text-danger small">Belum ada dosen terdaftar.</span>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
@endsection