@extends('layouts.master')
@section('title', 'Jadwal UAS')
@section('content')

    <!-- Header Info -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <div class="col-md-7">
                    <h5 class="card-title text-primary mb-3 fw-bold">Manajemen Jadwal UAS</h5>
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Halaman ini berisi daftar jadwal Ujian Akhir Semester (UAS) mahasiswa. Atur tanggal, ruangan, dan
                        jam pelaksanaan dengan mudah.
                        Jadwal akan disesuaikan dengan kurikulum program studi yang bersangkutan. <br>
                        <span class="badge bg-label-primary mt-2 fs-6">Tahun Ajaran {{ $tahunAjaran->nama }}
                            ({{ $tahunAjaran->semester }})</span>
                    </p>
                </div>
                <div class="col-md-5 text-center">
                    <img src="../assets/img/illustrations/calender.png" class="img-fluid" alt="Illustration"
                        style="max-height: 150px;">
                </div>
            </div>
        </div>
    </div>

    @can('jadwal-uas-create')
        <!-- IMPORT JADWAL SECTION -->
        <div class="card shadow-sm mb-4 border-top border-5 border-success">
            <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title text-success mb-0"><i class="bx bx-import me-1"></i> Tarik Data Jadwal UAS</h5>
                    <small class="text-muted">Generate baris jadwal ujian akhir secara otomatis berdasar matkul aktif.</small>
                </div>
            </div>
            <div class="card-body mt-3">
                <form id="generateForm" action="{{ route('admin.jadwal-uas.generate') }}" method="POST">
                    @csrf
                    <div class="row align-items-end bg-light p-3 rounded">
                        <div class="col-md-5 mb-2 mb-md-0">
                            <label for="jurusan_id" class="form-label fw-semibold">Pilih Program Studi <span
                                    class="text-danger">*</span></label>
                            <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                                <option value="">-- Pilih Program Studi --</option>
                                @foreach($programStudi as $j)
                                    <option value="{{ $j->jurusan_id }}">{{ $j->nama }}</option>
                                @endforeach
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
                        <div class="col-md-2 mt-3 mt-md-0 d-grid">
                            <button type="button" id="submitBtngenerate" class="btn btn-success"><i
                                    class="bx bx-download me-1"></i> Tarik Data</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    <!-- FILTER & PENCARIAN -->
    <div class="card shadow-sm mt-4 border-top border-5 border-primary">
        <div class="card-header bg-white pb-0">
            <h5 class="card-title text-primary mb-0"><i class="bx bx-search me-1"></i> Cari & Edit Jadwal UAS</h5>
        </div>
        <div class="card-body">
            <div class="row mt-3">
                <div class="col-md-4 mb-3">
                    <label for="program-studi" class="form-label fw-bold">Pilih Program Studi <span
                            class="text-danger">*</span></label>
                    <select id="program-studi" class="form-select">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $ps)
                            <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="semester" class="form-label fw-bold">Pilih Semester <span
                            class="text-danger">*</span></label>
                    <select id="semester" class="form-select">
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 8; $i++)
                            <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="jenis_kelas_cari" class="form-label fw-bold">Pilih Jenis Kelas <span
                            class="text-danger">*</span></label>
                    <select id="jenis_kelas_cari" class="form-select">
                        <option value="reguler">Reguler A</option>
                        <option value="karyawan">Reguler B</option>
                    </select>
                </div>
            </div>

            <div class="mt-2 text-end">
                <button id="search-btn" class="btn btn-primary px-4"><i class="bx bx-search-alt me-1"></i> Tampilkan
                    Jadwal</button>
            </div>

            <div id="alert-container" class="mt-3"></div>
        </div>
    </div>

    <!-- HASIL JADWAL -->
    <div class="card mt-4 mb-5">
        <div class="card-body">
            <h5 class="card-title"><i class="bx bx-table me-1"></i> List Jadwal UAS</h5>
            <div id="loading" class="text-center my-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <div class="mt-2 text-muted">Memuat tabel UAS...</div>
            </div>

            <div class="table-responsive text-nowrap">
                <table id="jadwal-table" class="table table-hover table-bordered mt-3" style="display: none;">
                    <thead class="table-primary border-bottom">
                        <tr>
                            <th class="text-center" style="width: 5%">#</th>
                            <th style="width: 25%">Mata Kuliah</th>
                            <th class="text-center" style="width: 5%">SMT</th>
                            <th style="width: 14%">Tanggal</th>
                            <th style="width: 13%">Jam Mulai</th>
                            <th style="width: 13%">Jam Selesai</th>
                            <th style="width: 15%">Ruangan</th>
                            <th class="text-center" style="width: 5%">Kelas</th>
                            <th class="text-center" style="width: 5%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        <!-- Data ditarik via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('search-btn').addEventListener('click', function () {
            let programStudi = document.getElementById('program-studi').value;
            let semester = document.getElementById('semester').value;
            let jenis_kelas = document.getElementById('jenis_kelas_cari').value;
            let alertContainer = document.getElementById('alert-container');
            let loading = document.getElementById('loading');
            let table = document.getElementById('jadwal-table');
            let tbody = table.querySelector('tbody');

            // Reset
            alertContainer.innerHTML = "";
            table.style.display = "none";
            tbody.innerHTML = "";

            if (!programStudi || !semester || !jenis_kelas) {
                alertContainer.innerHTML = `<div class="alert alert-warning"><i class="bx bx-error-circle me-1"></i> Harap pilih Program Studi, Semester, dan Jenis Kelas terlebih dahulu.</div>`;
                return;
            }

            loading.style.display = "block";

            fetch(`{{ route('admin.jadwal-uas.filter') }}?programStudi=${programStudi}&semester=${semester}&jenis_kelas=${jenis_kelas}`)
                .then(async response => {
                    const data = await response.json();
                    return { ok: response.ok, status: response.status, data };
                })
                .then(({ ok, status, data }) => {
                    loading.style.display = "none";

                    if (!ok) {
                        if (status === 404 && data.message) {
                            alertContainer.innerHTML = `
                                    <div class="alert alert-warning border border-warning alert-dismissible" role="alert">
                                        <h6 class="alert-heading d-flex align-items-center fw-bold mb-1"><i class="bx bxs-error-circle fs-4 me-2"></i> Jadwal Belum Tersedia</h6>
                                        <p class="mb-0">${data.message} <br>Anda dapat menggunakan form <strong>Tarik Data Jadwal UAS</strong> di atas untuk men-generate jadwal secara otomatis.</p>
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </div>`;
                            return;
                        }
                        throw new Error(data.message || 'Terjadi kesalahan sistem.');
                    }

                    if (data.message && (!Array.isArray(data) || data.length === 0)) {
                        alertContainer.innerHTML = `<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i> ${data.message}</div>`;
                        return;
                    }

                    if (!Array.isArray(data)) {
                        data = data.data || [];
                    }

                    if (data.length === 0) {
                        alertContainer.innerHTML = `<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i> Tidak ada jadwal UAS yang ditemukan.</div>`;
                        return;
                    }

                    data.forEach((jadwal, index) => {
                        let ruanganOptions = `<option value="">-- Pilih --</option>`;
                        @foreach($ruangan as $r)
                            ruanganOptions += `<option value="{{ $r->ruangan_id }}" ${"{{ $r->ruangan_id }}" == jadwal.ruangan_id ? 'selected' : ''}>{{ $r->nama }}</option>`;
                        @endforeach

                        let dtnggl = jadwal.tanggal || '';
                        let dMulai = jadwal.jam_mulai || '';
                        let dSelesai = jadwal.jam_selesai || '';

                        let row = `<tr data-id="${jadwal.id}">
                                <td class="text-center">${index + 1}</td>
                                <td class="fw-semibold text-wrap">${jadwal.nama_matakuliah}</td>
                                <td class="text-center">${jadwal.semester}</td>

                                <!-- Editable Tanggal -->
                                <td>
                                    <input type="date" class="form-control form-control-sm update-field" data-field="tanggal" value="${dtnggl}" autocomplete="off" />
                                </td>
                                <!-- Editable Jam Mulai -->
                                <td>
                                    <input type="time" class="form-control form-control-sm update-field" data-field="jam_mulai" value="${dMulai}" />
                                </td>
                                <!-- Editable Jam Selesai -->
                                <td>
                                    <input type="time" class="form-control form-control-sm update-field" data-field="jam_selesai" value="${dSelesai}" />
                                </td>

                                <!-- Editable Ruangan -->
                                <td>
                                    <select class="form-select form-select-sm update-field" data-field="ruangan_id">
                                        ${ruanganOptions}
                                    </select>
                                </td>

                                <td class="text-center">
                                    <span class="badge bg-label-danger">${String(jadwal.jenis_kelas).toLowerCase() === 'karyawan' ? 'Reguler B' : 'Reguler A'}</span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-outline-danger btn-sm delete-btn" data-id="${jadwal.id}" title="Hapus">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </td>
                            </tr>`;

                        tbody.innerHTML += row;
                    });

                    table.style.display = "table";
                })
                .catch(error => {
                    loading.style.display = "none";

                    alertContainer.innerHTML = `
            <div class="alert alert-danger">
                <i class="bx bx-x-circle me-1"></i>
                ${error.message || 'Terjadi kesalahan saat mengambil data jadwal UAS.'}
            </div>
        `;

                    console.error('Error:', error);
                });
        });

        // Simpan nilai awal saat field mulai diedit
        document.addEventListener('focusin', function (e) {
            if (e.target.classList.contains('update-field')) {
                e.target.dataset.previousValue = e.target.value;
            }
        });

        // Jam dan ruangan langsung tersimpan saat berubah.
        // Tanggal tidak diproses di event change agar tidak tersimpan saat masih diketik.
        document.addEventListener('change', async function (e) {
            if (!e.target.classList.contains('update-field')) {
                return;
            }

            const field = e.target.getAttribute('data-field');

            if (field === 'tanggal') {
                return;
            }

            await prosesUpdateField(e.target);
        });

        // Tanggal baru disimpan saat pengguna selesai mengisi dan keluar dari kolom.
        document.addEventListener('focusout', async function (e) {
            if (!e.target.classList.contains('update-field')) {
                return;
            }

            if (e.target.getAttribute('data-field') !== 'tanggal') {
                return;
            }

            const value = e.target.value;
            const previousValue = e.target.dataset.previousValue || '';

            if (value === previousValue) {
                return;
            }

            if (!value || !validasiTanggal(value)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Tanggal Belum Lengkap',
                    text: 'Silakan isi tanggal, bulan, dan tahun secara lengkap.'
                });

                e.target.value = previousValue;
                return;
            }

            await prosesUpdateField(e.target);
        });

        function validasiTanggal(value) {
            const pattern = /^\d{4}-\d{2}-\d{2}$/;

            if (!pattern.test(value)) {
                return false;
            }

            const [year, month, day] = value.split('-').map(Number);
            const date = new Date(year, month - 1, day);

            return date.getFullYear() === year
                && date.getMonth() === month - 1
                && date.getDate() === day;
        }

        async function prosesUpdateField(element) {
            if (element.dataset.saving === 'true') {
                return;
            }

            const row = element.closest('tr');
            const id = row.getAttribute('data-id');
            const field = element.getAttribute('data-field');
            const value = element.value;
            const previousValue = element.dataset.previousValue || '';
            const originalBg = element.style.backgroundColor;

            element.dataset.saving = 'true';
            element.disabled = true;
            element.style.backgroundColor = '#fff3cd';

            const berhasil = await updateJadwalAjax(
                id,
                field,
                value,
                element,
                originalBg
            );

            element.dataset.saving = 'false';

            if (berhasil) {
                element.dataset.previousValue = value;
            } else {
                element.value = previousValue;
            }
        }

        async function updateJadwalAjax(id, field, value, element, originalBg) {
            try {
                const response = await fetch(`{{ url('/admin/jadwal-uas/update') }}/${id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ field: field, value: value })
                });

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

                    let message = data.message || 'Data gagal disimpan.';

                    if (data.errors) {
                        const firstKey = Object.keys(data.errors)[0];

                        if (firstKey) {
                            const errors = data.errors[firstKey];
                            message = Array.isArray(errors) ? errors[0] : errors;
                        }
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan',
                        text: message
                    });

                    setTimeout(() => {
                        element.style.backgroundColor = originalBg;
                    }, 1500);

                    return false;
                }

                element.style.backgroundColor = '#d1e7dd';

                setTimeout(() => {
                    element.style.backgroundColor = originalBg;
                }, 1000);

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Disimpan',
                    text: data.message || 'Perubahan berhasil disimpan.',
                    showConfirmButton: false,
                    timer: 2000
                });

                return true;
            } catch (error) {
                console.error('Update jadwal UAS error:', error);

                element.disabled = false;
                element.style.backgroundColor = '#f8d7da';

                Swal.fire({
                    icon: 'error',
                    title: 'Error Server',
                    text: 'Terjadi kegagalan jaringan saat menyimpan data.'
                });

                setTimeout(() => {
                    element.style.backgroundColor = originalBg;
                }, 1500);

                return false;
            }
        }

        // Delete handling
        document.addEventListener('click', function (e) {
            let btn = e.target.closest('.delete-btn');
            if (btn) {
                let row = btn.closest('tr');
                let id = btn.getAttribute('data-id');

                Swal.fire({
                    title: "Hapus Jadwal?",
                    text: "Baris ujian ini akan dihapus secara permanen!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#8592a3',
                    confirmButtonText: "Ya, Hapus!",
                    cancelButtonText: "Batal"
                }).then((result) => {
                    if (result.isConfirmed) {
                        deleteJadwalUAS(id, row);
                    }
                });
            }
        });

        function deleteJadwalUAS(id, row) {
            fetch(`/admin/jadwal-uas/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
                .then(response => {
                    Swal.fire({
                        title: "Process!",
                        text: "Menghapus data...",
                        icon: "info",
                        timer: 1000,
                        showConfirmButton: false
                    });
                    setTimeout(() => { document.getElementById('search-btn').click(); }, 1200);
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire("Sistem Error!", "Gagal memproses penghapusan.", "error");
                });
        }

        // Modal import confirmation
        if (document.getElementById('submitBtngenerate')) {
            document.getElementById('submitBtngenerate').addEventListener('click', function () {
                let jurusan = document.getElementById('jurusan_id').value;
                let jenis = document.getElementById('jenis_kelas').value;

                if (!jurusan || !jenis) {
                    Swal.fire('Perhatian', 'Harap lengkapi Program Studi dan Jenis Kelas.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Konfirmasi Penarikan Data',
                    text: "Sistem akan me-generate baris jadwal UAS untuk masing-masing matkul.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Ya, Tarik Data!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Memproses...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading() }
                        });
                        document.getElementById('generateForm').submit();
                    }
                });
            });
        }

        @if(session('success'))
            Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', timer: 3000, showConfirmButton: false });
        @endif
        @if(session('error'))
            Swal.fire({ title: 'Gagal!', text: "{{ session('error') }}", icon: 'error', timer: 4000 });
        @endif
    </script>
@endsection
