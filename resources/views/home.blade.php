@extends('layouts.master')
@section('title', 'Dashboard')
@section('content')
<!-- Hero Section -->
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        <div class="card">
            <div class="d-flex align-items-start row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">
                            Selamat Datang di Sistem Informasi Akademik, {{ auth()->user()->name }}! 🎓
                        </h5>
                        <p class="mb-6">
                            Semoga hari Anda menyenangkan.<br />Tetap semangat dalam menjalankan tugas Anda!
                        </p>
                        <span class="badge bg-warning text-dark">
                            @foreach (auth()->user()->getRoleNames() as $role)
                            Role <span class="text-bold">{{ $role }}</span><br>
                            @endforeach
                        </span>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-6">
                        <img src="../assets/img/illustrations/man-with-laptop.png" height="175" class="scaleX-n1-rtl"
                            alt="View Badge User" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <!-- Kolom Statistik Mahasiswa -->
    <div class="col-12 mt-4 col-md-8 order-1">
        <div class="row">
            <!-- Total Mahasiswa Aktif -->
            <div class="col-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0 bg-primary text-white rounded-circle p-2">
                                <i class="bx bx-user-check fs-4"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOptActive" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOptActive">
                                    <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Mahasiswa Aktif</p>
                        <h4 class="card-title mb-3">{{ $totalMahasiswaAktif }}</h4>
                        <small class="text-success fw-medium">
                            <i class="bx bx-up-arrow-alt"></i> +5.6%
                        </small>
                    </div>
                </div>
            </div>

            <!-- Total Mahasiswa Tidak Aktif -->
            <div class="col-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0 bg-danger text-white rounded-circle p-2">
                                <i class="bx bx-user-x fs-4"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOptInactive" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOptInactive">
                                    <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Mahasiswa Tidak Aktif</p>
                        <h4 class="card-title mb-3">{{ $totalMahasiswaTidakAktif }}</h4>
                        <small class="text-danger fw-medium">
                            <i class="bx bx-down-arrow-alt"></i> -3.2%
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0 bg-success text-white rounded-circle p-2">
                                <i class="bx bxs-graduation"></i>
                            </div>
                            <div class="dropdown">
                                <button class="btn p-0" type="button" id="cardOptGraduated" data-bs-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOptInactive">
                                    <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                </div>
                            </div>
                        </div>
                        <p class="mb-1">Total Mahasiswa Lulus</p>
                        <h4 class="card-title mb-3">{{ $totalMahasiswaLulus }}</h4>
                        <small class="text-primary fw-medium">
                            <i class="bx bx-check-circle"></i> No Change
                        </small>
                    </div>
                </div>
            </div>


            <!-- Total Mahasiswa Lulus -->
            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">

                            <div class="dropdown">
                                {{-- <button class="btn p-0" type="button" id="cardOptGraduated"
                                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded text-muted"></i>
                                </button> --}}
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOptGraduated">
                                    <a class="dropdown-item" href="javascript:void(0);">View Details</a>
                                    <a class="dropdown-item" href="javascript:void(0);">Refresh</a>
                                </div>
                            </div>
                        </div>
                        <h5>Mahasiswa Berdasarkan Tahun Masuk</h5>
                        <canvas id="mahasiswaBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Chart Mahasiswa Berdasarkan Program Studi -->
    <div class="col-12 mt-4 col-md-4 order-2">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0">Mahasiswa Berdasarkan Program Studi</h5>
            </div>
            <div class="card-body">
                <canvas id="programStudiChart" style="max-height: 400px;"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('programStudiChart').getContext('2d');

    // Data sample (Replace with dynamic data from backend)
    const labels = @json($programStudiLabels); // ['Gizi', 'Kebidanan', 'Farmasi', ...]
    const data = @json($programStudiCounts);  // [120, 80, 60, 150, 130]
    const jurusanIds = @json($programStudiJurusanIds);  // [13211, 48201, 15401, ...]

    // Map warna berdasarkan jurusan_id
    const colors = jurusanIds.map((id, index) => {
        if (id === 13211) return '#ffc107'; // Kuning untuk jurusan_id 13211
        if (id === 48201) return '#6f42c1'; // Ungu untuk jurusan_id 48201
        if (id === 15401) return '#007bff'; // Biru untuk jurusan_id 15401
        // Default colors for the rest
        return [
            '#ffc107', // Green
            '#17a2b8', // Yellow
            '#6f42c1', // Red
            '#6f42c1', // Grey
            '#17a2b8', // Teal
        ][index % 5]; // Cycle through default colors
    });

    const chart = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderWidth: 1,
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function (context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            return `${label}: ${value}`;
                        }
                    }
                }
            }
        },
    });
});

 document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('mahasiswaBarChart').getContext('2d');

        const labels = @json($labels); // Tahun masuk dari backend
        const data = @json($values);  // Total mahasiswa dari backend

        const colors = labels.map(() => '#' + Math.floor(Math.random()*16777215).toString(16)); // Random colors for bars

        const chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Mahasiswa',
                    data: data,
                    backgroundColor: colors,
                    borderColor: colors,
                    borderWidth: 1,
                }],
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                return `${label}: ${value} Mahasiswa`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Tahun Masuk',
                        },
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Jumlah Mahasiswa',
                        },
                        beginAtZero: true,
                    },
                },
            },
        });
    });
</script>