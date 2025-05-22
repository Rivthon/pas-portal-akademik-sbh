@extends('layouts.master')
@section('title', 'Validator SKPI')
@section('content')
<div class="flex-grow-1 container-p-y ">
    <!-- Card Section -->
    <div class="card shadow-sm mb-4">
        <div class="d-flex align-items-center item g-0">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="card-title text-primary mb-3 fw-bold">
                        Penguasaan Bahasa Asing
                    </h5>
                    <nav aria-label="breadcrumb" class="mb-4" style="font-size: 0.85rem;">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.home') }}" class="breadcrumb-link">
                                    <i class="bx bx-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.skpi.index') }}" class="breadcrumb-link">
                                    Validator SKPI
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <span class="breadcrumb-active"
                                    style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                                    Penguasaan Bahasa Asing
                                </span>
                            </li>

                        </ol>
                    </nav>
                    <p class="text-muted mb-4" style="line-height: 1.6;">

                        <strong>Penguasaan Bahasa Asing</strong> adalah kemampuan yang dimiliki oleh mahasiswa dalam
                        menggunakan bahasa asing, baik lisan maupun tulisan. Hal ini mencakup kemampuan berbicara,
                        mendengarkan, membaca, dan menulis dalam bahasa asing. Penguasaan bahasa asing sangat penting
                        dalam dunia kerja dan pendidikan, karena dapat meningkatkan daya saing dan kemampuan
                        komunikasi mahasiswa di tingkat internasional.
                        <br>

                        {{--
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Apabila tidak terkirim, cek kembali nomor telepon WA yang terdaftar di sistem. Hubungi
                        ICT untuk info lebih lanjut.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div> --}}
                    </p>
                    <!-- CTA Button -->
                    <div class="mb-3">
                        {{-- <a href="{{ route('mahasiswa.skpi.cetak') }}" class="btn btn-primary">Cetak Rekap
                            Penilaian</a> --}}
                    </div>
                </div>
            </div>
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="100%" height="180" viewBox="0 0 64 64" fill="none">
                        <rect width="64" height="64" rx="8" fill="#F5F8FF" />
                        <path
                            d="M32 12C35.3137 12 38 14.6863 38 18C38 21.3137 35.3137 24 32 24C28.6863 24 26 21.3137 26 18C26 14.6863 28.6863 12 32 12Z"
                            fill="#5B7CFA" />
                        <path
                            d="M18 34C18 30.6863 20.6863 28 24 28H40C43.3137 28 46 30.6863 46 34V42C46 43.1046 45.1046 44 44 44H20C18.8954 44 18 43.1046 18 42V34Z"
                            fill="#AFC4FF" />
                        <path d="M32 46C35.866 46 39 49.134 39 53H25C25 49.134 28.134 46 32 46Z" fill="#5B7CFA" />
                        <path d="M24 19H40" stroke="#1E3A8A" stroke-width="2" stroke-linecap="round" />
                        <path d="M26 22H38" stroke="#1E3A8A" stroke-width="2" stroke-linecap="round" />
                        <circle cx="50" cy="14" r="4" fill="#34D399" />
                        <path d="M52 13L50.5 15L48 13" stroke="white" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @php
        $statuses = [
        [
        'title' => 'Disetujui',
        'route' => route('admin.skpi.bahasa.disetujui'),
        'color' => 'success',
        'svg' => '
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <rect width="64" height="64" rx="8" fill="#E6F9F0" />
            <circle cx="32" cy="32" r="24" fill="#34D399" />
            <path d="M22 34L30 42L44 26" stroke="white" stroke-width="4" stroke-linecap="round"
                stroke-linejoin="round" />
        </svg>
        ',
        'count_key' => 'count_disetujui',
        ],
        [
        'title' => 'Ditolak',
        'route' => route('admin.skpi.bahasa.ditolak'),
        'color' => 'danger',
        'svg' => '
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <rect width="64" height="64" rx="8" fill="#FDE8E8" />
            <circle cx="32" cy="32" r="24" fill="#F87171" />
            <path d="M24 24L40 40" stroke="white" stroke-width="4" stroke-linecap="round" />
            <path d="M40 24L24 40" stroke="white" stroke-width="4" stroke-linecap="round" />
        </svg>
        ',
        'count_key' => 'count_ditolak',
        ],
        [
        'title' => 'Ditinjau',
        'route' => route('admin.skpi.bahasa.ditinjau'),
        'color' => 'warning text-dark',
        'svg' => '
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <rect width="64" height="64" rx="8" fill="#FFF9E6" />
            <circle cx="32" cy="32" r="24" fill="#FBBF24" />
            <path d="M32 20V36" stroke="white" stroke-width="4" stroke-linecap="round" />
            <circle cx="32" cy="44" r="2" fill="white" />
        </svg>
        ',
        'count_key' => 'count_ditinjau',
        ],
        [
        'title' => 'Menunggu',
        'route' => route('admin.skpi.bahasa.menunggu'),
        'color' => 'secondary',
        'svg' => '
        <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
            <rect width="64" height="64" rx="8" fill="#F3F4F6" />
            <circle cx="32" cy="32" r="24" fill="#A1A1AA" />
            <path d="M32 20V34L40 38" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        ',
        'count_key' => 'count_menunggu',
        ],
        ];
        @endphp

        <style>
            .transition-transform {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }

            .hover-translate:hover {
                transform: translateY(-5px);
            }

            .hover-shadow-lg:hover {
                box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15) !important;
            }

            .card-hover:hover {
                border: 2px solid #6366F1;
                background-color: #f8f9ff;
            }
        </style>

        @foreach ($statuses as $status)
        <div class="col-md-3 col-sm-6 mb-4">
            <a href="{{ $status['route'] }}" class="text-decoration-none">
                <div
                    class="card card-hover h-100 text-center shadow-sm transition-transform hover-translate hover-shadow-lg">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        {!! $status['svg'] !!}
                        <h6 class="mb-0">{{ $status['title'] }}</h6>
                        <span class="badge bg-{{ $status['color'] }} mt-2">
                            Total:
                            <span class="badge bg-light text-{{ explode(' ', $status['color'])[0] }} ms-1">
                                {{ data_get(get_defined_vars(), $status['count_key'], 0) }}
                            </span>
                        </span>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection