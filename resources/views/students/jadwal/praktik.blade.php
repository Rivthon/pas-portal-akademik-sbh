@extends('layouts.mahasiswa')
@section('title', 'Jadwal Kuliah')
@section('content')
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Jadwal Praktik Anda
                        </h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Temukan jadwal praktik Anda dengan mudah. Jadwal ini mencakup informasi mata kuliah,
                            hari, waktu, dan
                            ruang kelas untuk semester aktif.
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="#" class="btn btn-primary">
                                Lihat Jadwal Praktik
                            </a>
                        </div>
                    </div>
                </div>
                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>
        <div class="card bg-light">
            <div class="card-body">
                <div class="row">
                    @forelse ($jadwals as $key => $item)
                    <div class="col-md-6 col-lg-4">
                        <div class="card bg-light shadow-sm mb-4">
                            <div class="card-body">
                                <h5 class="card-title text-primary">
                                    {{ $item['nama_matakuliah'] ?? 'Tidak ada data' }}
                                </h5>
                                <p class="card-text">
                                    <span class="badge bg-warning text-dark">Semester {{ $item['semester'] ?? '-'
                                        }}</span>
                                </p>
                                <ul class="list-unstyled">
                                    <li><strong>Hari:</strong> <span class="badge bg-warning text-dark">{{ $item['hari']
                                            ?? '-'
                                            }}</span></li>
                                    <li><strong>Jam:</strong> {{ $item['jam_mulai'] ?? '-' }} - {{ $item['jam_selesai']
                                        ?? '-' }}</li>
                                    <li><strong>Ruangan:</strong> {{ $item['ruangan'] ?? '-' }}</li>
                                </ul>
                                <hr>
                                <p class="fw-bold mb-2">Dosen Pengajar:</p>
                                @forelse ($item['dosen'] as $dosen)
                                <p class="mb-1">
                                    <i class="bx bx-user"></i> {{ $dosen['nama'] ?? 'Tidak ada data' }}
                                    <span class="badge bg-secondary">{{ $dosen['jenis_dosen'] ?? '-' }}</span>
                                </p>
                                @empty
                                <p class="text-muted">Belum ada dosen terdaftar.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center">
                        <div class="alert alert-warning" role="alert">
                            <i class="bx bx-info-circle"></i> Tidak ada jadwal tersedia untuk semester ini.
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection