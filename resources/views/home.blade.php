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
            @php
            $cards = [
            ['title' => 'Total', 'value' => $totalMahasiswa, 'icon' => 'bx-user', 'bg' => 'bg-info'],
            ['title' => 'Aktif', 'value' => $totalMahasiswaAktif, 'icon' => 'bx-user-check', 'bg' => 'bg-primary'],
            ['title' => 'Non Aktif', 'value' => $totalMahasiswaTidakAktif, 'icon' => 'bx-user-x', 'bg' =>
            'bg-danger'],
            ['title' => 'Lulus', 'value' => $totalMahasiswaLulus, 'icon' => 'bxs-graduation', 'bg' => 'bg-success'],
            ];
            @endphp

            @foreach($cards as $card)
            <div class="col-md-6 col-xl-3 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-body d-flex flex-column justify-content-center align-items-center">
                        <div class="icon mb-3">
                            <i class="bx {{ $card['icon'] }} {{ $card['bg'] }} p-3 rounded-circle text-white"></i>
                        </div>
                        <h3 class="fw-bold mb-0">{{ $card['value'] }}</h3>
                        <p class="mb-1 text-muted">{{ $card['title'] }}</p>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="col-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title">Total Mahasiswa Berdasarkan Tahun Masuk</h5>
                        <canvas id="mahasiswaBarChart" height="120"></canvas>
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