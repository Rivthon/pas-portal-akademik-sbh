@extends('layouts.dosen')
@section('title', 'Dashboard')
@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Kolom Teks Selamat Datang -->
                <div class="col-md-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Selamat Datang di Sistem Informasi Akademik, {{ auth('dosen')->user()->nama }} 🎓
                        </h5>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Semoga hari Anda menyenangkan.<br>
                            Tetap semangat dalam menjalankan tugas Anda
                        </p>
                        <div class="mb-3">
                            <p class="mb-1"><strong>Tanggal:</strong> {{ now()->format('l, d F Y') }}</p>
                            <p class="mb-0"><strong>Jam:</strong> <span id="current-time">{{ now()->format('H:i:s')
                                    }}</span></p>
                        </div>

                        <!-- Kalender Akademik -->
                        @if ($kalenderAkademik->isNotEmpty())
                        <div class="mt-3">
                            <h6 class="text-primary fw-bold">📅 Kalender Akademik</h6>
                            <ul class="list-unstyled">
                                @foreach ($kalenderAkademik as $kalender)
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                    <a class="badge bg-info text-decoration-none"
                                        href="{{ asset('storage/' . $kalender->path) }}" target="_blank">
                                        Lihat Kalender Akademik
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                        <p class="text-muted mt-3">Belum ada Kalender Akademik untuk program studi ini.</p>
                        @endif
                    </div>
                </div>
                <!-- Kolom Gambar Ilustrasi -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="../assets/img/illustrations/man-with-laptop.png" class="img-fluid"
                            alt="Illustration of a student" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik -->
        <div class="row mb-4">
            <!-- Total Kelas -->
            <div class="col-lg-4 col-md-12 col-6 mb-4">
                <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-white text-primary"><i class="bx bx-book-open fs-4"></i></span>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1 text-white">Total Kelas Diajar</span>
                        <h3 class="card-title mb-2 text-white">{{ $totalMatakuliah }}</h3>
                        <small class="text-white"><i class="bx bx-up-arrow-alt"></i> Mata Kuliah</small>
                    </div>
                </div>
            </div>
            
            <!-- Total Mahasiswa Bimbingan -->
            <div class="col-lg-4 col-md-12 col-6 mb-4">
                <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #fbc2eb 0%, #a6c1ee 100%);">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-white text-success"><i class="bx bx-group fs-4"></i></span>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1 text-white">Mahasiswa Bimbingan</span>
                        <h3 class="card-title text-white mb-2">{{ $totalMahasiswaBimbingan }}</h3>
                        <small class="text-white">Mahasiswa Aktif</small>
                    </div>
                </div>
            </div>
            
            <!-- Rata-rata EDOM -->
            <div class="col-lg-4 col-md-12 col-6 mb-4">
                <div class="card shadow-sm h-100 border-0" style="background: linear-gradient(135deg, #fdcbf1 0%, #fdcbf1 1%, #e6dee9 100%);">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-3">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-white text-warning"><i class="bx bx-star fs-4"></i></span>
                            </div>
                        </div>
                        <span class="fw-semibold d-block mb-1 text-dark">Rata-rata Nilai EDOM</span>
                        <h3 class="card-title text-dark mb-2">{{ $rataRataEdom }}</h3>
                        <small class="text-dark">Dari Skala Maksimal 100</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('dosen.nilai-dosen.input') }}" class="btn btn-primary rounded-pill shadow-sm px-4 py-2 d-flex align-items-center">
                        <i class="bx bx-edit-alt me-2"></i> Input Nilai Mahasiswa
                    </a>
                    <a href="{{ route('dosen.jadwal.index') }}" class="btn btn-info rounded-pill shadow-sm px-4 py-2 d-flex align-items-center">
                        <i class="bx bx-calendar me-2"></i> Lihat Jadwal Mengajar
                    </a>
                    <a href="{{ route('dosen.edom.hasil') }}" class="btn border-warning text-warning bg-white rounded-pill shadow-sm px-4 py-2 d-flex align-items-center mb-1 hover-warning">
                        <i class="bx bx-star me-2"></i> Hasil Evaluasi (EDOM)
                    </a>
                </div>
            </div>
            <style>
                .hover-warning:hover {
                    background-color: #ffab00 !important;
                    color: white !important;
                }
            </style>
        </div>
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <h5 class="card-title text-primary fw-bold mb-3">📰 Berita Terbaru</h5>

                <!-- Spinner Loading -->
                <div id="loading" class="text-center my-3">
                    <span class="spinner-border text-primary"></span>
                    <p class="text-muted">Memuat berita...</p>
                </div>

                <!-- Carousel Berita -->
                <div id="newsCarouselContainer" style="display: none;">
                    <div id="newsCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner" id="newsContent"></div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel"
                            data-bs-slide="prev">
                            <span class="carousel-control-prev-icon bg-dark rounded-circle p-3"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel"
                            data-bs-slide="next">
                            <span class="carousel-control-next-icon bg-dark rounded-circle p-3"
                                aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <p id="noNewsMessage" class="text-muted text-center" style="display: none;">Tidak ada berita terbaru.
                </p>
            </div>
        </div>

        <!-- AJAX Script untuk Memuat Berita -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    </div>
    <style>
        .carousel-control-prev,
        .carousel-control-next {
            width: auto;
            /* Agar tidak terlalu lebar */
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel-control-prev {
            left: -50px;
            /* Geser ke kiri */
        }

        .carousel-control-next {
            right: -50px;
            /* Geser ke kanan */
        }
    </style>
    {{-- Script untuk Jam Real-Time --}}
    <script>
        setInterval(() => {
        const currentTimeElement = document.getElementById('current-time');
        const now = new Date();
        currentTimeElement.textContent = now.toLocaleTimeString();
        }, 1000);
        window.onload = function() {
        $('#semesterModal').modal('show');
        };
         $(document).ready(function () {
            $.get("{{ route('dosen.getBeritaKampus') }}", function (data) {
                $("#loading").hide();

                if (data.length === 0) {
                    $("#noNewsMessage").show();
                    return;
                }

                let newsHtml = "";
                let chunkSize = 4; // Jumlah berita per slide

                for (let i = 0; i < data.length; i += chunkSize) {
                    let activeClass = i === 0 ? 'active' : '';
                    newsHtml += `<div class="carousel-item ${activeClass}"><div class="row g-3">`;

                    for (let j = i; j < i + chunkSize && j < data.length; j++) {
                        newsHtml += `
                            <div class="col-md-3 d-flex">
                                <div class="card shadow-sm w-100">
                                    <img src="${data[j].image}" class="card-img-top img-fluid" style="height: 180px; object-fit: cover;" alt="Thumbnail">
                                    <div class="card-body d-flex flex-column">
                                        <h6 class="card-title">
                                            <a href="${data[j].link}" target="_blank" class="text-decoration-none text-primary">
                                                ${data[j].title.substring(0, 50)}...
                                            </a>
                                        </h6>
                                        <p class="card-text text-muted mb-2">${data[j].date}</p>
                                        <a href="/dosen/dosen/berita/${data[j].id}" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>

                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    newsHtml += `</div></div>`;
                }

                $("#newsContent").html(newsHtml);
                $("#newsCarouselContainer").show();
            }).fail(function () {
                $("#loading").hide();
                $("#noNewsMessage").show();
            });
        });
    </script>


    @endsection