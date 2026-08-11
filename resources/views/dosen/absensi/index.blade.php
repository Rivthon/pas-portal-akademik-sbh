@extends('layouts.dosen')
@section('title', 'Buat Absensi Mahasiswa')
@section('content')
    <div class="row">
        <div class="col-xxl-12 mt-auto mb-auto order-0">
            {{-- Hero Profile Card --}}
            <div class="card shadow-sm mb-4 border-0"
                style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <div class="card-body position-relative overflow-hidden">
                    <div class="row g-0 align-items-center">
                        <!-- Content Section -->
                        <div class="col-md-7 text-white p-3 z-2">
                            <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-check-shield me-2"></i>Buat
                                Pertemuan Absensi</h4>
                            <p class="mb-0 text-white-50" style="line-height: 1.6;">
                                Silakan temukan mata kuliah yang Anda ampu, kemudian tekan untuk mulai <br>
                                mendaftarkan topik, sub topik, dan jadwal pertemuan baru sebelum melakukan presensi.
                            </p>
                        </div>

                        <!-- Image Section -->
                        <div class="col-md-5 text-center d-none d-md-block z-2">
                            <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                                alt="Illustration for attendance" style="max-height: 150px; opacity:0.9;">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stats Cards --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="transition: transform 0.2s;">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block mb-1 fw-semibold">Total Mata Kuliah</small>
                                <h3 class="mb-0 fw-bold text-primary" id="stat-total">0</h3>
                            </div>
                            <i class="bx bx-book-open fs-1 text-primary opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="transition: transform 0.2s;">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block mb-1 fw-semibold">Kelas Reguler</small>
                                <h3 class="mb-0 fw-bold text-success" id="stat-reguler">0</h3>
                            </div>
                            <i class="bx bx-sun fs-1 text-success opacity-50"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="transition: transform 0.2s;">
                        <div class="card-body d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted d-block mb-1 fw-semibold">Kelas Karyawan</small>
                                <h3 class="mb-0 fw-bold text-warning" id="stat-karyawan">0</h3>
                            </div>
                            <i class="bx bx-moon fs-1 text-warning opacity-50"></i>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Filter Card --}}
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-white pt-4 pb-3 border-bottom">
                    <form id="filter-form">
                        <div class="row align-items-center gx-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="input-group input-group-merge shadow-sm rounded-pill border">
                                    <span class="input-group-text bg-white border-0 rounded-pill-start"><i
                                            class="bx bx-search"></i></span>
                                    <input type="text" id="filterNamaJadwal" name="search"
                                        class="form-control border-0 rounded-pill-end ps-0"
                                        placeholder="Cari Mata Kuliah atau Nama Dosen..." aria-label="Search...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <select id="filterJenisKelas" name="jenis_kelas"
                                    class="form-select shadow-sm rounded-pill cursor-pointer border">
                                    <option value="semua">-- Tampilkan Semua Jenis Kelas --</option>
                                    <option value="reguler">Kelas Reguler (Pagi/Siang)</option>
                                    <option value="karyawan">Kelas Karyawan (Malam/Eksekutif)</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body bg-light mt-0 pt-4 position-relative" style="min-height: 300px;">
                    <div id="loading-spinner"
                        class="position-absolute w-100 h-100 top-0 start-0 justify-content-center align-items-center"
                        style="background: rgba(255,255,255,0.7); z-index: 10; display: none;">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>

                    <div id="absensiContainer" style="transition: opacity 0.3s ease;">
                        <div class="text-center py-5">
                            <div class="spinner-border text-primary mb-3"></div>
                            <p class="text-muted">Memuat data jadwal...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(function () {
                const container = $('#absensiContainer');
                const loading = $('#loading-spinner');
                const form = $('#filter-form');
                let searchTimeout;

                function animate(id, val) {
                    $('#' + id).text(val);
                }

                function loadData() {
                    loading.css('display', 'flex');
                    container.css('opacity', 0.5);

                    $.get("{{ route('dosen.absensi.filter') }}", form.serialize(), function (res) {
                        container.html(res.html);

                        animate('stat-total', res.statistik.total);
                        animate('stat-reguler', res.statistik.reguler);
                        animate('stat-karyawan', res.statistik.karyawan);

                    }).fail(function () {
                        container.html(`<div class="alert alert-danger text-center m-4"><i class="bx bx-error-circle fs-3 mb-2 d-block"></i>Gagal memuat data absensi. Silakan coba lagi.</div>`);
                    }).always(function () {
                        loading.hide();
                        container.css('opacity', 1);
                    });
                }

                // Debounce search input
                $('#filterNamaJadwal').on('input', function () {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(function () {
                        loadData();
                    }, 500); // 500ms delay prevents excessive requests
                });

                $('#filterJenisKelas').on('change', function () {
                    loadData();
                });

                // Initial load
                loadData();
            });
        </script>
    @endpush
@endsection
