@extends('layouts.master')
@section('title', 'Data Mahasiswa')
@section('content')

<!-- Header Info dan Import -->
<div class="card shadow-sm mb-4 border-top border-5 border-success">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-8">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-success mb-3 fw-bold">
                    <i class="bx bx-import me-2"></i>Import Data Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah manajemen seluruh data mahasiswa yang terdaftar di sistem terpusat. Anda dapat melakukan import data mahasiswa secara massal dengan mengunggah file spreadsheet (.xlsx atau .csv) yang berisi biodata mahasiswa baru.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <!-- Import Form -->
                    <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- File Upload Section -->
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="file" class="form-label fw-bold text-dark">Upload File Excel</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".xlsx, .csv" required>
                                <small class="text-muted"><i class="bx bx-info-circle"></i> Pastikan file berformat sesuai template</small>
                            </div>

                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bx bx-upload me-1"></i> Mulai Import
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.mahasiswa.download-template') }}" class="btn btn-outline-info w-100" title="Download Template Excel Kosong">
                                    <i class="bx bx-download me-1"></i> Template
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-4 text-center d-none d-md-block">
            <div class="p-3">
                <img src="../assets/img/illustrations/mahasiswa-2.png" class="img-fluid"
                    alt="Illustration of students" style="max-height: 180px;">
            </div>
        </div>
    </div>
</div>

<!-- Main Data Board -->
<div class="card border-top border-5 border-primary shadow-sm">
    <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title text-primary fw-bold mb-0">Direktori Pencarian Mahasiswa</h5>
            <small class="text-muted">Gunakan filter di bawah untuk melakukan pencarian spesifik.</small>
        </div>
    </div>

    <div class="card-body mt-4">
        <div class="bg-label-primary p-4 rounded mb-4">
            <div class="row g-3">
                <!-- Input Search -->
                <div class="col-md-4">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-search-alt"></i> Kata Kunci</label>
                    <input type="text" id="search" class="form-control border-primary text-primary" placeholder="Ketik Nama / NIM...">
                </div>

                <!-- Program Studi -->
                <div class="col-md-2">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-book"></i> Program Studi</label>
                    <select id="program-studi" class="form-select border-primary text-primary">
                        <option value="">Semua Program Studi</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tahun Masuk -->
                <div class="col-md-2">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-calendar-event"></i> Tahun Masuk</label>
                    <select id="tahun-masuk" class="form-select border-primary text-primary">
                        <option value="">Semua Tahun</option>
                        @for ($year = 2019; $year <= date('Y'); $year++)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-2">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-user-check"></i> Status Mahasiswa</label>
                    <select id="status" class="form-select border-primary text-primary">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Nonaktif">Nonaktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Dropout">Dropout</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-group"></i> Kelas</label>
                    <select id="kelas" class="form-select border-primary text-primary">
                        <option value="">Semua Kelas</option>
                        <option value="pagi">Reguler A</option>
                        <option value="karyawan">Reguler B</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="row mt-3 justify-content-end">
                <div class="col-auto">
                    <button id="search-btn" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="bx bx-search me-1"></i> Terapkan Filter
                    </button>
                    <button id="export-btn" class="btn btn-outline-success px-4 ms-2 fw-bold shadow-sm">
                        <i class="bx bx-file me-1"></i> Export Excel
                        <input type="hidden" id="export-url" value="{{ route('admin.export') }}">
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <div id="alert-container"></div>

        @can('mahasiswa-edit')
        <div class="card border shadow-none mb-3">
            <div class="card-body py-3">
                <div class="row align-items-end g-2">
                    <div class="col-md-8">
                        <label for="bulk-kelas" class="form-label fw-semibold mb-1">Klasifikasi mahasiswa terpilih</label>
                        <select id="bulk-kelas" class="form-select">
                            <option value="">-- Pilih kelas tujuan --</option>
                            <option value="pagi">Reguler A</option>
                            <option value="karyawan">Reguler B</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="button" id="bulk-kelas-btn" class="btn btn-primary w-100">
                            <i class="bx bx-group me-1"></i> Terapkan Kelas Massal
                        </button>
                    </div>
                </div>
                <small class="text-muted">Centang mahasiswa pada tabel, pilih kelas, lalu terapkan.</small>
            </div>
        </div>
        @endcan

        <!-- Tabel Hasil Pencarian -->
        <div class="table-responsive" id="table-container" style="min-height: 250px;">
            <div class="text-center py-5">
                <i class="bx bx-table text-muted mb-3" style="font-size: 3rem;"></i><br>
                <span class="text-muted fw-semibold">Silakan klik "Terapkan Filter" untuk mulai menampilkan data mahasiswa.</span>
            </div>
        </div>
    </div>
