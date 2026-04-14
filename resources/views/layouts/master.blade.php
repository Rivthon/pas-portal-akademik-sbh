<!DOCTYPE html>

<html lang="en" class="light-style layout-menu-fixed layout-compact" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('dashboard_assets/assets/') }}" data-template="vertical-menu-template-free">

<head>
    <meta charset="utf-8" />
    @php
    $settings = \App\Models\Setting::first();
    @endphp
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->name }} - @yield('title')</title>

    <meta name="description" content="{{ $settings->name }}">

    <!-- Google / Search Engine Tags -->
    <meta itemprop="name" content="{{ $settings->name }}">
    <meta itemprop="description" content="{{ $settings->name }}">

    <!-- Facebook Meta Tags -->
    <meta property="og:url" content="{{ $settings->website_url }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $settings->name }}">
    <meta property="og:description" content="{{ $settings->name }}">

    <!-- Twitter Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $settings->name }}">
    <meta name="twitter:description" content="{{ $settings->name }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Favicon -->
    <link rel="icon"
        href="{{ $settings->favicon ? asset('storage/' . $settings->favicon) : asset('default/favicon.ico') }}"
        type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/fonts/boxicons.css') }}" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/css/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/demo.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/custom.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/css/pages/page-profile.css') }}">

    <!-- Vendors CSS -->
    <link rel="stylesheet"
        href="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apex-charts.css') }}" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    {{-- Datatables CSS --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    {{-- Select2 CSS --}}
    <link rel="stylesheet" href="{{ asset('dashboard_assets/assets/vendor/libs/select2/css/select2.min.css') }}">

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('dashboard_assets/assets/vendor/js/helpers.js') }}"></script>
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('dashboard_assets/assets/js/config.js') }}"></script>


    @stack('head')
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">

        <div class="layout-container">
            <!-- Menu -->
            <div class="pre-loader is-load" id="preloader">
                <div class="circle-loader"></div>
            </div>
            @include('components.sidebar')
            <!-- / Menu -->
            @include('sweetalert::alert')
            @include('components.notification')
            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('components.navbar')
                <!-- / Navbar -->
                <div id="toast-overlay"></div>

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->

                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    @include('components.footer')
                    <!-- / Footer -->

                    <div class="content-backdrop fade"></div>
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>

        <!-- Overlay -->
        <div class="layout-overlay layout-menu-toggle"></div>
    </div>
    <!-- / Layout wrapper -->

    <!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src="{{ asset('dashboard_assets/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/js/menu.js') }}"></script>

    <!-- endbuild -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Vendors JS -->
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/chartjs.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/chartjs/charts-chartjs.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    {{-- Select2 JS --}}
    <script src="{{ asset('dashboard_assets/assets/vendor/libs/select2/js/select2.full.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- Main JS -->
    <script src="{{ asset('dashboard_assets/assets/js/main.js') }}"></script>
    <script src="{{ asset('dashboard_assets/assets/js/custom.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('dashboard_assets/assets/js/dashboards-analytics.js') }}"></script>
    @include('sweetalert::alert', ['cdn' => "https://cdn.jsdelivr.net/npm/sweetalert2@9"])

    <!-- Place this tag in your head or just before your close body tag. -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- @include('components.notification') --}}
    <script>
        function formatJam(jam) {
            if (!jam) return '';
            const [h, m] = jam.split(':');
            return `${h}:${m} WIB`;
        }

        $('#jam_mulai').on('input', function () {
            $('#keterangan_jam_mulai').text(formatJam(this.value));
        }).trigger('input');

        $('#jam_selesai').on('input', function () {
            $('#keterangan_jam_selesai').text(formatJam(this.value));
        }).trigger('input');

    </script>
    <script>
        $(document).ready(function () {
            $('#dosen_id_mhs').select2({
                placeholder: "-- Cari & Pilih Dosen --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script>
        $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2();
    });
    //card matakuliah

        window.addEventListener('load', function() {
            document.getElementById('preloader').style.display = 'none';
        });
    </script>
    <script>
        $(document).ready(function () {
    /**
     * Fungsi generik untuk menangani pencarian dinamis
     * @param {string} formSelector - Selector untuk form pencarian
     * @param {string} listSelector - Selector untuk elemen target yang akan diperbarui
     * @param {string} urlRoute - Route untuk request AJAX
     */
    function handleSearch(formSelector, listSelector, urlRoute) {
        $(document).on('submit', formSelector, function (event) {
            event.preventDefault(); // Mencegah form melakukan submit biasa
            let searchQuery = $(formSelector).find('input[name="search"]').val(); // Ambil nilai input pencarian

            // Kirim request AJAX
            $.ajax({
                url: urlRoute, // Route yang dinamis
                method: "GET",
                data: { search: searchQuery }, // Kirim parameter pencarian
                beforeSend: function () {
                    // Tampilkan loading indicator sebelum data diterima
                    $(listSelector).html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');
                },
                success: function (response) {
                    // Update isi elemen target dengan response
                    $(listSelector).html(response.html);
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat memuat data.');
                }
            });
        });
    }

    /**
     * Fungsi generik untuk pagination dinamis
     * @param {string} listSelector - Selector untuk elemen target
     */
    function handlePagination(listSelector) {
        $(document).on('click', '.pagination-links a', function (event) {
            event.preventDefault(); // Mencegah navigasi default
            let url = $(this).attr('href'); // URL dari pagination link

            // Kirim request AJAX ke URL
            $.ajax({
                url: url,
                method: "GET",
                beforeSend: function () {
                    // Tampilkan loading indicator
                    $(listSelector).html('<div class="text-center"><div class="spinner-border text-primary"></div></div>');
                },
                success: function (response) {
                    // Perbarui konten elemen target
                    $(listSelector).html(response.html);
                },
                error: function (xhr) {
                    alert('Terjadi kesalahan saat memuat data.');
                }
            });
        });
    }

    const searchConfigs = [
        { form: '#search-mahasiswa', list: '#mahasiswa-list', route: "{{ route('admin.mahasiswa.index') }}" },
        { form: '#search-matakuliah', list: '#matakuliah-list', route: "{{ route('admin.matakuliah.index') }}" },
        { form: '#search-kurikulum', list: '#kurikulum-list', route: "{{ route('admin.kurikulum.index') }}" },
        { form: '#search-dosen', list: '#dosen-list', route: "{{ route('admin.dosen.index') }}" },
        { form: '#search-jadwal-uts', list: '#jadwal-uts-list', route: "{{ route('admin.jadwal-uts.index') }}" },
        { form: '#search-jadwal-uas', list: '#jadwal-uas-list', route: "{{ route('admin.jadwal-uas.index') }}" },
        { form: '#search-aktivasi', list: '#aktivasi-list', route: "{{ route('admin.aktivasi.index') }}" },
        { form: '#search-uap', list: '#uap-list', route: "{{ route('admin.jadwal-uap.index') }}" },
        { form: '#search-tarif', list: '#tarif-list', route: "{{ route('admin.tarif.index') }}" },
        { form: '#search-tenor', list: '#tenor-list', route: "{{ route('admin.tenor-pembayaran.index') }}" },
        { form: '#search-tagihan', list: '#tagihan-list', route: "{{ route('admin.tagihan-mahasiswa.index') }}" },
    ];

    // Inisialisasi pencarian dan pagination untuk setiap konfigurasi
    searchConfigs.forEach(config => {
        handleSearch(config.form, config.list, config.route);
        handlePagination(config.list);
    });
});

 // Toggle-status & reset-all-status handlers moved to aktivasi-mhs/index.blade.php

        //generate tagihan mahasiswa
        $('#generate-tagihan').on('click', function () {
            Swal.fire({
                title: "Konfirmasi",
                text: "Yakin ingin menggenerate tagihan untuk mahasiswa aktif?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Generate!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route('admin.generate') }}",
                    method: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    beforeSend: function () {
                        $('#generate-tagihan').prop('disabled', true).text('Menggenerate...');
                    },
                    success: function (response) {
                        console.log(response); // DEBUG: Lihat respon di console browser
                        Swal.fire({
                            title: "Berhasil!",
                            text: response.message || "Tagihan berhasil dibuat.",
                            icon: "success",
                            timer: 3000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // Refresh halaman setelah sukses
                        });
                    },
                    error: function (xhr) {
                        console.error(xhr); // DEBUG: Lihat error di console browser
                        let errorMessage = "Terjadi kesalahan saat generate tagihan.";

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage += "\n" + xhr.responseJSON.message;
                        }

                        Swal.fire({
                            title: "Gagal!",
                            text: errorMessage,
                            icon: "error"
                        });
                    },
                    complete: function () {
                        $('#generate-tagihan').prop('disabled', false).text('Generate Tagihan');
                    }
                });
            });
        });


  // Fungsi untuk menampilkan pesan pada tabel
