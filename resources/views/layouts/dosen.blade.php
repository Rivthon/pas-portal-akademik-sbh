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
    {{-- <div class="pre-loader is-load">
        <div class="circle-loader"></div>
    </div> --}}
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            <!-- Menu -->
            @include('components.sidebar-dosen')
            <!-- / Menu -->
            @include('sweetalert::alert')
            @include('components.notification')
            <!-- Layout container -->
            <div class="layout-page">

                <!-- Navbar -->
                @include('components.navbar-dosen')
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
                    @include('components.footer-dosen')
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
    @stack('script')
    <script>
        $(document).ready(function () {
            $('#searchJadwal').on('keyup', function () {
                let query = $(this).val();

                $.ajax({
                    url: "{{ route('dosen.jadwal.search') }}",
                    type: "GET",
                    data: { search: query },
                    success: function (data) {
                        $('#jadwalContainer').html(data);
                    }
                });
            });
        });
         $(document).ready(function () {
            $('#searchPraktik').on('keyup', function () {
                let query = $(this).val();

                $.ajax({
                    url: "{{ route('dosen.praktik.search') }}",
                    type: "GET",
                    data: { search: query },
                    success: function (data) {
                        $('#jadwalContainer').html(data);
                    }
                });
            });
        });
         $(document).ready(function () {
            $('#searchAbsensi').on('keyup', function () {
                let query = $(this).val();

                $.ajax({
                    url: "{{ route('dosen.absensi.search') }}",
                    type: "GET",
                    data: { search: query },
                    success: function (data) {
                        $('#absensiContainer').html(data);
                    }
                });
            });
        });

           function openPertemuanModal(jadwal_id, nama_matakuliah, kode_matakuliah) {
                document.getElementById("jadwal_id").value = jadwal_id;
                document.getElementById("nama_matakuliah").value = nama_matakuliah;

                // 🔹 Panggil AJAX untuk memuat daftar pertemuan
                loadListPertemuan(jadwal_id);

                // Tampilkan modal
                var pertemuanModal = new bootstrap.Modal(document.getElementById("pertemuanModal"));
                pertemuanModal.show();
            }

            // 🔹 Load Daftar Pertemuan
            function loadListPertemuan(jadwal_id) {
                $.ajax({
                    url: `/dosen/dosen/pertemuan/list/${jadwal_id}`,
                    type: "GET",
                    success: function (data) {
                        let pertemuanHTML = "";
                        if (data.length === 0) {
                            pertemuanHTML = '<li class="list-group-item text-muted">Belum ada pertemuan.</li>';
                        } else {
                            data.forEach(function (item) {
                                // Hitung durasi mengajar (dalam menit)
                                let startTime = new Date(`2024-01-01T${item.jam_mulai}`);
                                let endTime = new Date(`2024-01-01T${item.jam_selesai}`);
                                let durasiMenit = (endTime - startTime) / (1000 * 60);

                                pertemuanHTML += `
                                    <li class="list-group-item">
                                        <strong>${item.tanggal_pertemuan}</strong> - ${item.topik} <br>
                                        ⏰ <span class="text-muted">${item.jam_mulai} - ${item.jam_selesai} (${durasiMenit} menit)</span>
                                        <a href="/dosen/dosen/absensi/buat/${item.pertemuan_id}" class="btn btn-sm btn-primary float-end">
                                            Lihat Absensi
                                        </a>
                                    </li>
                                `;
                            });
                        }
                        $("#pertemuanList").html(pertemuanHTML);
                    },
                    error: function () {
                        $("#pertemuanList").html('<li class="list-group-item text-danger">Gagal memuat daftar pertemuan.</li>');
                    }
                });
            }

            // 🔹 Submit Form Tambah Pertemuan
           $(document).ready(function () {
            $('#pertemuanForm').submit(function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('dosen.absensi.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        alert("Pertemuan berhasil disimpan!");

                        // Sembunyikan modal
                        $('#pertemuanModal').modal('hide');
                        $('#pertemuanForm')[0].reset();

                        // 🔹 Perbarui daftar pertemuan dalam modal
                        loadListPertemuan(response.jadwal_id);
                    },
                    error: function (xhr) {
                        alert("Terjadi kesalahan. Silakan coba lagi.");
                    }
                });
            });
        });