</div>
@push('script')
<script>
$(document).ready(function () {
    // Fungsi untuk Fetch Data Mahasiswa
    function fetchMahasiswa(url) {
        var search = $('#search').val();
        var programStudi = $('#program-studi').val();
        var tahunMasuk = $('#tahun-masuk').val();
        var status = $('#status').val();
        var kelas = $('#kelas').val();

        // Menampilkan loading indicator
        $('#table-container').html('<div class="text-center my-3"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div> Memuat data...</div>');

        $.ajax({
            url: url,
            type: 'GET',
            data: {
                search: search,
                jurusan_id: programStudi,
                tahun_masuk: tahunMasuk,
                status: status,
                kelas: kelas
            },
            dataType: 'json',
            success: function (response) {
                $('#table-container').html(response.html);
                $('#pagination-container').html(response.pagination);
            },
            error: function (xhr) {
                $('#table-container').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data.</div>');
                console.error(xhr.responseText);
            }
        });
    }

    // Event ketika tombol cari ditekan
    $('#search-btn').on('click', function () {
        fetchMahasiswa("{{ route('admin.mahasiswa.index') }}");
    });

    $(document).on('change', '#select-all-mahasiswa', function () {
        $('.mahasiswa-checkbox').prop('checked', this.checked);
    });

    $(document).on('change', '.kelas-dropdown', function () {
        const selectElement = $(this);
        const mahasiswaId = selectElement.closest('tr').data('id');
        const previousValue = selectElement.data('previous');

        $.ajax({
            url: `/admin/mahasiswa/${mahasiswaId}/update-kelas`,
            type: 'PUT',
            data: {
                _token: "{{ csrf_token() }}",
                kelas: selectElement.val()
            },
            beforeSend: function () {
                selectElement.prop('disabled', true);
            },
            success: function (response) {
                selectElement.data('previous', response.kelas);
                showStatusAlert('success', response.message);
            },
            error: function (xhr) {
                selectElement.val(previousValue);
                showStatusAlert('danger', xhr.responseJSON?.message || 'Gagal memperbarui kelas mahasiswa.');
            },
            complete: function () {
                selectElement.prop('disabled', false);
            }
        });
    });

    $('#bulk-kelas-btn').on('click', function () {
        const mahasiswaIds = $('.mahasiswa-checkbox:checked').map(function () {
            return Number(this.value);
        }).get();
        const kelas = $('#bulk-kelas').val();

        if (mahasiswaIds.length === 0 || !kelas) {
            showStatusAlert('warning', 'Pilih mahasiswa dan kelas tujuan terlebih dahulu.');
            return;
        }

        if (!confirm(`Ubah kelas ${mahasiswaIds.length} mahasiswa yang dipilih?`)) {
            return;
        }

        const button = $(this);
        $.ajax({
            url: "{{ route('admin.mahasiswa.bulkUpdateKelas') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                mahasiswa_ids: mahasiswaIds,
                kelas: kelas
            },
            beforeSend: function () {
                button.prop('disabled', true);
            },
            success: function (response) {
                showStatusAlert('success', response.message);
                fetchMahasiswa("{{ route('admin.mahasiswa.index') }}");
            },
            error: function (xhr) {
                showStatusAlert('danger', xhr.responseJSON?.message || 'Gagal memperbarui kelas mahasiswa.');
            },
            complete: function () {
                button.prop('disabled', false);
            }
        });
    });

    // Event ketika menekan Enter di input pencarian
    $('#search').on('keypress', function (e) {
        if (e.which === 13) { // 13 = Enter
            fetchMahasiswa("{{ route('admin.mahasiswa.index') }}");
        }
    });

    // Event untuk pagination menggunakan event delegation
    $(document).on('click', '#pagination-container a', function (e) {
        e.preventDefault();
        var url = $(this).attr('href');
        if (url) {
            fetchMahasiswa(url);
        }
    });

    // Status Dropdown Event
    $(document).on("change", ".status-dropdown", function () {
        let selectElement = $(this);
        let mahasiswaRow = selectElement.closest("tr");
        let mahasiswaId = mahasiswaRow.data("id");
        let newStatus = selectElement.val();
        let statusBadge = mahasiswaRow.find(".status-badge");

        $.ajax({
            url: `/admin/mahasiswa/${mahasiswaId}/update-status`,
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

    // Dosen Dropdown Event
    $(document).on("change", ".dosen-dropdown", function () {
        let selectElement = $(this);
        let mahasiswaRow = selectElement.closest("tr");
        let mahasiswaId = mahasiswaRow.data("id");
        let newDosen = selectElement.val();

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
        if (alertBox.length === 0) {
            $('#alert-container').html(`<div id="status-alert" class="alert alert-${type} d-block">${message}</div>`);
            setTimeout(() => $("#status-alert").removeClass("d-block").addClass("d-none"), 5000);
        } else {
            alertBox.removeClass().addClass(`alert alert-${type} d-block`).html(message);
            setTimeout(() => alertBox.removeClass("d-block").addClass("d-none"), 5000);
        }
    }

    // Export Button Event
    $('#export-btn').on('click', function (e) {
        e.preventDefault();

        const baseUrl = $('#export-url').val();
        const search = $('#search').val();
        const programStudi = $('#program-studi').val();
        const tahunMasuk = $('#tahun-masuk').val();
        const status = $('#status').val();
        const kelas = $('#kelas').val();

        if (!search && !programStudi && !tahunMasuk && !status && !kelas) {
            if (!confirm("Tidak ada filter diterapkan. Apakah Anda yakin ingin mengekspor semua data mahasiswa?")) {
                return;
            }
        }

        const exportUrl = `${baseUrl}?search=${encodeURIComponent(search)}&jurusan_id=${programStudi}&tahun_masuk=${tahunMasuk}&status=${status}&kelas=${kelas}`;

        window.location.href = exportUrl;
    });
});
</script>
@endpush
@endsection
