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
                                        href="{{ route('dosen.calendar-akademik.file', $kalender) }}" target="_blank">
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

        @if($teachingReminders->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4 teaching-reminder-card">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center gap-2 px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="announcement-icon announcement-icon-sm bg-label-warning"><i class="bx bx-time-five"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Pengingat Pengajaran Minggu Ini</h6>
                        <small class="text-muted">{{ $teachingReminders->count() }} jadwal belum memiliki pertemuan</small>
                    </div>
                </div>
                <button class="btn btn-sm btn-icon btn-label-warning announcement-toggle" type="button"
                    data-bs-toggle="collapse" data-bs-target="#dosenTeachingReminders"
                    aria-expanded="false" aria-controls="dosenTeachingReminders" title="Buka atau tutup pengingat">
                    <i class="bx bx-chevron-down fs-4"></i>
                </button>
            </div>
            <div class="collapse" id="dosenTeachingReminders">
                <div class="card-body px-3 pt-0 pb-3">
                    <div class="alert alert-warning py-2 mb-2">
                        <i class="bx bx-info-circle me-1"></i>
                        Pengingat ini hanya informasi dan tidak mengubah jadwal, absensi, atau BAP.
                    </div>
                    @foreach($teachingReminders as $reminder)
                        <a href="{{ $reminder['url'] }}"
                            class="teaching-reminder-item d-flex align-items-center gap-2 text-decoration-none p-2 rounded-3">
                            <span class="announcement-icon announcement-icon-sm {{ $reminder['type'] === 'praktik' ? 'bg-label-success' : 'bg-label-primary' }}">
                                <i class="bx {{ $reminder['type'] === 'praktik' ? 'bx-test-tube' : 'bx-book-open' }}"></i>
                            </span>
                            <span class="flex-grow-1 overflow-hidden">
                                <span class="d-flex flex-wrap align-items-center gap-1">
                                    <strong class="text-body">Anda belum mengajar {{ $reminder['course'] }} minggu ini</strong>
                                    <span class="badge {{ $reminder['type'] === 'praktik' ? 'bg-label-success' : 'bg-label-primary' }}">
                                        {{ ucfirst($reminder['type']) }}
                                    </span>
                                </span>
                                <span class="d-block text-muted small">
                                    {{ $reminder['prodi'] }} &bull; Semester {{ $reminder['semester'] ?? '-' }}
                                    &bull; {{ $reminder['class'] }} &bull; {{ $reminder['day'] }}, {{ $reminder['time'] }}
                                </span>
                            </span>
                            <i class="bx bx-chevron-right text-muted"></i>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @if($rpsReplacementNotifications->isNotEmpty())
        <div class="card border-0 shadow-sm mb-4 rps-notification-card">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center gap-2 px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="announcement-icon announcement-icon-sm bg-label-info"><i class="bx bx-file"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Pembaruan RPS oleh Rekan Dosen</h6>
                        <small class="text-muted">{{ $rpsReplacementNotifications->count() }} pembaruan terbaru</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('dosen.rps.index') }}" class="btn btn-sm btn-label-info">Buka RPS</a>
                    <button class="btn btn-sm btn-icon btn-label-secondary announcement-toggle" type="button"
                        data-bs-toggle="collapse" data-bs-target="#dosenRpsNotifications"
                        aria-expanded="false" aria-controls="dosenRpsNotifications" title="Buka atau tutup pemberitahuan RPS">
                        <i class="bx bx-chevron-down fs-4"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="dosenRpsNotifications">
                <div class="card-body px-3 pt-0 pb-3">
                    @foreach($rpsReplacementNotifications as $notification)
                        <a href="{{ route('dosen.rps.index') }}" class="rps-notification-item d-flex align-items-center gap-2 text-decoration-none p-2 rounded-3">
                            <span class="announcement-icon announcement-icon-sm bg-label-info"><i class="bx bx-refresh"></i></span>
                            <span class="flex-grow-1 overflow-hidden">
                                <strong class="text-body d-block">
                                    RPS {{ $notification->kurikulum?->mataKuliah?->nama ?? 'mata kuliah' }} telah diganti oleh {{ $notification->uploader?->nama ?? 'rekan dosen' }}
                                </strong>
                                <span class="d-block text-muted small">
                                    {{ $notification->kurikulum?->programStudi?->nama ?? '-' }} &bull;
                                    {{ jenis_kelas_label($notification->jenis_kelas) }}
                                </span>
                            </span>
                            <small class="text-muted text-nowrap">{{ $notification->created_at?->diffForHumans() }}</small>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        @php
            $ringkasLmsAnnouncements = $lmsAnnouncements->take(4);
            $lmsPendingCount = $lmsAnnouncements->where('pending', true)->count();
            $sisaLmsAnnouncements = max(0, $lmsAnnouncements->count() - $ringkasLmsAnnouncements->count());
        @endphp
        <div class="card border-0 shadow-sm mb-4 lms-announcement-card">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center gap-2 px-3 py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="announcement-icon announcement-icon-sm bg-label-primary"><i class="bx bx-bell"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Aktivitas LMS</h6>
                        <small class="text-muted">
                            {{ $lmsAnnouncements->count() }} terbaru
                            @if($lmsPendingCount > 0)
                                &middot; {{ $lmsPendingCount }} perlu dinilai
                            @endif
                        </small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($lmsPendingCount > 0)
                        <span class="badge bg-label-warning d-none d-sm-inline-flex">{{ $lmsPendingCount }} pending</span>
                    @endif
                    <a href="{{ route('dosen.lms.index') }}" class="btn btn-sm btn-label-primary">
                        <i class="bx bx-book-open"></i><span class="d-none d-sm-inline ms-1">Buka LMS</span>
                    </a>
                    <button class="btn btn-sm btn-icon btn-label-secondary announcement-toggle" type="button"
                        data-bs-toggle="collapse" data-bs-target="#dosenLmsAnnouncements"
                        aria-expanded="false" aria-controls="dosenLmsAnnouncements" title="Buka atau tutup aktivitas LMS">
                        <i class="bx bx-chevron-down fs-4"></i>
                    </button>
                </div>
            </div>
            <div class="collapse" id="dosenLmsAnnouncements">
                <div class="card-body px-3 pt-0 pb-3">
                    @forelse($ringkasLmsAnnouncements as $announcement)
                    <a href="{{ $announcement['url'] }}" class="lms-announcement-item d-flex align-items-center gap-2 text-decoration-none p-2 rounded-3">
                        <span class="announcement-icon announcement-icon-sm bg-label-{{ $announcement['color'] }}"><i class="bx {{ $announcement['icon'] }}"></i></span>
                        <span class="flex-grow-1 overflow-hidden">
                            <span class="d-flex align-items-center gap-2">
                                <strong class="text-body text-truncate">{{ $announcement['title'] }}</strong>
                                @if($announcement['pending'])
                                    <span class="badge bg-label-warning flex-shrink-0">Perlu Dinilai</span>
                                @endif
                            </span>
                            <span class="d-block text-muted small text-truncate">{{ $announcement['course'] }} &bull; {{ $announcement['detail'] }}</span>
                        </span>
                        <small class="text-muted text-nowrap">{{ $announcement['event_at']?->diffForHumans() }}</small>
                    </a>
                    @empty
                        <div class="text-center py-3"><i class="bx bx-bell-off fs-3 text-muted"></i><h6 class="mt-2 mb-1">Belum ada aktivitas LMS</h6><small class="text-muted">Pengumpulan tugas dan quiz mahasiswa akan muncul di sini.</small></div>
                    @endforelse
                    @if($sisaLmsAnnouncements > 0)
                        <div class="text-center border-top mt-2 pt-2">
                            <a href="{{ route('dosen.lms.index') }}" class="small fw-semibold">
                                Lihat {{ $sisaLmsAnnouncements }} aktivitas lainnya di LMS
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <style>
            .lms-announcement-card { border-radius: 1rem; }
            .lms-announcement-item { border: 1px solid transparent; transition: .2s ease; }
            .lms-announcement-item + .lms-announcement-item { margin-top: .35rem; }
            .lms-announcement-item:hover { background: rgba(105,108,255,.06); border-color: rgba(105,108,255,.14); transform: translateX(3px); }
            .teaching-reminder-card { border-left: 4px solid #ffab00 !important; border-radius: 1rem; }
            .teaching-reminder-item { border: 1px solid rgba(255,171,0,.16); transition: .2s ease; }
            .teaching-reminder-item + .teaching-reminder-item { margin-top: .4rem; }
            .teaching-reminder-item:hover { background: rgba(255,171,0,.06); border-color: rgba(255,171,0,.28); }
            .rps-notification-card { border-left: 4px solid #03c3ec !important; border-radius: 1rem; }
            .rps-notification-item { border: 1px solid rgba(3,195,236,.16); transition: .2s ease; }
            .rps-notification-item + .rps-notification-item { margin-top: .4rem; }
            .rps-notification-item:hover { background: rgba(3,195,236,.06); border-color: rgba(3,195,236,.28); }
            .announcement-icon { width: 42px; height: 42px; border-radius: .75rem; display: inline-flex; flex-shrink: 0; align-items: center; justify-content: center; font-size: 1.3rem; }
            .announcement-icon-sm { width: 34px; height: 34px; border-radius: .65rem; font-size: 1.05rem; }
            .announcement-toggle .bx-chevron-down { transition: transform .2s ease; }
            .announcement-toggle[aria-expanded="true"] .bx-chevron-down { transform: rotate(180deg); }
            @media (max-width: 575.98px) { .lms-announcement-item > small { display: none; } }
        </style>

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
                                        <a href="/dosen/berita/${data[j].id}" class="btn btn-outline-primary btn-sm">Baca Selengkapnya</a>

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
