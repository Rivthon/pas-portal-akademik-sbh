@extends('layouts.mahasiswa')
@section('title', 'Dashboard')
@section('content')
@php
    $sedangCuti = strtolower(trim((string) auth('mahasiswa')->user()->status_mhs)) === 'cuti';
    $semesterMahasiswa = (int) auth('mahasiswa')->user()->semester;
    $periodeAkademik = strtolower(trim((string) optional($ta)->semester));
    $semesterTidakSesuai = ! $sedangCuti && $semesterMahasiswa > 0 && (
        ($periodeAkademik === 'ganjil' && $semesterMahasiswa % 2 === 0) ||
        ($periodeAkademik === 'genap' && $semesterMahasiswa % 2 === 1)
    );
    $semesterYangDisarankan = $periodeAkademik === 'ganjil' ? '1, 3, 5, atau 7' : '2, 4, 6, atau 8';
@endphp
@if($sedangCuti)
    <div class="alert alert-warning border-warning shadow-sm mb-4" role="alert">
        <div class="d-flex align-items-start gap-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning bg-opacity-25 flex-shrink-0" style="width:48px;height:48px">
                <i class="bx bx-calendar-minus fs-3 text-warning"></i>
            </span>
            <div>
                <h5 class="alert-heading mb-1">Status Akademik: Cuti</h5>
                <div>Anda tetap dapat melihat riwayat akademik, administrasi, pedoman, profil, dan layanan bantuan. KRS, LMS, absensi, jadwal, ujian, serta aktivitas akademik semester berjalan dinonaktifkan sampai status Anda kembali aktif.</div>
                <a href="{{ route('mahasiswa.cuti.index') }}" class="btn btn-sm btn-warning mt-3">
                    <i class="bx bx-history me-1"></i>Lihat Riwayat Pengajuan Cuti
                </a>
            </div>
        </div>
    </div>
