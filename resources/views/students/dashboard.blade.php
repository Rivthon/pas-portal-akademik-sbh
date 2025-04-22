@extends('layouts.mahasiswa')
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
                            Selamat Datang di Sistem Informasi Akademik, {{ auth('mahasiswa')->user()->nama }}! 🎓
                        </h5>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Semoga hari Anda menyenangkan.<br>
                            Tetap semangat dalam menjalankan tugas Anda!
                        </p>
                        <div class="mb-3">
                            <p class="mb-1"><strong>Semester:</strong> {{ auth('mahasiswa')->user()->semester }}</p>
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
        <div class="row">
            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Total SKS</h5>
                        <p class="card-text display-4">{{ $totalSks }}</p>
                        <small>Jumlah SKS yang telah diambil</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">IPK</h5>
                        <p class="card-text display-4">{{ number_format($ipk, 2) }}</p>
                        <small>Indeks Prestasi Kumulatif</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Predikat</h5>
                        <p class="card-text display-4">
                            @if($ipk >= 3.51)
                            Pujian (Cum Laude)
                            @elseif($ipk >= 3.01)
                            Sangat Memuaskan (Very Satisfactory)
                            @elseif($ipk >= 2.76)
                            Memuaskan (Satisfactory)
                            @elseif($ipk >= 2.00)
                            Kurang Memuaskan (Less Satisfactory)
                            @else
                            Gagal (Fail)
                            @endif
                        </p>
                        <small>Predikat berdasarkan IPK</small>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="card mt-4 mb-4">
            <div class="card-body">
                <h5 class="card-title"></h5>Informasi Penting</h5>
                <div class="row mt-4">
                    <!-- Status KRS -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Status KRS</h5>
                                @if(auth('mahasiswa')->user()->status_krs == 0)
                                <p class="text-danger fw-bold">Belum Verifikasi</p>
                                <p class="text-muted">Silakan lakukan pembayaran ke BAUK.</p>
                                @else
                                <p class="text-success fw-bold">Terverifikasi</p>
                                <p class="text-muted">KRS Anda sudah aktif.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status KHS -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Status KHS</h5>
                                @if(auth('mahasiswa')->user()->status_khs == 0)
                                <p class="text-danger fw-bold">Belum Verifikasi</p>
                                <p class="text-muted">Silakan lakukan pembayaran ke BAUK.</p>
                                @else
                                <p class="text-success fw-bold">Terverifikasi</p>
                                <p class="text-muted">KHS Anda sudah aktif.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Nilai UTS -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Status Nilai UTS</h5>
                                @if(auth('mahasiswa')->user()->status_nilai_uts == 0)
                                <p class="text-danger fw-bold">Belum Verifikasi</p>
                                <p class="text-muted">Silakan lakukan pembayaran ke BAUK.</p>
                                @else
                                <p class="text-success fw-bold">Terverifikasi</p>
                                <p class="text-muted">Nilai UTS Anda sudah tersedia.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Status Nilai UAS -->
                    <div class="col-md-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Status Nilai UAS</h5>
                                @if(auth('mahasiswa')->user()->status_nilai_uas == 0)
                                <p class="text-danger fw-bold">Belum Verifikasi</p>
                                <p class="text-muted">Silakan lakukan pembayaran ke BAUK.</p>
                                @else
                                <p class="text-success fw-bold">Terverifikasi</p>
                                <p class="text-muted">Nilai UAS Anda sudah tersedia.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div> --}}
        <div class="card shadow-sm mb-4 mt-4">
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
    <div id="semesterModal" class="modal fade" tabindex="-1" aria-labelledby="semesterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="semesterModalLabel">
                        <i class="bx bx-calendar"></i> Pilih Semester Aktif
                    </h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body">
                    <!-- Menampilkan Semester yang Sedang Aktif -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted">Semester Aktif Saat Ini:</h6>
                                <strong class="fs-5 text-primary">Semester - {{ auth('mahasiswa')->user()->semester
                                    }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted">Tahun Ajaran Aktif Saat Ini:</h6>
                                <strong class="fs-5 text-primary">{{ $ta->nama }} ({{ $ta->semester }})</strong>
                            </div>
                        </div>
                    </div>


                    <!-- Divider -->
                    <hr class="my-3">

                    <!-- Notifikasi atau Icon dengan Keterangan -->
                    <div class="mb-4 text-center">
                        <img src="../assets/img/illustrations/danger-chat-ill.png"
                            class="img-fluid mx-auto d-block mb-3" alt="Illustration of a student"
                            style="max-height: 150px;">
                        <p class="text-muted small">
                            Pastikan memilih semester dengan benar sesuai dengan jadwal perkuliahan Anda.
                        </p>
                    </div>

                    <!-- Form Pilihan Semester -->
                    <form action="{{ route('mahasiswa.semester.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="semester" class="form-label fw-semibold">Pilih Semester</label>
                            <select name="semester" class="form-select" id="semester">
                                <option disabled selected>-- Pilih Semester --</option>
                                @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                                    @endfor
                            </select>
                        </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x-circle"></i> Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-save"></i> Simpan
                    </button>
                </div>
                </form>
            </div>
        </div>
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
            $.get("{{ route('mahasiswa.getBeritaKampus') }}", function (data) {
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
                                    <a href="/mahasiswa/berita/${data[j].id}" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>
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