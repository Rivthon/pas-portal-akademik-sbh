@extends('layouts.master')

@section('title', 'Jadwal UTS')

@section('content')

<!-- Header Info -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Manajemen Jadwal UTS
                </h5>

                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Halaman ini berisi informasi penetapan jadwal Ujian Tengah
                    Semester (UTS) mahasiswa. Anda dapat mengatur ruangan ujian,
                    tanggal, serta jam ujian.

                    Jadwal akan disesuaikan dengan kurikulum program studi yang
                    bersangkutan.

                    <br>

                    <span class="badge bg-label-primary mt-2 fs-6">
                        Tahun Ajaran {{ $tahunAjaran->nama }}
                        ({{ $tahunAjaran->semester }})
                    </span>
                </p>
            </div>

            <div class="col-md-5 text-center">
                <img
                    src="{{ asset('assets/img/illustrations/calender.png') }}"
                    class="img-fluid"
                    alt="Illustration"
                    style="max-height: 150px;"
                >
            </div>
        </div>
    </div>
</div>

@can('jadwal-uts-create')
<!-- Import Jadwal -->
<div class="card shadow-sm mb-4 border-top border-5 border-success">
    <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title text-success mb-0">
                <i class="bx bx-import me-1"></i>
                Tarik Data Jadwal UTS
            </h5>

            <small class="text-muted">
                Generate data awal formulir jadwal UTS berdasarkan mata kuliah aktif.
            </small>
        </div>
    </div>

    <div class="card-body mt-3">
        <form
            id="generateForm"
            action="{{ route('admin.jadwal-uts.generate') }}"
            method="POST"
        >
            @csrf

            <div class="row align-items-end bg-light p-3 rounded">
                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="jurusan_id" class="form-label fw-semibold">
                        Pilih Program Studi
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="jurusan_id"
                        id="jurusan_id"
                        class="form-select"
                        required
                    >
                        <option value="">-- Pilih Program Studi --</option>

                        @foreach ($programStudi as $j)
                            <option value="{{ $j->jurusan_id }}">
                                {{ $j->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="jenis_kelas" class="form-label fw-semibold">
                        Pilih Jenis Kelas
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="jenis_kelas"
                        id="jenis_kelas"
                        class="form-select"
                        required
                    >
                        <option value="Reguler">Reguler A</option>
                        <option value="Karyawan">Reguler B</option>
                    </select>
                </div>

                <div class="col-md-2 mt-3 mt-md-0 d-grid">
                    <button
                        type="button"
                        id="submitBtngenerate"
                        class="btn btn-success"
                    >
                        <i class="bx bx-download me-1"></i>
                        Tarik Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- Filter dan Pencarian -->
<div class="card shadow-sm mt-4 border-top border-5 border-primary">
    <div class="card-header bg-white pb-0">
        <h5 class="card-title text-primary mb-0">
            <i class="bx bx-search me-1"></i>
            Cari & Edit Jadwal UTS
        </h5>
    </div>

    <div class="card-body">
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label for="program-studi" class="form-label fw-bold">
                    Pilih Program Studi
                    <span class="text-danger">*</span>
                </label>

                <select id="program-studi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>

                    @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">
                            {{ $ps->nama }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="semester" class="form-label fw-bold">
                    Pilih Semester
                    <span class="text-danger">*</span>
                </label>

                <select id="semester" class="form-select">
                    <option value="">-- Pilih Semester --</option>

                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">
                            Semester {{ $i }}
                        </option>
                    @endfor
                </select>
            </div>

            <div class="col-md-4 mb-3">
                <label for="jenis_kelas_cari" class="form-label fw-bold">
                    Pilih Jenis Kelas
                    <span class="text-danger">*</span>
                </label>

                <select id="jenis_kelas_cari" class="form-select">
                    <option value="Reguler">Reguler A</option>
                    <option value="Karyawan">Reguler B</option>
                </select>
            </div>
        </div>

        <div class="mt-2 text-end">
            <button id="search-btn" class="btn btn-primary px-4">
                <i class="bx bx-search-alt me-1"></i>
                Tampilkan Jadwal
            </button>
        </div>

        <div id="alert-container" class="mt-3"></div>
    </div>
</div>

<!-- Hasil Jadwal -->
<div class="card mt-4 mb-5">
    <div class="card-body">
        <h5 class="card-title">
            <i class="bx bx-table me-1"></i>
            List Jadwal UTS
        </h5>

        <div
            id="loading"
            class="text-center my-4"
            style="display: none;"
        >
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>

            <div class="mt-2 text-muted">
                Memuat data jadwal...
            </div>
        </div>

        <div class="table-responsive text-nowrap">
            <table
                id="jadwal-table"
                class="table table-hover table-bordered mt-3"
                style="display: none;"
            >
                <thead class="table-primary border-bottom">
                    <tr>
                        <th class="text-center" style="width: 5%;">#</th>
                        <th style="width: 25%;">Mata Kuliah</th>
                        <th class="text-center" style="width: 5%;">SMT</th>
                        <th style="width: 14%;">Tanggal</th>
                        <th style="width: 13%;">Jam Mulai</th>
                        <th style="width: 13%;">Jam Selesai</th>
                        <th style="width: 15%;">Ruangan</th>
                        <th class="text-center" style="width: 5%;">Kelas</th>
                        <th class="text-center" style="width: 5%;">Aksi</th>
                    </tr>
                </thead>

                <tbody class="table-border-bottom-0">
                    <!-- Data dimuat melalui AJAX -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const filterUrl = @json(route('admin.jadwal-uts.filter'));
        const updateBaseUrl = @json(url('/admin/jadwal-uts/update'));
        const deleteBaseUrl = @json(url('/admin/jadwal-uts/delete'));
        const csrfToken = @json(csrf_token());

        const daftarRuangan = @json(
            $ruangan->map(function ($item) {
                return [
                    'id' => $item->ruangan_id,
                    'nama' => $item->nama,
                ];
            })->values()
        );

        const searchButton = document.getElementById('search-btn');
        const alertContainer = document.getElementById('alert-container');
        const loading = document.getElementById('loading');
        const table = document.getElementById('jadwal-table');
        const tbody = table.querySelector('tbody');

        /**
         * Menampilkan pesan pada bagian filter.
         */
        function tampilkanAlert(type, message) {
            alertContainer.innerHTML = `
                <div class="alert alert-${type}">
                    ${escapeHtml(message)}
                </div>
            `;
        }

        /**
         * Mencegah nilai dari server dimasukkan sebagai HTML berbahaya.
         */
        function escapeHtml(value) {
            if (value === null || value === undefined) {
                return '';
            }

            return String(value)
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        /**
         * Memastikan format tanggal sesuai input type="date".
         */
        function formatTanggal(value) {
            if (!value) {
                return '';
            }

            return String(value).substring(0, 10);
        }

        /**
         * Memastikan format jam sesuai input type="time".
         */
        function formatJam(value) {
            if (!value) {
                return '';
            }

            return String(value).substring(0, 5);
        }

        /**
         * Membuat daftar pilihan ruangan.
         */
        function buatRuanganOptions(ruanganId) {
            let options = '<option value="">-- Pilih --</option>';

            daftarRuangan.forEach(function (ruangan) {
                const selected =
                    String(ruangan.id) === String(ruanganId)
                        ? 'selected'
                        : '';

                options += `
                    <option
                        value="${escapeHtml(ruangan.id)}"
                        ${selected}
                    >
                        ${escapeHtml(ruangan.nama)}
                    </option>
                `;
            });

            return options;
        }

        /**
         * Mengambil daftar jadwal sesuai filter.
         */
        async function loadJadwal() {
            const programStudi =
                document.getElementById('program-studi').value;

            const semester =
                document.getElementById('semester').value;

            const jenisKelas =
                document.getElementById('jenis_kelas_cari').value;

            alertContainer.innerHTML = '';
            table.style.display = 'none';
            tbody.innerHTML = '';

            if (!programStudi || !semester || !jenisKelas) {
                tampilkanAlert(
                    'warning',
                    'Harap pilih Program Studi, Semester, dan Jenis Kelas terlebih dahulu.'
                );

                return;
            }

            loading.style.display = 'block';
            searchButton.disabled = true;

            try {
                const params = new URLSearchParams({
                    programStudi: programStudi,
                    semester: semester,
                    jenis_kelas: jenisKelas
                });

                const response = await fetch(
                    `${filterUrl}?${params.toString()}`,
                    {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    }
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'Data jadwal gagal diambil.'
                    );
                }

                const data = Array.isArray(result)
                    ? result
                    : result.data || [];

                if (data.length === 0) {
                    tampilkanAlert(
                        'info',
                        'Tidak ada jadwal UTS yang ditemukan.'
                    );

                    return;
                }

                data.forEach(function (jadwal, index) {
                    const tanggal = formatTanggal(jadwal.tanggal);
                    const jamMulai = formatJam(jadwal.jam_mulai);
                    const jamSelesai = formatJam(jadwal.jam_selesai);

                    const row = document.createElement('tr');

                    row.dataset.id = jadwal.id;

                    row.innerHTML = `
                        <td class="text-center">
                            ${index + 1}
                        </td>

                        <td class="fw-semibold text-wrap">
                            ${escapeHtml(jadwal.nama_matakuliah)}
                        </td>

                        <td class="text-center">
                            ${escapeHtml(jadwal.semester)}
                        </td>

                        <td>
                            <input
                                type="date"
                                class="form-control form-control-sm update-field date-field"
                                data-field="tanggal"
                                value="${escapeHtml(tanggal)}"
                                autocomplete="off"
                            >

                        </td>

                        <td>
                            <input
                                type="time"
                                class="form-control form-control-sm update-field"
                                data-field="jam_mulai"
                                value="${escapeHtml(jamMulai)}"
                            >
                        </td>

                        <td>
                            <input
                                type="time"
                                class="form-control form-control-sm update-field"
                                data-field="jam_selesai"
                                value="${escapeHtml(jamSelesai)}"
                            >
                        </td>

                        <td>
                            <select
                                class="form-select form-select-sm update-field"
                                data-field="ruangan_id"
                            >
                                ${buatRuanganOptions(jadwal.ruangan_id)}
                            </select>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-label-warning">
                                ${escapeHtml(String(jadwal.jenis_kelas).toLowerCase() === 'karyawan' ? 'Reguler B' : 'Reguler A')}
                            </span>
                        </td>

                        <td class="text-center">
                            <button
                                type="button"
                                class="btn btn-outline-danger btn-sm delete-btn"
                                data-id="${escapeHtml(jadwal.id)}"
                                title="Hapus Jadwal"
                            >
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    `;

                    tbody.appendChild(row);
                });

                table.style.display = 'table';
            } catch (error) {
                tampilkanAlert(
                    'warning',
                    error.message ||
                    'Terjadi kesalahan saat mengambil data jadwal UTS.'
                );

                console.error('Load jadwal error:', error);
            } finally {
                loading.style.display = 'none';
                searchButton.disabled = false;
            }
        }

        searchButton.addEventListener('click', loadJadwal);

        /**
         * Menyimpan nilai awal ketika field mulai diedit.
         */
        document.addEventListener('focusin', function (event) {
            const element = event.target;

            if (!element.classList.contains('update-field')) {
                return;
            }

            element.dataset.previousValue = element.value;
        });

        /**
         * Jam dan ruangan langsung tersimpan saat berubah.
         *
         * Tanggal tidak disimpan melalui event change karena pada beberapa
         * browser event change dapat terpanggil ketika pengguna masih
         * mengisi bagian tanggal, bulan, atau tahun.
         */
        document.addEventListener('change', async function (event) {
            const element = event.target;

            if (!element.classList.contains('update-field')) {
                return;
            }

            const field = element.dataset.field;

            if (field === 'tanggal') {
                return;
            }

            await prosesUpdateField(element);
        });

        /**
         * Tanggal hanya disimpan setelah pengguna selesai mengisi
         * dan keluar dari kolom tanggal.
         */
        document.addEventListener('focusout', async function (event) {
            const element = event.target;

            if (!element.classList.contains('update-field')) {
                return;
            }

            if (element.dataset.field !== 'tanggal') {
                return;
            }

            const value = element.value;
            const previousValue =
                element.dataset.previousValue ?? '';

            if (value === previousValue) {
                return;
            }

            if (!value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Belum Diisi',
                    text: 'Silakan isi tanggal, bulan, dan tahun secara lengkap.'
                });

                element.value = previousValue;
                return;
            }

            if (!validasiTanggal(value)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Tidak Valid',
                    text: 'Silakan masukkan tanggal, bulan, dan tahun yang valid.'
                });

                element.value = previousValue;
                return;
            }

            await prosesUpdateField(element);
        });

        /**
         * Validasi tanggal dengan format YYYY-MM-DD.
         */
        function validasiTanggal(value) {
            const pattern = /^\d{4}-\d{2}-\d{2}$/;

            if (!pattern.test(value)) {
                return false;
            }

            const [year, month, day] =
                value.split('-').map(Number);

            const date = new Date(year, month - 1, day);

            return (
                date.getFullYear() === year &&
                date.getMonth() === month - 1 &&
                date.getDate() === day
            );
        }

        /**
         * Memproses penyimpanan field.
         */
        async function prosesUpdateField(element) {
            if (element.dataset.saving === 'true') {
                return;
            }

            const row = element.closest('tr');

            if (!row) {
                return;
            }

            const id = row.dataset.id;
            const field = element.dataset.field;
            const value = element.value;
            const previousValue =
                element.dataset.previousValue ?? '';

            const originalBackground =
                element.style.backgroundColor;

            element.dataset.saving = 'true';
            element.disabled = true;
            element.style.backgroundColor = '#fff3cd';

            const berhasil = await updateJadwalAjax(
                id,
                field,
                value,
                element,
                originalBackground
            );

            element.dataset.saving = 'false';

            if (berhasil) {
                element.dataset.previousValue = value;
            } else {
                element.value = previousValue;
            }
        }

        /**
         * Mengirim pembaruan jadwal melalui AJAX.
         */
        async function updateJadwalAjax(
            id,
            field,
            value,
            element,
            originalBackground
        ) {
            try {
                const response = await fetch(
                    `${updateBaseUrl}/${id}`,
                    {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            field: field,
                            value: value
                        })
                    }
                );

                let data;

                try {
                    data = await response.json();
                } catch (error) {
                    data = {
                        success: false,
                        message: 'Respons server tidak valid.'
                    };
                }

                element.disabled = false;

                if (!response.ok || !data.success) {
                    element.style.backgroundColor = '#f8d7da';

                    const validationMessage =
                        ambilPesanValidasi(data);

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text:
                            validationMessage ||
                            data.message ||
                            'Data gagal disimpan.'
                    });

                    setTimeout(function () {
                        element.style.backgroundColor =
                            originalBackground;
                    }, 1500);

                    return false;
                }

                element.style.backgroundColor = '#d1e7dd';

                setTimeout(function () {
                    element.style.backgroundColor =
                        originalBackground;
                }, 1000);

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Disimpan',
                    text:
                        data.message ||
                        'Perubahan berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 1800
                });

                return true;
            } catch (error) {
                console.error('Update jadwal error:', error);

                element.disabled = false;
                element.style.backgroundColor = '#f8d7da';

                Swal.fire({
                    icon: 'error',
                    title: 'Error Server',
                    text: 'Terjadi kegagalan jaringan saat menyimpan data.'
                });

                setTimeout(function () {
                    element.style.backgroundColor =
                        originalBackground;
                }, 1500);

                return false;
            }
        }

        /**
         * Mengambil pesan pertama dari validation errors Laravel.
         */
        function ambilPesanValidasi(data) {
            if (!data.errors) {
                return null;
            }

            const firstKey = Object.keys(data.errors)[0];

            if (!firstKey) {
                return null;
            }

            const messages = data.errors[firstKey];

            return Array.isArray(messages)
                ? messages[0]
                : messages;
        }

        /**
         * Menangani tombol hapus.
         */
        document.addEventListener('click', function (event) {
            const button = event.target.closest('.delete-btn');

            if (!button) {
                return;
            }

            const row = button.closest('tr');
            const id = button.dataset.id;

            Swal.fire({
                title: 'Hapus Jadwal?',
                text: 'Baris jadwal ini akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then(function (result) {
                if (result.isConfirmed) {
                    deleteJadwalUTS(id, row, button);
                }
            });
        });

        /**
         * Menghapus jadwal melalui AJAX.
         */
        async function deleteJadwalUTS(id, row, button) {
            button.disabled = true;

            try {
                const response = await fetch(
                    `${deleteBaseUrl}/${id}`,
                    {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    }
                );

                const data = await response.json();

                if (!response.ok || !data.success) {
                    throw new Error(
                        data.message || 'Jadwal gagal dihapus.'
                    );
                }

                row.remove();

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Berhasil',
                    text:
                        data.message ||
                        'Jadwal berhasil dihapus.',
                    showConfirmButton: false,
                    timer: 2000
                });

                if (tbody.children.length === 0) {
                    table.style.display = 'none';

                    tampilkanAlert(
                        'info',
                        'Tidak ada jadwal UTS yang tersedia.'
                    );
                } else {
                    perbaruiNomorUrut();
                }
            } catch (error) {
                button.disabled = false;

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal Menghapus',
                    text:
                        error.message ||
                        'Terjadi kesalahan saat menghapus jadwal.'
                });
            }
        }

        /**
         * Memperbarui nomor urut setelah data dihapus.
         */
        function perbaruiNomorUrut() {
            tbody.querySelectorAll('tr').forEach(function (row, index) {
                const nomorCell = row.querySelector('td:first-child');

                if (nomorCell) {
                    nomorCell.textContent = index + 1;
                }
            });
        }

        /**
         * Konfirmasi tarik data jadwal.
         */
        const generateButton =
            document.getElementById('submitBtngenerate');

        if (generateButton) {
            generateButton.addEventListener('click', function () {
                const jurusan =
                    document.getElementById('jurusan_id').value;

                const jenis =
                    document.getElementById('jenis_kelas').value;

                if (!jurusan || !jenis) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Perhatian',
                        text: 'Harap lengkapi Program Studi dan Jenis Kelas.'
                    });

                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Penarikan Data',
                    text: 'Sistem akan membuat baris jadwal UTS berdasarkan mata kuliah pada kurikulum.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ya, Tarik Data',
                    cancelButtonText: 'Batal'
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }

                    Swal.fire({
                        title: 'Memproses...',
                        text: 'Data jadwal sedang dibuat.',
                        allowOutsideClick: false,
                        didOpen: function () {
                            Swal.showLoading();
                        }
                    });

                    document
                        .getElementById('generateForm')
                        .submit();
                });
            });
        }

        @if (session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: @json(session('success')),
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        @endif

        @if (session('error'))
            Swal.fire({
                title: 'Gagal!',
                text: @json(session('error')),
                icon: 'error',
                timer: 4000
            });
        @endif
    });
</script>

@endsection
