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
                    url: `/dosen/pertemuan/list/${jadwal_id}`,
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
                                        <span class="badge bg-label-${(item.metode_pbm || 'offline').toLowerCase() === 'online' ? 'primary' : 'secondary'} ms-1">${(item.metode_pbm || 'offline').charAt(0).toUpperCase() + (item.metode_pbm || 'offline').slice(1)}</span>
                                        <a href="/dosen/absensi/buat/${item.pertemuan_id}" class="btn btn-sm btn-primary float-end">
                                            Lihat Absensi
                                        </a>
                                    </li>
                                `;
                            });
                        }
                        $("#pertemuanList").html(pertemuanHTML);
                    },
                    error: function (xhr) {
                        let errorMessage = xhr.responseJSON?.message || "Gagal memuat daftar pertemuan. Silakan coba lagi.";
                        $("#pertemuanList").html(`<li class="list-group-item text-danger">${errorMessage}</li>`);
                    }
                });
            }

            // 🔹 Submit Form Tambah Pertemuan
           $(document).ready(function () {
            $(document).on('submit', '#pertemuanForm', function (e) {
                e.preventDefault();

                $.ajax({
                    url: "{{ route('dosen.absensi.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function (response) {
                        if (response.redirect_url) {
                            window.location.assign(response.redirect_url);
                            return;
                        }

                        // Sembunyikan modal
                        bootstrap.Modal.getInstance(document.getElementById('pertemuanModal')).hide();

                        // 🔹 Perbarui daftar pertemuan dalam modal
                        loadListPertemuan(response.jadwal_id);
                    },
                    error: function (xhr) {
                        let errorMessage = xhr.responseJSON?.message || "Terjadi kesalahan. Silakan coba lagi Pertemuan Teori.";
                        Swal.fire({ icon: 'error', title: 'Gagal', text: errorMessage });
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
        document.getElementById('format-jam-mulai').innerText = `Jam yang dipilih: ${time}`;
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
               url: `/dosen/pertemuan-praktik/list/${jadwal_praktik_id}`,
                type: "GET",
                success: function (data) {
                    let absensiHTML = data.length === 0
                        ? '<li class="list-group-item text-muted">Belum ada absensi.</li>'
                        : data.map(item => `
                            <li class="list-group-item">
                                <strong>${item.tanggal_pertemuan}</strong> - ${item.topik} <br>
                                ⏰ <span class="text-muted">${item.jam_mulai} - ${item.jam_selesai} (${calculateDuration(item.jam_mulai, item.jam_selesai)} menit)</span>
                                <span class="badge bg-label-${(item.metode_pbm || 'offline').toLowerCase() === 'online' ? 'primary' : 'secondary'} ms-1">${(item.metode_pbm || 'offline').charAt(0).toUpperCase() + (item.metode_pbm || 'offline').slice(1)}</span>
                                <a href="/dosen/absensi-praktik/buat/${item.pertemuan_praktik_id}" class="btn btn-sm btn-primary float-end">
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
                        let errorMessage = xhr.responseJSON?.message || "Terjadi kesalahan. Silakan coba lagi.";
                        alert(`Error: ${errorMessage}`);
                    }
                });
            });
        });

    </script>
    <script>
        // Variabel global untuk menyimpan konfigurasi dari server
    let konfigurasiNilai = {
        bobot: {},
        mutu: []
    };

    // ===================================================================================
    // FUNGSI KALKULASI BARU (MENGGUNAKAN ATURAN DARI SERVER)
    // ===================================================================================

    /**
     * Mengonversi nilai akhir ke Nilai Huruf berdasarkan aturan dari server.
     */
    function konversiKeKHS(nilai) {
        // Cari di aturan 'mutu' dari nilai terbesar ke terkecil
        for (const aturan of konfigurasiNilai.mutu) {
            if (nilai >= aturan.nilai) {
                return aturan.huruf;
            }
        }
        return 'E'; // Default jika tidak ada yang cocok
    }

    /**
     * Menghitung nilai akhir berdasarkan bobot dari server.
     */
    function hitungNilaiAkhirDanKhs(mahasiswaId) {
        const bobot = konfigurasiNilai.bobot;

        // Cek jika bobot belum terisi
        if (Object.keys(bobot).length === 0) return;

        const uts = parseFloat($(`input[name="uts[${mahasiswaId}]"]`).val()) || 0;
        const uas = parseFloat($(`input[name="uas[${mahasiswaId}]"]`).val()) || 0;
        const tugas = parseFloat($(`input[name="tugas[${mahasiswaId}]"]`).val()) || 0;
        const absensi = parseFloat($(`input[name="absensi[${mahasiswaId}]"]`).val()) || 0;
        const praktik = parseFloat($(`input[name="praktik[${mahasiswaId}]"]`).val()) || 0;

        // Hitung nilai akhir dengan membagi persen dengan 100
        const nilaiAkhir =
            (uts * (bobot.uts / 100)) +
            (uas * (bobot.uas / 100)) +
            (tugas * (bobot.tugas / 100)) +
            (absensi * (bobot.absensi / 100)) +
            (praktik * (bobot.praktik / 100));

        const khs = konversiKeKHS(nilaiAkhir);

        // Tampilkan dengan 2 angka desimal untuk presisi, lalu bulatkan di input
        $(`#akhir-${mahasiswaId}`).val(nilaiAkhir.toFixed(2));
        $(`#khs-${mahasiswaId}`).text(khs);
    }
