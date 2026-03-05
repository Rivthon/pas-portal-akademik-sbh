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

 document.addEventListener('DOMContentLoaded', function () {
    // Menggunakan event delegation pada document untuk menangani toggle yang muncul setelah paginasi atau pencarian
    document.addEventListener('change', function (event) {
        if (event.target.classList.contains('toggle-status')) {
            const toggle = event.target;
            const mahasiswaId = toggle.dataset.id;
            const type = toggle.dataset.type;
            const status = toggle.checked ? 1 : 0;

            fetch('{{ route("admin.aktivasi-mhs.updateStatus") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    mahasiswa_id: mahasiswaId,
                    type: type,
                    status: status,
                }),
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                    } else {
                        alert('Gagal memperbarui status: ' + data.message);
                        toggle.checked = !toggle.checked; // Kembalikan nilai toggle jika gagal
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memperbarui status.');
                    toggle.checked = !toggle.checked; // Kembalikan nilai toggle jika gagal
                });
        }
    });


    // Event listener untuk tombol reset semua status
    document.getElementById('reset-all-status').addEventListener('click', function () {
        if (confirm('Apakah Anda yakin ingin mereset semua status menjadi 0?')) {
            fetch('{{ route("admin.reset.all.status") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload(); // Refresh halaman untuk menampilkan perubahan
                    } else {
                        alert('Gagal mereset status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat mereset status.');
                });
        }
    });
});

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

                // Utility Functions
                const showTableMessage = (message) => {
                    tableBody.html(`<tr><td colspan="9" class="text-center">${message}</td></tr>`);
                };

                const showError = (message) => {
                    errorMessage.text(message).fadeIn().delay(3000).fadeOut();
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

                // Tahun Ajaran Change Handler
                $("#tahun-ajaran").on("change", function() {
                    const tahunAjaranId = $(this).val();
                    $("#program-studi").val(null).trigger("change");
                    $("#mata-kuliah").val(null).trigger("change");

                    // Only disable program studi if no tahun ajaran selected
                    $("#program-studi").prop("disabled", !tahunAjaranId);

                    // NEVER disable mata kuliah here
                    showTableMessage(tahunAjaranId ? "Silakan pilih program studi" : "Silakan pilih tahun ajaran");
                });

                // Program Studi Change Handler
                 $("#program-studi").on("change", async function() {
                const programStudiId = $(this).val();
                const tahunAjaranId = $("#tahun-ajaran").val();
                const mataKuliahSelect = $("#mata-kuliah");

                mataKuliahSelect.val(null).trigger("change");
                showTableMessage("Memuat data mata kuliah...");
                saveButton.hide();

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
                        // Ensure mata kuliah is always enabled when we have options
                        mataKuliahSelect.prop("disabled", false);
                        showTableMessage("Silakan pilih mata kuliah");
                    } else {
                        showTableMessage("Tidak ada mata kuliah tersedia");
                        showError("Tidak ditemukan mata kuliah untuk program studi ini");
                        // Keep mata kuliah enabled but empty
                        mataKuliahSelect.prop("disabled", false);
                    }
                } catch (error) {
                    console.error("Error:", error);
                    showError("Gagal memuat mata kuliah");
                    showTableMessage("Gagal memuat data");
                    // Keep mata kuliah enabled even on error
                    mataKuliahSelect.prop("disabled", false);
                } finally {
                    setLoading(false);
                }
            });

                $("#mata-kuliah").on("change", async function() {
                    const mataKuliahId = $(this).val();
                    const tahunAjaranId = $("#tahun-ajaran").val();

                    tableBody.empty();
                    saveButton.hide();

                    if (!mataKuliahId || !tahunAjaranId) {
                        showTableMessage("Silakan pilih mata kuliah");
                        return;
                    }

                    setLoading(true);
                    showTableMessage("Memuat data mahasiswa...");

                    try {
                        const response = await $.ajax({
                            url: `/admin/mahasiswa/input-nilai/${mataKuliahId}/${tahunAjaranId}`,
                            method: 'GET',
                            dataType: 'json'
                        });

                        if (response.mahasiswa && response.mahasiswa.length > 0) {
                            renderMahasiswaTable(response.mahasiswa);
                            saveButton.show();
                        } else {
                            showTableMessage("Tidak ada mahasiswa yang mengambil mata kuliah ini");
                        }
                    } catch (error) {
                        console.error("Error:", error);
                        showError("Gagal memuat data mahasiswa");
                        showTableMessage("Gagal memuat data");
                    } finally {
                        setLoading(false);
                        // Ensure mata kuliah stays enabled after loading
                        $("#mata-kuliah").prop("disabled", false);
                    }
                });


                // Render Mahasiswa Table
                const renderMahasiswaTable = (students) => {
                    tableBody.empty();

                    $.each(students, function(index, mhs) {
                        const row = `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${mhs.nama} (${mhs.mahasiswa_id})</td>
                                <td>
                                    <input type="hidden" name="krs_id[${mhs.mahasiswa_id}]" value="${mhs.krs_id || ''}">
                                    <input type="number" step="0.01" name="uts[${mhs.mahasiswa_id}]"
                                        class="form-control" value="${mhs.uts || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="uas[${mhs.mahasiswa_id}]"
                                        class="form-control" value="${mhs.uas || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="tugas[${mhs.mahasiswa_id}]"
                                        class="form-control" value="${mhs.tugas || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="absensi[${mhs.mahasiswa_id}]"
                                        class="form-control" value="${mhs.absensi || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="praktik[${mhs.mahasiswa_id}]"
                                        class="form-control" value="${mhs.praktik || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="akhir[${mhs.mahasiswa_id}]"
                                        class="form-control nilai-akhir" value="${mhs.akhir || ''}"
                                        min="0" max="100">
                                </td>
                                <td>
                                    <input type="text" name="khs[${mhs.mahasiswa_id}]"
                                        class="form-control nilai-huruf" value="${mhs.khs || ''}" readonly>
                                </td>
                            </tr>
                        `;
                        tableBody.append(row);
                    });
                };

                // Auto calculate grade when akhir value changes
                $(document).on("input", ".nilai-akhir", function() {
                    const nilai = parseFloat($(this).val());
                    const hurufInput = $(this).closest("tr").find(".nilai-huruf");

                    if (!isNaN(nilai) && nilai >= 0 && nilai <= 100) {
                        hurufInput.val(calculateGrade(nilai));
                    } else {
                        hurufInput.val("");
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
                            alert("Data nilai berhasil disimpan!");
                            // Don't reload the table - keep user's current input state
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
         // Clear Selection Button - MODIFIED to properly handle mata kuliah state
           $("#clear-selection").on("click", function() {
    // Clear and reset mata kuliah dropdown
            $("#mata-kuliah").val(null).trigger("change").prop("disabled", false);

            // Clear and reset program studi dropdown
            $("#program-studi").val(null).trigger("change");

            // Reset table message
            showTableMessage("Silakan pilih program studi dan mata kuliah");

            // Hide save button
            $("#save-nilai").hide();

            // Ensure proper enabled states:
            // - Program studi enabled only if tahun ajaran is selected
            const tahunAjaranSelected = $("#tahun-ajaran").val();
            $("#program-studi").prop("disabled", !tahunAjaranSelected);

            // Mata kuliah always enabled after clear
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