function showTableMessage(message) {
    document.querySelector('#table-mahasiswa tbody').innerHTML = `
        <tr>
            <td colspan="8" class="text-center">${message}</td>
        </tr>
    `;
}

// Fungsi untuk Fetch Data dengan Error Handling
async function fetchData(url) {
    try {
        const response = await fetch(url);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error('Fetch Error:', error);
        alert('Gagal mengambil data. Silakan coba lagi.');
        return null;
    }
}

        function confirmResetEdom() {
            Swal.fire({
                title: "Apakah Anda yakin?",
                text: "Semua status EDOM akan diatur ulang!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Ya, Reset!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("resetEdomForm").submit();
                }
            });
        }

        function confirmSetupEdom() {
            Swal.fire({
                title: "Konfirmasi Setup EDOM",
                text: "Apakah Anda yakin ingin memulihkan status EDOM?",
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#28a745",
                cancelButtonColor: "#d33",
                confirmButtonText: "Ya, Pulihkan!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById("setupEdomForm").submit();
                }
            });
        }

    </script>
    <script>
        $(document).ready(function() {
        // Initialize Select2
        $("#tahun-ajaran, #program-studi, #mata-kuliah").select2({
            allowClear: true,
            placeholder: "Pilih opsi",
            width: '100%'
        });

                // DOM Elements
                const tableBody = $("#table-mahasiswa tbody");
                const saveButton = $("#save-nilai");
                const loadingSpinner = $("#loading-spinner");
                const errorMessage = $("#error-message");

                // Global bobot storage
                let currentBobot = { uts: 25, uas: 35, tugas: 20, absensi: 10, praktik: 10 };
                let currentKonfigurasi = {};

                // Utility Functions
                const showTableMessage = (message, sub) => {
                    const subHtml = sub ? `<div class="empty-sub">${sub}</div>` : '';
                    tableBody.html(`<tr><td colspan="9"><div class="empty-state"><div class="empty-icon"><i class="bx bx-search-alt"></i></div><div class="empty-text">${message}</div>${subHtml}</div></td></tr>`);
                    $('#table-info').text(message);
                    $('#save-bar').hide();
                };

                const showError = (message) => {
                    if (typeof toastr !== 'undefined') {
                        toastr.error(message, 'Error!');
                    } else {
                        errorMessage.text(message).fadeIn().delay(3000).fadeOut();
                    }
                };

                const setLoading = (isLoading) => {
                    if (isLoading) {
                        loadingSpinner.show();
                        $("select").prop("disabled", true);
                    } else {
                        loadingSpinner.hide();
                        $("select").not("#program-studi, #mata-kuliah").prop("disabled", false);
                    }
                };

                // Calculate grade from score
                const calculateGrade = (score) => {
                    if (score >= 85.5) return 'A';
                    if (score >= 78.5) return 'AB';
                    if (score >= 74.5) return 'BA';
                    if (score >= 70.5) return 'B';
                    if (score >= 66.5) return 'BC';
                    if (score >= 59.5) return 'C';
                    if (score >= 45.5) return 'D';
                    return 'E';
                };

                // Auto-calculate row
                const autoCalcRow = ($row) => {
                    const uts = parseFloat($row.find('input[name^="uts"]').val()) || 0;
                    const uas = parseFloat($row.find('input[name^="uas"]').val()) || 0;
                    const tugas = parseFloat($row.find('input[name^="tugas"]').val()) || 0;
                    const absensi = parseFloat($row.find('input[name^="absensi"]').val()) || 0;
                    const praktik = parseFloat($row.find('input[name^="praktik"]').val()) || 0;

                    const nilaiAkhir = (
                        (uts * currentBobot.uts / 100) +
                        (uas * currentBobot.uas / 100) +
                        (tugas * currentBobot.tugas / 100) +
                        (absensi * currentBobot.absensi / 100) +
                        (praktik * currentBobot.praktik / 100)
                    ).toFixed(2);

                    const hurufMutu = calculateGrade(parseFloat(nilaiAkhir));

                    $row.find('.nilai-akhir').text(nilaiAkhir);
                    $row.find('.nilai-huruf').text(hurufMutu);
                };

                // Render bobot panel
                const renderBobotPanel = (bobot, konfigurasi) => {
                    const source = konfigurasi.bobot_source === 'custom' ?
                        '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Custom MK</span>' :
                        '<span class="badge bg-label-warning"><i class="bx bx-info-circle me-1"></i>Default Prodi</span>';

                    const totalPersen = parseFloat(bobot.uts) + parseFloat(bobot.uas) + parseFloat(bobot.tugas) + parseFloat(bobot.absensi) + parseFloat(bobot.praktik);

                    const html = `
                        <div id="bobot-panel" class="card mb-3 border" style="border-color: rgba(105,108,255,.15) !important;">
                            <div class="card-header d-flex align-items-center justify-content-between py-2" style="background: rgba(105,108,255,.04);">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bx bx-calculator text-primary"></i>
                                    <span class="fw-bold" style="font-size: .85rem;">Bobot Penilaian</span>
                                    ${source}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-muted" style="font-size: .75rem;">Total: <strong class="${totalPersen === 100 ? 'text-success' : 'text-danger'}">${totalPersen}%</strong></span>
                                    <button type="button" id="btn-edit-bobot" class="btn btn-sm btn-outline-primary" style="font-size: .72rem;">
                                        <i class="bx bx-edit-alt me-1"></i>Ubah Bobot
                                    </button>
                                </div>
                            </div>
                            <div id="bobot-display" class="card-body py-2">
                                <div class="d-flex flex-wrap gap-3">
                                    <span class="badge bg-label-primary">UTS: ${bobot.uts}%</span>
                                    <span class="badge bg-label-primary">UAS: ${bobot.uas}%</span>
                                    <span class="badge bg-label-primary">Tugas: ${bobot.tugas}%</span>
                                    <span class="badge bg-label-primary">Absen: ${bobot.absensi}%</span>
                                    <span class="badge bg-label-primary">Praktik: ${bobot.praktik}%</span>
                                </div>
                            </div>
                            <div id="bobot-edit" class="card-body py-3" style="display: none;">
                                <div class="row g-2 align-items-end">
                                    <div class="col">
                                        <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">UTS (%)</label>
                                        <input type="number" class="form-control form-control-sm bobot-input" id="bobot-uts" value="${bobot.uts}" min="0" max="100">
                                    </div>
                                    <div class="col">
                                        <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">UAS (%)</label>
                                        <input type="number" class="form-control form-control-sm bobot-input" id="bobot-uas" value="${bobot.uas}" min="0" max="100">
                                    </div>
                                    <div class="col">
                                        <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Tugas (%)</label>
                                        <input type="number" class="form-control form-control-sm bobot-input" id="bobot-tugas" value="${bobot.tugas}" min="0" max="100">
                                    </div>
                                    <div class="col">
                                        <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Absen (%)</label>
                                        <input type="number" class="form-control form-control-sm bobot-input" id="bobot-absen" value="${bobot.absensi}" min="0" max="100">
                                    </div>
                                    <div class="col">
                                        <label class="form-label mb-1" style="font-size: .7rem; font-weight: 700;">Praktik (%)</label>
                                        <input type="number" class="form-control form-control-sm bobot-input" id="bobot-praktik" value="${bobot.praktik}" min="0" max="100">
                                    </div>
                                    <div class="col-auto">
                                        <button type="button" id="btn-save-bobot" class="btn btn-sm btn-primary">
                                            <i class="bx bx-save me-1"></i>Simpan
                                        </button>
                                        <button type="button" id="btn-cancel-bobot" class="btn btn-sm btn-outline-secondary">Batal</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;

                    $('#bobot-panel').remove();
                    $('#bobot-panel-container').html(html);
                };

                // Update table header with bobot %
                const updateTableHeader = (bobot) => {
                    $('.th-komponen').eq(0).html(`UTS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.uts}%)</small>`);
                    $('.th-komponen').eq(1).html(`UAS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.uas}%)</small>`);
                    $('.th-komponen').eq(2).html(`TUGAS <small class="text-muted badge bg-label-secondary ms-1">(${bobot.tugas}%)</small>`);
                    $('.th-komponen').eq(3).html(`ABSEN <small class="text-muted badge bg-label-secondary ms-1">(${bobot.absensi}%)</small>`);
                    $('.th-komponen').eq(4).html(`PRAKTIK <small class="text-muted badge bg-label-secondary ms-1">(${bobot.praktik}%)</small>`);
                };

                // Tahun Ajaran Change Handler
                $("#tahun-ajaran").on("change", function() {
                    const tahunAjaranId = $(this).val();
                    $("#program-studi").val(null).trigger("change");
                    $("#mata-kuliah").val(null).trigger("change");
                    $("#program-studi").prop("disabled", !tahunAjaranId);
                    $('#bobot-panel-container').empty();
                    showTableMessage(
                        tahunAjaranId ? "Silakan pilih program studi" : "Silakan pilih tahun ajaran",
                        "Ikuti langkah di atas untuk memulai input nilai"
                    );
                });

                // Program Studi Change Handler
                 $("#program-studi").on("change", async function() {
                const programStudiId = $(this).val();
                const tahunAjaranId = $("#tahun-ajaran").val();
                const mataKuliahSelect = $("#mata-kuliah");

                mataKuliahSelect.val(null).trigger("change");
                showTableMessage("Memuat data mata kuliah...", "Mohon tunggu sebentar");
                saveButton.hide();
                $('#save-bar').hide();
                $('#bobot-panel-container').empty();

                if (!programStudiId || !tahunAjaranId) return;

                setLoading(true);

                try {
                    const response = await $.ajax({
                        url: `/admin/mata-kuliah/${programStudiId}/${tahunAjaranId}`,
                        method: 'GET',
                        dataType: 'json'
                    });

                    mataKuliahSelect.empty().append('<option value=""></option>');

                    if (response.length > 0) {
                        $.each(response, function(index, mk) {
                            mataKuliahSelect.append(
                                `<option value="${mk.matakuliah_id}">
                                    ${mk.nama} (${mk.matakuliah_id}) - Semester ${mk.smt}
                                </option>`
                            );
                        });
                        mataKuliahSelect.prop("disabled", false);
                        showTableMessage("Silakan pilih mata kuliah", "Pilih mata kuliah dari dropdown di atas");
                    } else {
                        showTableMessage("Tidak ada mata kuliah tersedia");
                        showError("Tidak ditemukan mata kuliah untuk program studi ini");
                        mataKuliahSelect.prop("disabled", false);
                    }
                } catch (error) {
                    console.error("Error:", error);
                    showError("Gagal memuat mata kuliah");
                    showTableMessage("Gagal memuat data");
                    mataKuliahSelect.prop("disabled", false);
                } finally {
                    setLoading(false);
                }
            });

                // Mata Kuliah Change Handler — load mahasiswa + bobot
                $("#mata-kuliah").on("change", async function() {
                    const mataKuliahId = $(this).val();
                    const tahunAjaranId = $("#tahun-ajaran").val();

                    tableBody.empty();
                    saveButton.hide();
                    $('#bobot-panel-container').empty();

                    if (!mataKuliahId || !tahunAjaranId) {
                        showTableMessage("Silakan pilih mata kuliah", "Pilih mata kuliah dari dropdown di atas");
                        return;
                    }

                    setLoading(true);
                    showTableMessage("Memuat data mahasiswa...", "Mohon tunggu sebentar");

                    try {
                        const response = await $.ajax({
                            url: `/admin/mahasiswa/input-nilai/${mataKuliahId}/${tahunAjaranId}`,
                            method: 'GET',
                            dataType: 'json'
                        });

                        if (response.mahasiswa && response.mahasiswa.length > 0) {
                            // Store bobot globally
                            currentBobot = response.konfigurasi.bobot;
                            currentKonfigurasi = response.konfigurasi;

                            // Render bobot panel
                            renderBobotPanel(currentBobot, currentKonfigurasi);
                            updateTableHeader(currentBobot);

                            // Render table
                            renderMahasiswaTable(response.mahasiswa);
                            saveButton.show();
                            $('#save-bar').show();
                            $('#table-info').text(response.mahasiswa.length + ' Mahasiswa ditemukan');
                            
                            // Tampilkan tombol export
                            $('#table-actions').show();
                            $('#btn-export-excel').attr('href', `/admin/mahasiswa/input-nilai/export/${mataKuliahId}/${tahunAjaranId}/excel`);
                            $('#btn-export-pdf').attr('href', `/admin/mahasiswa/input-nilai/export/${mataKuliahId}/${tahunAjaranId}/pdf`);

                        } else {
                            showTableMessage("Tidak ada mahasiswa", "Tidak ada mahasiswa aktif yang mengambil mata kuliah ini");
                            $('#table-actions').hide();
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        const errMsg = error.responseJSON?.message || "Gagal memuat data mahasiswa";
                        showError(errMsg);
                        showTableMessage("Gagal memuat data", errMsg);
                    } finally {
                        setLoading(false);
                        $("#mata-kuliah").prop("disabled", false);
                    }
                });


                // Render Mahasiswa Table — with auto-calculated akhir & khs
                const renderMahasiswaTable = (students) => {
                    tableBody.empty();

                    $.each(students, function(index, mhs) {
                        // Pre-calculate nilai akhir
                        const uts = parseFloat(mhs.uts) || 0;
                        const uas = parseFloat(mhs.uas) || 0;
                        const tugas = parseFloat(mhs.tugas) || 0;
                        const absen = parseFloat(mhs.absen) || 0;
                        const praktik = parseFloat(mhs.praktik) || 0;

                        const nilaiAkhir = (
                            (uts * currentBobot.uts / 100) +
                            (uas * currentBobot.uas / 100) +
                            (tugas * currentBobot.tugas / 100) +
                            (absen * currentBobot.absensi / 100) +
                            (praktik * currentBobot.praktik / 100)
                        ).toFixed(2);

                        const hurufMutu = calculateGrade(parseFloat(nilaiAkhir));

                        const row = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${mhs.nama}<br><small class="text-muted">${mhs.nim}</small></td>
                                <td>
                                    <input type="hidden" name="krs_id[${mhs.mahasiswa_id}]" value="${mhs.krs_id || ''}">
                                    <input type="number" step="0.01" name="uts[${mhs.mahasiswa_id}]"
                                        class="form-control form-control-sm komponen-nilai" value="${mhs.uts || ''}"
                                        min="0" max="100" style="width: 80px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="uas[${mhs.mahasiswa_id}]"
                                        class="form-control form-control-sm komponen-nilai" value="${mhs.uas || ''}"
                                        min="0" max="100" style="width: 80px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="tugas[${mhs.mahasiswa_id}]"
                                        class="form-control form-control-sm komponen-nilai" value="${mhs.tugas || ''}"
                                        min="0" max="100" style="width: 80px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="absensi[${mhs.mahasiswa_id}]"
                                        class="form-control form-control-sm komponen-nilai" value="${mhs.absen || ''}"
                                        min="0" max="100" style="width: 80px;">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="praktik[${mhs.mahasiswa_id}]"
                                        class="form-control form-control-sm komponen-nilai" value="${mhs.praktik || ''}"
                                        min="0" max="100" style="width: 80px;">
                                </td>
                                <td class="text-center">
                                    <strong class="nilai-akhir" style="font-size: .9rem;">${nilaiAkhir}</strong>
                                </td>
                                <td class="text-center">
                                    <span class="badge ${hurufMutu === 'A' || hurufMutu === 'AB' ? 'bg-success' : hurufMutu === 'BA' || hurufMutu === 'B' ? 'bg-primary' : hurufMutu === 'BC' || hurufMutu === 'C' ? 'bg-warning' : 'bg-danger'} nilai-huruf">${hurufMutu}</span>
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                };

                // Auto-calculate on any komponen nilai input change
                $(document).on("input", ".komponen-nilai", function() {
                    const $row = $(this).closest("tr");
                    autoCalcRow($row);
                });

                // Bobot panel: toggle edit mode
                $(document).on("click", "#btn-edit-bobot", function() {
                    $('#bobot-display').hide();
                    $('#bobot-edit').show();
                });
                $(document).on("click", "#btn-cancel-bobot", function() {
                    $('#bobot-edit').hide();
                    $('#bobot-display').show();
                });

                // Bobot panel: save bobot via AJAX
                $(document).on("click", "#btn-save-bobot", async function() {
                    const btn = $(this);
                    btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...');

                    const newBobot = {
                        program_studi_id: currentKonfigurasi.program_studi_id,
                        matakuliah_id: currentKonfigurasi.matakuliah_id,
                        persen_uts: parseFloat($('#bobot-uts').val()) || 0,
                        persen_uas: parseFloat($('#bobot-uas').val()) || 0,
                        persen_tugas: parseFloat($('#bobot-tugas').val()) || 0,
                        persen_absen: parseFloat($('#bobot-absen').val()) || 0,
                        persen_praktik: parseFloat($('#bobot-praktik').val()) || 0,
                    };

                    try {
                        const response = await $.ajax({
                            url: '{{ route("admin.bobot-nilai.save") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ...newBobot,
                            },
                            dataType: 'json'
                        });

                        if (response.success) {
                            // Update global bobot
                            currentBobot = {
                                uts: newBobot.persen_uts,
                                uas: newBobot.persen_uas,
                                tugas: newBobot.persen_tugas,
                                absensi: newBobot.persen_absen,
                                praktik: newBobot.persen_praktik,
                            };
                            currentKonfigurasi.bobot = currentBobot;
                            currentKonfigurasi.bobot_source = 'custom';

                            // Re-render bobot panel
                            renderBobotPanel(currentBobot, currentKonfigurasi);
                            updateTableHeader(currentBobot);

                            // Recalculate all rows
                            $('#table-mahasiswa tbody tr').each(function() {
                                autoCalcRow($(this));
                            });

                            if (typeof toastr !== 'undefined') {
                                toastr.success('Bobot berhasil disimpan & nilai diperbarui.', 'Berhasil!');
                            }
                        } else {
                            showError(response.message || 'Gagal menyimpan bobot');
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        showError("Gagal menyimpan bobot nilai");
                    } finally {
                        btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>Simpan');
                    }
                });

                // Form Submission Handler
                $("#form-nilai").on("submit", async function(e) {
                    e.preventDefault();

                    const form = this;
                    const formData = $(form).serialize();
                    const originalText = saveButton.text();

                    saveButton.prop("disabled", true).text("Menyimpan...");
                    setLoading(true);

                    try {
                        const response = await $.ajax({
                            url: $(form).attr("action"),
                            method: 'POST',
                            data: formData,
                            dataType: 'json'
                        });

                        if (response.success) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(response.message, 'Berhasil!', { timeOut: 3000, progressBar: true });
                            } else {
                                Swal.fire({ title: 'Berhasil!', text: response.message, icon: 'success', timer: 2000, showConfirmButton: false });
                            }
                        } else {
                            showError(response.message || "Gagal menyimpan data");
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        showError("Terjadi kesalahan saat menyimpan data");
                    } finally {
                        saveButton.prop("disabled", false).text(originalText);
                        setLoading(false);
                    }
                });
            });
         // Clear Selection Button
           $("#clear-selection").on("click", function() {
            $("#mata-kuliah").val(null).trigger("change").prop("disabled", false);
            $("#program-studi").val(null).trigger("change");
            showTableMessage("Silakan pilih mata kuliah", "Ikuti langkah di atas untuk memulai input nilai");
            $("#save-nilai").hide();
            $('#save-bar').hide();
            $('#bobot-panel-container').empty();
            const tahunAjaranSelected = $("#tahun-ajaran").val();
            $("#program-studi").prop("disabled", !tahunAjaranSelected);
            $("#mata-kuliah").prop("disabled", false);
        });
    </script>
    <script>
        $(document).ready(function() {
        // Fungsi untuk Fetch Data Mahasiswa
        function fetchMahasiswa(url) {
            var search = $('#search').val();
            var programStudi = $('#program-studi').val();
            var tahunMasuk = $('#tahun-masuk').val();
            var status = $('#status').val();

            // Menampilkan loading indicator
            $('#table-container').html('<div class="text-center my-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat data...</div>');

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    search: search,
                    jurusan_id: programStudi,
                    tahun_masuk: tahunMasuk,
                    status: status
                },
                dataType: 'json',
                success: function(response) {
                    $('#table-container').html(response.html);
                    $('#pagination-container').html(response.pagination);
                },
                error: function(xhr) {
                    $('#table-container').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>');
                    console.error(xhr.responseText);
                }
            });
        }

        // Event ketika tombol cari ditekan
        $('#search-btn').on('click', function() {
            fetchMahasiswa("{{ route('admin.mahasiswa.index') }}");
        });

        // Event ketika menekan Enter di input pencarian
        $('#search').on('keypress', function(e) {
            if (e.which === 13) { // 13 = Enter
                fetchMahasiswa("{{ route('admin.mahasiswa.index') }}");
            }
        });

        // Event untuk pagination menggunakan event delegation
        $(document).on('click', '#pagination-container a', function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            if (url) {
                fetchMahasiswa(url);
            }
        });
    });
    $(document).ready(function () {
    $(document).on("change", ".status-dropdown", function () {
        let selectElement = $(this);
        let mahasiswaRow = selectElement.closest("tr");
        let mahasiswaId = mahasiswaRow.data("id");
        let newStatus = selectElement.val();
        let statusBadge = mahasiswaRow.find(".status-badge");

        $.ajax({
            url: `/admin/mahasiswa/${mahasiswaId}/update-status`, // Pastikan route ini benar
            type: "PUT",
            data: {
                _token: "{{ csrf_token() }}",
                status_mhs: newStatus,
            },
            beforeSend: function () {
                selectElement.prop("disabled", true);
            },
            success: function (response) {
                let statusColors = {
                    "aktif": "success",
                    "nonaktif": "danger",
                    "lulus": "primary",
                    "dropout": "warning",
                    "cuti": "info",
                };

                // Perbarui badge status
                statusBadge.removeClass().addClass(`badge status-badge bg-${statusColors[newStatus]}`).text(newStatus.charAt(0).toUpperCase() + newStatus.slice(1));

                // Tampilkan alert sukses
                showStatusAlert("success", response.message || "Status berhasil diperbarui!");
            },
            error: function (xhr, status, error) {
                console.log("Error Details:", xhr); // Cek error lebih detail di console
                let errorMsg = "Terjadi kesalahan saat memperbarui status.";

                if (xhr.responseJSON) {
                    errorMsg = xhr.responseJSON.message || errorMsg;
                } else if (xhr.responseText) {
                    errorMsg = xhr.responseText;
                }

                showStatusAlert("danger", errorMsg);
            },
            complete: function () {
                selectElement.prop("disabled", false);
            }
        });
    });
    $(document).on("change", ".dosen-dropdown", function () {
    let selectElement = $(this);
    let mahasiswaRow = selectElement.closest("tr");
    let mahasiswaId = mahasiswaRow.data("id");
    let newDosen = selectElement.val(); // <-- ini yg hilang sebelumnya

    $.ajax({
        url: `/admin/mahasiswa/${mahasiswaId}/update-dosen`,
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            dosen_id: newDosen,
        },
        beforeSend: function () {
            selectElement.prop("disabled", true);
        },
        success: function (response) {
            showStatusAlert("success", response.message || "Dosen berhasil diperbarui!");
        },
        error: function (xhr) {
            console.log("Error Details:", xhr);
            let errorMsg = "Terjadi kesalahan saat memperbarui dosen.";
            if (xhr.responseJSON) {
                errorMsg = xhr.responseJSON.message || errorMsg;
            }
            showStatusAlert("danger", errorMsg);
        },
        complete: function () {
            selectElement.prop("disabled", false);
        }
    });
});


    function showStatusAlert(type, message) {
        let alertBox = $("#status-alert");
        alertBox.removeClass().addClass(`alert alert-${type} d-block`).html(message);
        setTimeout(() => alertBox.removeClass("d-block").addClass("d-none"), 5000);
    }
});

    </script>
    <script>
        document.getElementById('export-btn').addEventListener('click', function (e) {
    e.preventDefault();

    const baseUrl = document.getElementById('export-url').value;

    const search = document.getElementById('search').value;
    const programStudi = document.getElementById('program-studi').value;
    const tahunMasuk = document.getElementById('tahun-masuk').value;
    const status = document.getElementById('status').value;

    if (!search && !programStudi && !tahunMasuk && !status) {
        if (!confirm("Tidak ada filter diterapkan. Apakah Anda yakin ingin mengekspor semua data mahasiswa?")) {
            return;
        }
    }

    const exportUrl = `${baseUrl}?search=${encodeURIComponent(search)}&jurusan_id=${programStudi}&tahun_masuk=${tahunMasuk}&status=${status}`;

    // Gunakan window.location.href untuk trigger download file
    window.location.href = exportUrl;
});


    </script>
    <script>
        function handleAjaxTable({ sectionId, searchInputId, url }) {
        const $section = $(sectionId);
        const $searchInput = $(searchInputId);
        const $spinner = $('#loading-spinner');

        function fetchData(page = 1, search = '') {
            $spinner.show();

            $.ajax({
                url: `${url}?page=${page}&search=${search}`,
                success: function (data) {
                    $section.find('#table-container').html(data);
                },
                error: function () {
                    $section.find('#table-container').html(
                        '<div class="alert alert-danger text-center">Gagal memuat data.</div>'
                    );
                },
                complete: function () {
                    $spinner.hide();
                }
            });
        }

        // Debounced Search Handler
        let debounce;
        $searchInput.on('keyup', function () {
            clearTimeout(debounce);
            const query = $(this).val();
            debounce = setTimeout(() => {
                fetchData(1, query);
            }, 300);
        });

        // Pagination Handler
        $section.on('click', '.pagination a', function (e) {
            e.preventDefault();
            const page = $(this).attr('href').split('page=')[1];
            const query = $searchInput.val();
            fetchData(page, query);
        });

        // Return fetchData if needed
        return { fetchData };
    }

    $(document).ready(function () {
        // Transkrip section
        if ($('#transkrip-section').length) {
            handleAjaxTable({
                sectionId: '#transkrip-section',
                searchInputId: '#search-pengajuan',
                url: "{{ route('admin.transkrip.index') }}"
            });
        }

        // Tahun Ajaran section
        if ($('#tahun-ajaran-section').length) {
            handleAjaxTable({
                sectionId: '#tahun-ajaran-section',
                searchInputId: '#search-tahun-ajaran',
                url: "{{ route('admin.tahun-ajaran.index') }}"
            });
        }

        // Permintaan Helpdesk section
        if ($('#permintaan-section').length) {
            handleAjaxTable({
                sectionId: '#permintaan-section',
                searchInputId: '#search-permintaan',
                url: "{{ route('admin.helpdesk.index') }}"
            });
        }
    });
    </script>
    <script>
        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            let data = $(this).serialize();

            $.get("{{ route('admin.uap.getMahasiswa') }}", data)
            .done(function(response) {
                let rows = '';
                if (Array.isArray(response.data) && response.data.length > 0) {
                response.data.forEach(mhs => {
                    rows += `
                    <tr data-id="${mhs.mahasiswa_id}">
                        <td>${mhs.nim}</td>
                        <td>${mhs.nama}</td>
                        <td>
                        <input type="number" class="form-control tulis" data-id="${mhs.mahasiswa_id}" name="uap_tulis[${mhs.mahasiswa_id}]" placeholder="Tulis" min="0" max="100" value="${mhs.uap_tulis ?? ''}">
                        </td>
                        <td>
                        <input type="number" class="form-control praktik" data-id="${mhs.mahasiswa_id}" name="uap_praktik[${mhs.mahasiswa_id}]" placeholder="Praktik" min="0" max="100" value="${mhs.uap_praktik ?? ''}">
                        </td>
                        <td>
                        <button type="button" class="btn btn-sm btn-success simpan-nilai" data-id="${mhs.mahasiswa_id}">Simpan</button>
                        </td>
                    </tr>
                    `;
                });
                } else {
                rows = `<tr><td colspan="5" class="text-center">Tidak ada data mahasiswa ditemukan.</td></tr>`;
                }
                $('#mahasiswa-uap-list').html(`
                <table class="table table-bordered mt-3">
                    <thead>
                    <tr>
                        <th>NIM</th>
                        <th>Nama</th>
                        <th>UAP Tulis</th>
                        <th>UAP Praktik</th>
                        <th>Aksi</th>
                    </tr>
                    </thead>
                    <tbody>${rows}</tbody>
                </table>
                `);
            })
            .fail(function(xhr) {
                let message = xhr.responseJSON?.message ?? "Error tidak diketahui";
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                message += "\n" + Object.values(xhr.responseJSON.errors).flat().join("\n");
                }
                alert("Gagal ambil mahasiswa:\n" + message);
                $('#mahasiswa-uap-list').html(
                `<div class="alert alert-danger mt-3">${message}</div>`
                );
            });

        });

        // Simpan nilai dengan AJAX
        $(document).on('click', '.simpan-nilai', function () {
            let id = $(this).data('id');
            // Ambil value terbaru dari input pada baris yang sama
            let row = $(this).closest('tr');
            let uap_tulis = row.find('.tulis').val();
            let uap_praktik = row.find('.praktik').val();
            let tahun_ajaran_id = $('[name="tahun_ajaran_id"]').val();

            $.post("{{ route('admin.uap.simpanNilai') }}", {
            _token: '{{ csrf_token() }}',
            mahasiswa_id: id,
            tahun_ajaran_id: tahun_ajaran_id,
            uap_tulis: uap_tulis,
            uap_praktik: uap_praktik
            }, function(res) {
            if(res.success) {
                alert('Nilai berhasil disimpan');
            } else {
                alert('Gagal menyimpan nilai');
            }
            });
        });

    // Simpan nilai dengan AJAX

    </script>
</body>

</html>