// ===================================================================================
        function showTableMessage(message) {
            const tableBody = document.querySelector("#table-mahasiswa tbody");
            const columnCount = document.querySelector("#table-mahasiswa thead th").length;
            tableBody.innerHTML = `<tr><td colspan="${columnCount}" class="text-center">${message}</td></tr>`;
        }

        // ===================================================================================
        // EVENT HANDLER DAN LOGIKA UTAMA
        // ===================================================================================

        $(document).ready(function () {
            // Inisialisasi Select2
            $("#tahun-ajaran, #program-studi, #mata-kuliah").select2({
                allowClear: true,
            });

            const tableBody = document.querySelector("#table-mahasiswa tbody");
            const saveButton = document.getElementById("save-nilai");

            // Handle perubahan Tahun Ajaran
            $("#tahun-ajaran").on("change", function () {
                const tahunAjaranId = this.value;
                $("#program-studi").val(null).trigger("change");
                $("#program-studi").prop("disabled", !tahunAjaranId);
            });

            // Handle perubahan Program Studi -> Fetch Mata Kuliah
            $("#program-studi").on("change", async function () {
                const programStudiId = $(this).val();
                const tahunAjaranId = $("#tahun-ajaran").val();
                const mataKuliahSelect = $("#mata-kuliah");

                mataKuliahSelect.val(null).trigger("change");
                mataKuliahSelect.html('<option value=""></option>').prop("disabled", true);
                showTableMessage("Pilih Tahun Ajaran dan Program Studi.");

                if (!programStudiId || !tahunAjaranId) return;

                try {
                    const response = await fetch(`/dosen/mata-kuliah/${programStudiId}/${tahunAjaranId}`);
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status}`);
                    const data = await response.json();

                    if (Array.isArray(data) && data.length > 0) {
                        const options = data.map(mk =>
                            `<option value="${mk.jadwal_id}">
                                ${mk.nama} (${mk.matakuliah_id}) - ${mk.program_studi} - ${mk.jenis_kelas.toUpperCase()} - Semester ${mk.smt}
                            </option>`
                        ).join("");
                        mataKuliahSelect.html('<option value=""></option>' + options);
                        mataKuliahSelect.prop("disabled", false);
                    } else {
                        alert("Tidak ada mata kuliah yang tersedia untuk program studi ini.");
                    }
                } catch (error) {
                    console.error("Gagal memuat mata kuliah:", error);
                    alert("Terjadi kesalahan saat memuat mata kuliah.");
                }
            });

            // Handle perubahan Mata Kuliah -> Fetch Mahasiswa (Filter Klasik)
            $("#mata-kuliah").on("change", async function () {
                const jadwalId = $(this).val();
                const mkNama = $(this).find("option:selected").text();

                if(!jadwalId) return;

                // Hilangkan styling card yg mgkn sebelumnya diselect
                $(".mk-card").removeClass("selected-card");
                $("#label-mk-terpilih").text(mkNama);

                await loadInputNilai(jadwalId);
            });

            // Handle Klik Kartu Mata Kuliah di Dashboard (Fitur Baru)
            $(".mk-card").on("click", async function() {
                const jadwalId = $(this).data("jadwal-id");
                const mkNama = $(this).data("mk-nama");

                // Clear active dropdown selections (arsip collapse)
                $("#mata-kuliah").val("").trigger("change.select2");

                $(".mk-card").removeClass("selected-card");
                $(this).addClass("selected-card");

                $("#label-mk-terpilih").text(mkNama);

                await loadInputNilai(jadwalId);
            });

            // Tutup form
            $("#btnTutupPanel").on("click", function() {
                $("#panel-penilaian").slideUp();
                $(".mk-card").removeClass("selected-card");
                $("#mata-kuliah").val("").trigger("change.select2");
            });

            const updateTableHeader = (bobot) => {
                $('.table-light th').eq(3).html(`UTS <br><small class="text-muted badge bg-label-secondary mx-auto mt-1">${bobot.uts}%</small>`);
                $('.table-light th').eq(4).html(`UAS <br><small class="text-muted badge bg-label-secondary mx-auto mt-1">${bobot.uas}%</small>`);
                $('.table-light th').eq(5).html(`TUGAS <br><small class="text-muted badge bg-label-secondary mx-auto mt-1">${bobot.tugas}%</small>`);
                $('.table-light th').eq(6).html(`ABSEN <br><small class="text-muted badge bg-label-secondary mx-auto mt-1">${bobot.absensi}%</small>`);
                $('.table-light th').eq(7).html(`PRAKTIK <br><small class="text-muted badge bg-label-secondary mx-auto mt-1">${bobot.praktik}%</small>`);
            };

            const renderBobotPanel = (bobot, konfigurasi) => {
                const source = konfigurasi.bobot_source === 'custom' ?
                    '<span class="badge bg-label-success"><i class="bx bx-check-circle me-1"></i>Custom Dosen</span>' :
                    '<span class="badge bg-label-warning"><i class="bx bx-info-circle me-1"></i>Default Prodi</span>';

                const totalPersen = parseFloat(bobot.uts) + parseFloat(bobot.uas) + parseFloat(bobot.tugas) + parseFloat(bobot.absensi) + parseFloat(bobot.praktik);

                const html = `
                    <div id="bobot-panel" class="card mb-3 border shadow-none" style="border-color: rgba(105,108,255,.15) !important;">
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

            // Fungsi inti render data mhs
            async function loadInputNilai(jadwalId) {
                const tableBody = document.querySelector("#table-mahasiswa tbody");
                const saveButton = document.getElementById("save-nilai");
                const panel = document.getElementById("panel-penilaian");

                // Reset state
                $(panel).slideDown();
                setTimeout(() => window.scrollTo({ top: panel.offsetTop - 70, behavior: 'smooth' }), 300);

                tableBody.innerHTML = `<tr><td colspan="9" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span>Menarik data mahasiswa...</td></tr>`;
                saveButton.style.display = "none";
                $('#bobot-panel-container').empty();
                konfigurasiNilai = { bobot: {}, mutu: [] }; // Reset konfigurasi

                const requestUrl = `/dosen/input-nilai-dosen/jadwal/${jadwalId}`;
                try {
                    const response = await fetch(requestUrl);
                    if (!response.ok) throw new Error(`HTTP error! Status: ${response.status} ${response.statusText}`);
                    const data = await response.json();

                    if (data?.message) {
                        showTableMessage(data.message);
                        return;
                    }

                    if (data.mahasiswa && data.konfigurasi) {
                        konfigurasiNilai = data.konfigurasi;
                        document.getElementById('nilai-jadwal-id').value = konfigurasiNilai.jadwal_id;

                        renderBobotPanel(konfigurasiNilai.bobot, konfigurasiNilai);
                        updateTableHeader(konfigurasiNilai.bobot);

                        const daftarMahasiswa = data.mahasiswa;

                        if (daftarMahasiswa.length > 0) {
                            tableBody.innerHTML = daftarMahasiswa.map((mhs, index) => `
                                <tr>
                                    <td class="text-center">${index + 1}</td>
                                    <td><span class="fw-bold text-dark">${mhs.nama}</span><br><small class="text-muted">${mhs.nim}</small></td>
                                    <td>
                                        <input type="hidden" name="krs_id[${mhs.mahasiswa_id}]" value="${mhs.krs_id ?? ''}">
                                        <input type="number" step="0.01" name="uts[${mhs.mahasiswa_id}]" class="form-control form-control-sm nilai-input text-center" value="${mhs.uts ?? ''}" data-id="${mhs.mahasiswa_id}" min="0" max="100">
                                    </td>
                                    <td><input type="number" step="0.01" name="uas[${mhs.mahasiswa_id}]" class="form-control form-control-sm nilai-input text-center" value="${mhs.uas ?? ''}" data-id="${mhs.mahasiswa_id}" min="0" max="100"></td>
                                    <td><input type="number" step="0.01" name="tugas[${mhs.mahasiswa_id}]" class="form-control form-control-sm nilai-input text-center" value="${mhs.tugas ?? ''}" data-id="${mhs.mahasiswa_id}" min="0" max="100"></td>
                                    <td><input type="number" step="0.01" name="absensi[${mhs.mahasiswa_id}]" class="form-control form-control-sm nilai-input text-center" value="${mhs.absen ?? ''}" data-id="${mhs.mahasiswa_id}" min="0" max="100"></td>
                                    <td><input type="number" step="0.01" name="praktik[${mhs.mahasiswa_id}]" class="form-control form-control-sm nilai-input text-center" value="${mhs.praktik ?? ''}" data-id="${mhs.mahasiswa_id}" min="0" max="100"></td>
                                    <td class="text-center align-middle">
                                        <input type="text" name="akhir[${mhs.mahasiswa_id}]" class="form-control form-control-sm text-center fw-bold bg-transparent border-0 akhir-input" id="akhir-${mhs.mahasiswa_id}" readonly>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge bg-label-primary px-3 fs-6" id="khs-${mhs.mahasiswa_id}">-</span>
                                    </td>
                                </tr>
                            `).join("");

                            // Kalkulasi awal
                            daftarMahasiswa.forEach((mhs) => hitungNilaiAkhirDanKhs(mhs.mahasiswa_id));
                            saveButton.style.display = "block";
                        } else {
                            showTableMessage("Tidak ada mahasiswa yang terdaftar pada mata kuliah ini.");
                        }
                    } else {
                        throw new Error("Format data dari server tidak sesuai.");
                    }
                } catch (error) {
                    console.error("Gagal memuat data:", error);
                    showTableMessage(`Gagal memuat data. Error: ${error.message}`);
                }
            }

            // Event listener ganti bobot panel toggle
            $(document).on("click", "#btn-edit-bobot", function() {
                $('#bobot-display').hide();
                $('#bobot-edit').slideDown();
            });
            $(document).on("click", "#btn-cancel-bobot", function() {
                $('#bobot-edit').slideUp();
                setTimeout(() => $('#bobot-display').show(), 300);
            });

            // Simpan bobot ke server via AJAX
            $(document).on("click", "#btn-save-bobot", async function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="bx bx-loader-alt bx-spin me-1"></i>Menyimpan...');

                const newBobot = {
                    program_studi_id: konfigurasiNilai.program_studi_id,
                    matakuliah_id: konfigurasiNilai.matakuliah_id,
                    persen_uts: parseFloat($('#bobot-uts').val()) || 0,
                    persen_uas: parseFloat($('#bobot-uas').val()) || 0,
                    persen_tugas: parseFloat($('#bobot-tugas').val()) || 0,
                    persen_absen: parseFloat($('#bobot-absen').val()) || 0,
                    persen_praktik: parseFloat($('#bobot-praktik').val()) || 0,
                };

                try {
                    const response = await fetch("{{ route('dosen.bobot-nilai.save') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            Accept: "application/json",
                        },
                        body: JSON.stringify(newBobot)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Update global bobot variable
                        konfigurasiNilai.bobot = {
                            uts: newBobot.persen_uts,
                            uas: newBobot.persen_uas,
                            tugas: newBobot.persen_tugas,
                            absensi: newBobot.persen_absen,
                            praktik: newBobot.persen_praktik,
                        };
                        konfigurasiNilai.bobot_source = 'custom';

                        // Rerender the panel with new values
                        renderBobotPanel(konfigurasiNilai.bobot, konfigurasiNilai);
                        updateTableHeader(konfigurasiNilai.bobot);

                        // Recalculate ALL values in the table dynamically based on new bobot!
                        $(".nilai-input").first().trigger("input"); // This triggers one row
                        // Actually let's just loop over all inputs
                        $(".akhir-input").each(function() {
                            hitungNilaiAkhirDanKhs($(this).attr("id").split("-")[1]);
                        });

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Berhasil!', text: 'Bobot nilai berhasil diperbarui!', icon: 'success', timer: 2000, showConfirmButton: false });
                        } else {
                            alert('Bobot nilai berhasil diperbarui!');
                        }
                    } else {
                        alert(data.message || 'Gagal menyimpan bobot');
                    }
                } catch (error) {
                    console.error("Error:", error);
                    alert("Terjadi kesalahan sistem saat menyimpan bobot nilai.");
                } finally {
                    btn.prop('disabled', false).html('<i class="bx bx-save me-1"></i>Simpan');
                }
            });

            // Event listener untuk input nilai
            $(tableBody).on("input", ".nilai-input", function () {
                const mahasiswaId = $(this).data("id");
                hitungNilaiAkhirDanKhs(mahasiswaId);
            });

            // Handle Submit Form Nilai
            document.getElementById("form-nilai").addEventListener("submit", async function (e) {
                e.preventDefault();

                const form = e.target;
                const formData = new FormData(form);

                saveButton.disabled = true;
                saveButton.textContent = "Menyimpan...";

                try {
                    const response = await fetch(form.action, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                            Accept: "application/json",
                        },
                        body: formData,
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({ title: 'Berhasil!', text: data.message || "Data berhasil disimpan!", icon: 'success', timer: 2000, showConfirmButton: false });
                        } else {
                            alert(data.message || "Data berhasil disimpan!");
                        }
                    } else {
                        alert(data.message || "Gagal menyimpan data.");
                    }
                } catch (error) {
                    console.error("Error saving data:", error);
                    alert("Terjadi kesalahan saat menyimpan data. Silakan coba lagi.");
                } finally {
                    saveButton.disabled = false;
                    saveButton.textContent = "Simpan Semua Nilai";
                }
            });
        });
    </script>

    </script>
</body>

</html>