// // 🔹 Fungsi untuk Buat Absensi
// function buatAbsensi(idPertemuan) {
//     window.location.href = `/absensi/buat/${idPertemuan}`;
// }
 $(document).ready(function () {
        $('#selectMahasiswa').select2({
            placeholder: "Cari Mahasiswa...",
            allowClear: true
        });
    });
        $(document).ready(function () {
            // Tandai semua mahasiswa sebagai "Hadir"
            $("#markAllPresent").click(function () {
                $(".status-absen[value='hadir']").prop("checked", true);
            });

            // Reset semua absensi ke Alpha (default)
            $("#resetAbsensi").click(function () {
                $(".status-absen[value='tidak hadir']").prop("checked", true);
            });

            // Submit form dengan AJAX
               $('#absensiForm').on('submit', function (e) {
        e.preventDefault(); // Mencegah reload halaman

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('dosen.absensi-store') }}", // Sesuaikan dengan route penyimpanan
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Absensi berhasil disimpan.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Refresh halaman setelah sukses
                });
            },
           error: function (xhr) {
                console.log(xhr.responseText); // Tampilkan di console browser
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan: ' + xhr.responseText,
                });
            }

        });
    });
        });
          // Submit form dengan AJAX
               $('#praktikForm').on('submit', function (e) {
        e.preventDefault(); // Mencegah reload halaman

        let formData = new FormData(this);

        $.ajax({
            url: "{{ route('dosen.absensi-praktik-store') }}", // Sesuaikan dengan route penyimpanan
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Absensi berhasil disimpan.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Refresh halaman setelah sukses
                });
            },
           error: function (xhr) {
                console.log(xhr.responseText); // Tampilkan di console browser
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Terjadi kesalahan: ' + xhr.responseText,
                });
            }

        });
    });

        document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("btnTambahMahasiswa").addEventListener("click", function() {
            let select = document.getElementById("selectMahasiswa");
            let mahasiswaId = select.value;
            let mahasiswaNama = select.options[select.selectedIndex].text;

            if (!mahasiswaId) {
                alert("Pilih mahasiswa terlebih dahulu!");
                return;
            }

            // Cek apakah mahasiswa sudah ada di tabel
            let existingRows = document.querySelectorAll("input[name^='status']");
            for (let row of existingRows) {
                if (row.name.includes(mahasiswaId)) {
                    alert("Mahasiswa ini sudah ada di daftar absensi!");
                    return;
                }
            }

            // Tambah baris baru ke tabel
            let tbody = document.querySelector("tbody");
            let newRow = document.createElement("tr");

            newRow.innerHTML = `
                <td>#</td>
                <td>${mahasiswaNama}</td>
                <td class="text-center"><span class="badge bg-info">-</span></td>
                <td class="text-center"><input class="form-check-input" type="radio" name="status[${mahasiswaId}]" value="hadir" required></td>
                <td class="text-center"><input class="form-check-input" type="radio" name="status[${mahasiswaId}]" value="izin"></td>
                <td class="text-center"><input class="form-check-input" type="radio" name="status[${mahasiswaId}]" value="sakit"></td>
                <td class="text-center"><input class="form-check-input" type="radio" name="status[${mahasiswaId}]" value="tidak hadir"></td>
                <td><input type="text" class="form-control" name="keterangan[${mahasiswaId}]" placeholder="Opsional"></td>
            `;

            tbody.appendChild(newRow);

            // Reset pilihan mahasiswa setelah ditambahkan
            select.value = "";
        });
    });

    document.getElementById('jam_mulai').addEventListener('input', function () {
        let time = this.value;
        document.getElementById('format-jam').innerText = `Jam yang dipilih: ${time}`;
    });

    document.getElementById('jam_selesai').addEventListener('input', function () {
        let time = this.value;
        document.getElementById('format-jam-selesai').innerText = `Jam selesai yang dipilih: ${time}`;
    });
    function confirmLogout(event) {
        event.preventDefault();
        Swal.fire({
            title: "Anda yakin ingin keluar?",
            text: "Anda akan keluar dari sesi saat ini!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Ya, Keluar!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('logout-form').submit();
            }
        });
    }
        function openAbsensiPraktikModal(jadwal_praktik_id, nama_matakuliah) {
            document.getElementById("jadwal_praktik_id").value = jadwal_praktik_id;
            document.getElementById("nama_matakuliah").value = nama_matakuliah;


            // Panggil AJAX untuk memuat daftar pertemuan berdasarkan tipe
            loadListAbsensiPraktik(jadwal_praktik_id);

            // Tampilkan modal
            var pertemuanPrModal = new bootstrap.Modal(document.getElementById("pertemuanPrModal"));
            pertemuanPrModal.show();
        }

        function loadListAbsensiPraktik(jadwal_praktik_id) {
            $.ajax({
               url: `/dosen/dosen/pertemuan-praktik/list/${jadwal_praktik_id}`,
                type: "GET",
                success: function (data) {
                    let absensiHTML = data.length === 0
                        ? '<li class="list-group-item text-muted">Belum ada absensi.</li>'
                        : data.map(item => `
                            <li class="list-group-item">
                                <strong>${item.tanggal_pertemuan}</strong> - ${item.topik} <br>
                                ⏰ <span class="text-muted">${item.jam_mulai} - ${item.jam_selesai} (${calculateDuration(item.jam_mulai, item.jam_selesai)} menit)</span>
                                <a href="/dosen/dosen/absensi-praktik/buat/${item.pertemuan_praktik_id}" class="btn btn-sm btn-primary float-end">
                                    Lihat Absensi
                                </a>
                            </li>`).join('');

                    $("#absensiList").html(absensiHTML);
                },
                error: function () {
                    $("#absensiList").html('<li class="list-group-item text-danger">Gagal memuat daftar absensi.</li>');
                }
            });
        }

        function calculateDuration(jam_mulai, jam_selesai) {
            let startTime = new Date(`2024-01-01T${jam_mulai}`);
            let endTime = new Date(`2024-01-01T${jam_selesai}`);
            return Math.round((endTime - startTime) / (1000 * 60));
        }

         // 🔹 Submit Form Tambah Pertemuan
           $(document).ready(function () {
            $('#pertemuanprakForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('dosen.absensi-praktik.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        alert("Pertemuan berhasil disimpan!");

                        // Sembunyikan modal
                        $('#pertemuanPrModal').modal('hide');
                        $('#pertemuanprakForm')[0].reset();

                        // 🔹 Perbarui daftar pertemuan dalam modal
                        loadListPertemuan(response.jadwal_praktik_id);
                    },
                    error: function (xhr) {
                        alert("Terjadi kesalahan. Silakan coba lagi.");
                    }
                });
            });
        });

    </script>

</body>

</html>