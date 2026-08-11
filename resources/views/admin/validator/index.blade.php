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
                        Validator SKPI
                    </h5>
                    <p class="text-muted mb-4" style="line-height: 1.6;">

                        Surat Keterangan Pedamping Ijazah (SKPI) adalah dokumen yang
                        menyatakan bahwa mahasiswa telah menyelesaikan pendidikan di sebuah perguruan tinggi
                        dan memiliki kompetensi yang diakui oleh lembaga tersebut. SKPI biasanya mencakup informasi
                        tentang program studi yang diambil, nilai yang diperoleh, serta kegiatan ekstrakurikuler atau
                        prestasi yang diraih selama masa studi. SKPI juga dapat berfungsi sebagai bukti bahwa
                        mahasiswa telah mengikuti pendidikan yang sesuai dengan standar yang ditetapkan oleh
                        perguruan tinggi dan dapat digunakan sebagai referensi untuk melamar pekerjaan atau
                        melanjutkan pendidikan ke jenjang yang lebih tinggi.
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
        $menus = [
        ['title' => 'Sertifikasi / Kompetensi', 'route' => route('admin.skpi.sertifikasi'), 'permission' => 'skpi-sertifikasi-list'],
        ['title' => 'Penguasaan bahasa asing', 'route' => route('admin.skpi.bahasa'), 'permission' => 'skpi-bahasa-list'],
        ['title' => 'Program Pembinaan Mahasiswa Wirausaha', 'route' => route('admin.skpi.wirausaha'), 'permission' => 'skpi-wirausaha-list'],
        ['title' => 'Program Kreativitas Mahasiswa', 'route' => route('admin.skpi.pkm'), 'permission' => 'skpi-pkm-list'],
        ['title' => 'PPSM', 'route' => route('admin.skpi.ppsm'), 'permission' => 'skpi-ppsm-list'],
        ['title' => 'Kegiatan Tambahan', 'route' => route('admin.skpi.tambahan'), 'permission' => 'skpi-tambahan-list'],
        ];
        $icons = [
        'Sertifikasi / Kompetensi' => 'cert',
        'Penguasaan bahasa asing' => 'globe',
        'Program Pembinaan Mahasiswa Wirausaha' => 'briefcase',
        'Program Kreativitas Mahasiswa' => 'bulb',
        'PPSM' => 'group',
        'Kegiatan Tambahan' => 'plus',
        ];
        @endphp

        @foreach ($menus as $menu)
        @continue(!auth()->user()->can($menu['permission']))
        @php
        $icon = $icons[$menu['title']] ?? 'file';
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
        <div class="col-md-4 col-sm-6 mb-4">
            <a href="{{ $menu['route'] }}" class="text-decoration-none">
                <div
                    class="card card-hover h-100 text-center shadow-sm transition-transform hover-translate hover-shadow-lg">
                    <div class="card-body d-flex flex-column align-items-center justify-content-center">
                        @if($icon == 'cert')
                        <!-- Sertifikasi / Kompetensi SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="16" y="12" width="64" height="72" rx="6" stroke="#377DFF" stroke-width="4"
                                fill="white" />
                            <circle cx="48" cy="40" r="16" fill="#FFC542" stroke="#377DFF" stroke-width="4" />
                            <path d="M48 31.2l3.8 7.3 8.1 1.2-5.9 5.7 1.4 8.1-7.4-3.9-7.4 3.9 1.4-8.1-5.9-5.7 8.1-1.2z"
                                fill="white" />
                        </svg>
                        @elseif($icon == 'globe')
                        <!-- Penguasaan bahasa asing SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="48" cy="48" r="28" stroke="#00B894" stroke-width="4" />
                            <path d="M20 48h56M48 20a28 28 0 010 56M37 20a28 28 0 000 56" stroke="#00B894"
                                stroke-width="4" stroke-linecap="round" />
                            <rect x="58" y="60" width="18" height="12" rx="3" fill="#00B894" />
                            <path d="M66 72l3 6 3-6" fill="#00B894" />
                        </svg>
                        @elseif($icon == 'briefcase')
                        <!-- Program Pembinaan Mahasiswa Wirausaha SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M48 16a18 18 0 0118 18c0 6.9-3.9 12.9-9.6 16H39.6C33.9 46.9 30 40.9 30 34a18 18 0 0118-18z"
                                stroke="#FF6B6B" stroke-width="4" fill="white" />
                            <rect x="40" y="37" width="4" height="9" fill="#FF6B6B" />
                            <rect x="48" y="33" width="4" height="13" fill="#FFD93D" />
                            <rect x="56" y="29" width="4" height="17" fill="#FF6B6B" />
                            <rect x="40" y="58" width="16" height="6" rx="2" fill="#FF6B6B" />
                            <rect x="38" y="64" width="20" height="6" rx="3" fill="#FF6B6B" />
                        </svg>
                        @elseif($icon == 'bulb')
                        <!-- Program Kreativitas Mahasiswa SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                d="M36 30a12 12 0 1124 0h2a10 10 0 110 20h-2v4h2a8 8 0 110 16H36a8 8 0 110-16h2v-4h-2a10 10 0 110-20h2z"
                                stroke="#A55EEA" stroke-width="4" fill="white" />
                            <circle cx="32" cy="44" r="6" stroke="#4ECDC4" stroke-width="3" />
                            <path d="M32 38v12M26 44h12" stroke="#4ECDC4" stroke-width="3" stroke-linecap="round" />
                            <circle cx="64" cy="60" r="6" stroke="#4ECDC4" stroke-width="3" />
                            <path d="M64 54v12M58 60h12" stroke="#4ECDC4" stroke-width="3" stroke-linecap="round" />
                        </svg>
                        @elseif($icon == 'group')
                        <!-- PPSM SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M48 18l20 8v18c0 20-20 28-20 28s-20-8-20-28V26l20-8z" stroke="#F57F17"
                                stroke-width="4" fill="white" />
                            <circle cx="38" cy="44" r="6" fill="#F57F17" />
                            <path d="M32 56c0-6 4-10 6-10s6 4 6 10v6H32v-6z" fill="#F57F17" />
                            <circle cx="58" cy="44" r="6" fill="#F57F17" />
                            <path d="M52 56c0-6 4-10 6-10s6 4 6 10v6H52v-6z" fill="#F57F17" />
                        </svg>
                        @elseif($icon == 'plus')
                        <!-- Kegiatan Tambahan SVG -->
                        <svg width="96" height="96" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="44" y="24" width="8" height="48" rx="2" fill="#6366F1" />
                            <rect x="24" y="44" width="48" height="8" rx="2" fill="#6366F1" />
                            <circle cx="48" cy="16" r="4" fill="#10B981" />
                            <circle cx="48" cy="80" r="4" fill="#10B981" />
                            <circle cx="16" cy="48" r="4" fill="#10B981" />
                            <circle cx="80" cy="48" r="4" fill="#10B981" />
                            <circle cx="24" cy="24" r="4" fill="#10B981" />
                            <circle cx="72" cy="24" r="4" fill="#10B981" />
                            <circle cx="24" cy="72" r="4" fill="#10B981" />
                            <circle cx="72" cy="72" r="4" fill="#10B981" />
                        </svg>
                        @else
                        <!-- Default SVG -->
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="2" class="mb-3"
                            viewBox="0 0 24 24">
                            <rect x="4" y="4" width="16" height="16" rx="2" />
                        </svg>
                        @endif
                        <h6 class="mb-0">{{ $menu['title'] }}</h6>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
</div>
@endsection