@endif
@if(session('cuti_notice'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-lock-alt me-1"></i>{{ session('cuti_notice') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
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
                            <p class="mb-1 d-flex align-items-center flex-wrap gap-2">
                                <strong>Semester:</strong>
                                <span class="{{ $semesterTidakSesuai ? 'badge bg-danger semester-mismatch-badge' : '' }}">
                                    {{ $semesterMahasiswa ?: '-' }}
                                </span>
                                @if($semesterTidakSesuai)
                                    <span class="badge bg-label-danger">Tidak sesuai periode {{ ucfirst($periodeAkademik) }}</span>
                                @endif
                            </p>
                            <p class="mb-1"><strong>Tanggal:</strong> {{ now()->format('l, d F Y') }}</p>
                            <p class="mb-0"><strong>Jam:</strong> <span id="current-time">{{ now()->format('H:i:s')
                                    }}</span></p>
                        </div>

                        @if($semesterTidakSesuai)
                            <div class="alert alert-danger border-danger semester-mismatch-warning" role="alert">
                                <div class="d-flex align-items-start gap-2">
                                    <i class="bx bxs-error-circle fs-3 flex-shrink-0"></i>
                                    <div class="flex-grow-1">
                                        <strong>Semester Anda tidak sesuai dengan tahun ajaran aktif!</strong>
                                        <div class="small mt-1">
                                            Saat ini periode <strong>{{ $ta->nama }} ({{ ucfirst($periodeAkademik) }})</strong>,
                                            tetapi profil Anda tercatat Semester <strong>{{ $semesterMahasiswa }}</strong>.
                                            Segera periksa dan ubah ke semester {{ $semesterYangDisarankan }} sesuai posisi akademik Anda.
                                        </div>
                                        <button type="button" class="btn btn-danger btn-sm mt-3"
                                            data-bs-toggle="modal" data-bs-target="#semesterModal">
                                            <i class="bx bx-edit-alt me-1"></i> Perbaiki Semester Sekarang
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Kalender Akademik -->
                        @if ($kalenderAkademik->isNotEmpty())
                        <div class="mt-3">
                            <h6 class="text-primary fw-bold">📅 Kalender Akademik</h6>
                            <ul class="list-unstyled">
                                @foreach ($kalenderAkademik as $kalender)
                                <li class="d-flex align-items-center mb-2">
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                    <a class="badge bg-info text-decoration-none"
                                        href="{{ route('mahasiswa.calendar-akademik.file', $kalender) }}" target="_blank">
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
            <div class="col-md-4 mb-3">
                <div class="card text-center shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <i class="bx bx-book-open fs-1 text-primary mb-2"></i>
                        <h6 class="card-title text-secondary mb-1">Total SKS</h6>
                        <p class="display-4 fw-bold text-dark mb-0">{{ $totalSks }}</p>
                        <small class="text-muted">Jumlah SKS yang telah diambil</small>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card text-center shadow-sm border-0 rounded-3">
                    @php
                    $mahasiswa = auth()->guard('mahasiswa')->user();
                    @endphp
                    <div class="card-body">
                        @php
                        $mahasiswa = auth()->guard('mahasiswa')->user();
                        @endphp

                        <i
                            class="bx bx-bar-chart fs-1 {{ $mahasiswa->status_edom == 1 ? 'text-success' : 'text-secondary' }} mb-2"></i>
                        <h6 class="card-title text-secondary mb-1">IPK</h6>

                        @if($mahasiswa->status_edom == 1)
                        <p class="display-4 fw-bold text-dark mb-0">
                            {{ number_format($ipk, 2) }}
                        </p>
                        <small class="text-muted">Indeks Prestasi Kumulatif</small>
                        @else
                        <p class="display-6 fw-semibold text-danger mb-0">
                            <i class="bx bx-lock-alt"></i> Terkunci
                        </p>
                        <small class="text-muted">Silahkan isi EDOM terlebih dahulu</small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card text-center shadow-sm border-0 rounded-3">
                    <div class="card-body">
                        <i class="bx bx-award fs-1 text-warning mb-2"></i>
                        <h6 class="card-title text-secondary mb-1">Predikat</h6>


                        @php
                        $mahasiswa = auth()->guard('mahasiswa')->user();
                        @endphp

                        <p class="fs-4 fw-semibold text-dark mb-0">

                            @if(optional($mahasiswa)->status_edom == 0)
                            <span class="text-danger">
                                Silahkan mengisi EDOM terlebih dahulu
                            </span>

                            @elseif(optional($mahasiswa)->status_edom == 1)

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

                            @else
                            <span class="text-muted">
                                Data belum tersedia
                            </span>

                            @endif

                        </p>
                        <small class="text-muted">Predikat berdasarkan IPK</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 lms-announcement-card">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center px-4 pt-4">
                <div class="d-flex align-items-center gap-2">
                    <span class="announcement-icon bg-label-primary"><i class="bx bx-bell"></i></span>
                    <div><h5 class="fw-bold mb-0">Announcement LMS</h5><small class="text-muted">Materi, tugas, dan quiz terbaru</small></div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-label-primary">{{ $lmsAnnouncements->count() }}</span>
                    <a href="{{ route('mahasiswa.lms.index') }}" class="btn btn-sm btn-label-primary">Buka LMS</a>
                    <button class="btn btn-sm btn-icon btn-label-secondary announcement-toggle" type="button"
                        data-bs-toggle="collapse" data-bs-target="#mahasiswaLmsAnnouncements"
                        aria-expanded="false" aria-controls="mahasiswaLmsAnnouncements" title="Buka atau tutup announcement">
                        <i class="bx bx-chevron-down fs-4"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="mahasiswaLmsAnnouncements">
                <div class="card-body px-4 pb-4">
                    @forelse($lmsAnnouncements as $announcement)
                    <a href="{{ $announcement['url'] }}" class="lms-announcement-item d-flex align-items-start gap-3 text-decoration-none p-3 rounded-3">
                        <span class="announcement-icon bg-label-{{ $announcement['color'] }}"><i class="bx {{ $announcement['icon'] }}"></i></span>
                        <span class="flex-grow-1 overflow-hidden">
                            <span class="d-flex flex-wrap gap-2 mb-1"><strong class="text-body">{{ $announcement['title'] }}</strong>
                                @if($announcement['event_at']?->isAfter(now()->subDays(7)))<span class="badge bg-label-danger">Baru</span>@endif
                            </span>
                            <span class="d-block text-muted small text-truncate">{{ $announcement['course'] }} &bull; {{ $announcement['detail'] }}</span>
                        </span>
                        <small class="text-muted text-nowrap">{{ $announcement['event_at']?->diffForHumans() }}</small>
                    </a>
                    @empty
                        <div class="text-center py-5"><i class="bx bx-bell-off fs-1 text-muted"></i><h6 class="mt-2 mb-1">Belum ada announcement</h6><small class="text-muted">Materi, tugas, atau quiz baru akan muncul di sini.</small></div>
                    @endforelse
                </div>
            </div>
        </div>

        <style>
            .semester-mismatch-warning {
                box-shadow: 0 0 0 rgba(255, 62, 29, 0.35);
                animation: semesterWarningPulse 1.8s ease-in-out infinite;
            }
            .semester-mismatch-badge { animation: semesterBadgePulse 1.2s ease-in-out infinite alternate; }
            @keyframes semesterWarningPulse {
                0%, 100% { box-shadow: 0 0 0 0 rgba(255, 62, 29, 0); }
                50% { box-shadow: 0 0 0 .3rem rgba(255, 62, 29, .13); }
            }
            @keyframes semesterBadgePulse {
                from { transform: scale(1); }
                to { transform: scale(1.12); }
            }
            @media (prefers-reduced-motion: reduce) {
                .semester-mismatch-warning, .semester-mismatch-badge { animation: none; }
            }
            .lms-announcement-card { border-radius: 1rem; }
            .lms-announcement-item { border: 1px solid transparent; transition: .2s ease; }
            .lms-announcement-item + .lms-announcement-item { margin-top: .35rem; }
            .lms-announcement-item:hover { background: rgba(105,108,255,.06); border-color: rgba(105,108,255,.14); transform: translateX(3px); }
            .announcement-icon { width: 42px; height: 42px; border-radius: .75rem; display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; font-size: 1.3rem; }
            .announcement-toggle .bx-chevron-down { transition: transform .2s ease; }
            .announcement-toggle[aria-expanded="true"] .bx-chevron-down { transform: rotate(180deg); }
            @media (max-width: 575.98px) { .lms-announcement-item > small { display: none; } }
        </style>


        <!-- AJAX Script untuk Memuat Berita -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    </div>
    <div id="semesterModal" class="modal fade" tabindex="-1" aria-labelledby="semesterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <!-- Modal Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="semesterModalLabel">
                        <i class="bx bx-calendar-check"></i> Konfirmasi Semester Perkuliahan
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
                                <h6 class="text-muted">Semester Anda Saat Ini:</h6>
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

                    <!-- Pengingat pemilihan semester -->
                    <div class="mb-4 text-center">
                        <img src="../assets/img/illustrations/danger-chat-ill.png"
                            class="img-fluid mx-auto d-block mb-3" alt="Illustration of a student"
                            style="max-height: 150px;">
                        <div class="alert alert-warning text-start mb-2" role="alert">
                            <div class="d-flex">
                                <i class="bx bx-info-circle fs-4 me-2 mt-1"></i>
                                <div>
                                    <strong>Pastikan semester Anda sudah benar.</strong>
                                    <div class="small mt-1">
                                        Pilihan semester membantu PAS menampilkan jadwal dan informasi akademik yang sesuai.
                                        Untuk periode <strong>{{ $ta->nama }} ({{ $ta->semester }})</strong>,
                                        pilih semester yang sedang Anda jalani saat ini.
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="text-muted small mb-0">
                            Mahasiswa pindahan atau yang mengambil mata kuliah lintas semester tetap memilih semester utama saat ini;
                            mata kuliah yang diikuti tetap mengikuti KRS.
                        </p>
                    </div>

                    <!-- Form Pilihan Semester -->
                    <form action="{{ route('mahasiswa.semester.update') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="semester" class="form-label fw-semibold">Pilih Semester</label>
                            <select name="semester" class="form-select" id="semester" required>
                                <option value="" disabled @selected(! auth('mahasiswa')->user()->semester)>-- Pilih Semester --</option>
                                @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}" @selected((int) auth('mahasiswa')->user()->semester === $i)>Semester {{ $i }}</option>
                                    @endfor
                            </select>
                            <div class="form-text">Periksa kembali sebelum menyimpan agar informasi pada dashboard tidak keliru.</div>
                        </div>
                </div>
                <!-- Modal Footer -->
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bx bx-x-circle"></i> Tutup
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-check-circle"></i> Ya, Gunakan Semester Ini
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
