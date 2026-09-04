@extends('layouts.master')
@section('title', 'Jadwal Kuliah Akademik')
@section('content')

<!-- Header Info -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">Manajemen Jadwal Kuliah</h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Halaman ini berisi informasi mengenai jadwal kuliah reguler mahasiswa. Anda dapat mengatur ruangan, jam, dan hari untuk masing-masing kelas.
                    Jadwal disesuaikan dengan kurikulum program studi masing-masing, <br>
                    <span class="badge bg-label-primary mt-2 fs-6">Tahun Ajaran {{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester }})</span>
                </p>
            </div>
            <div class="col-md-5 text-center">
                <img src="../assets/img/illustrations/calender.png" class="img-fluid" alt="Illustration" style="max-height: 150px;">
            </div>
        </div>
    </div>
</div>

@can('jadwal-create')
<!-- IMPORT JADWAL SECTION -->
<div class="card shadow-sm mb-4 border-top border-5 border-success">
    <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title text-success mb-0"><i class="bx bx-import me-1"></i> Tarik Data Jadwal Kuliah</h5>
            <small class="text-muted">Generate data awal jadwal kuliah berdasarkan kurikulum aktif jurusan yang dipilih.</small>
        </div>
    </div>
    <div class="card-body mt-3">
        <form id="generateForm" action="{{ route('admin.jadwal.generate') }}" method="POST">
            @csrf
            <div class="row align-items-end bg-light p-3 rounded">
                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="jurusan_id" class="form-label fw-semibold">Pilih Program Studi <span class="text-danger">*</span></label>
                    <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach($programStudi as $j)
                            <option value="{{ $j->jurusan_id }}">{{ $j->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="jenis_kelas" class="form-label fw-semibold">Pilih Jenis Kelas <span class="text-danger">*</span></label>
                    <select name="jenis_kelas" id="jenis_kelas" class="form-select" required>
                        <option value="Reguler">Reguler A</option>
                        <option value="Karyawan">Reguler B</option>
                    </select>
                </div>
                <div class="col-md-2 mt-3 mt-md-0 d-grid">
                    <button type="button" id="submitBtngenerate" class="btn btn-success"><i class="bx bx-download me-1"></i> Tarik Data</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endcan

<!-- FILTER & PENCARIAN -->
<div class="card shadow-sm mt-4 border-top border-5 border-primary">
    <div class="card-header bg-white pb-0">
        <h5 class="card-title text-primary mb-0"><i class="bx bx-search me-1"></i> Cari & Edit Jadwal Kuliah</h5>
    </div>
    <div class="card-body">
        <div class="row mt-3">
            <div class="col-md-4 mb-3">
                <label for="program-studi" class="form-label fw-bold">Pilih Program Studi <span class="text-danger">*</span></label>
                <select id="program-studi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="semester" class="form-label fw-bold">Pilih Semester <span class="text-danger">*</span></label>
                <select id="semester" class="form-select">
                    <option value="">-- Pilih Semester --</option>
                    @for ($i = 1; $i <= 8; $i++)
                        <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label for="jenis_kelas_cari" class="form-label fw-bold">Pilih Jenis Kelas <span class="text-danger">*</span></label>
                <select id="jenis_kelas_cari" class="form-select">
                    <option value="reguler">Reguler A</option>
                    <option value="karyawan">Reguler B</option>
                </select>
            </div>
        </div>

        <div class="mt-2 text-end">
            <button id="search-btn" class="btn btn-primary px-4"><i class="bx bx-search-alt me-1"></i> Tampilkan Jadwal</button>
        </div>

        <div id="alert-container" class="mt-3"></div>
    </div>
</div>

<!-- HASIL JADWAL -->
<div class="card mt-4 mb-5">
    <div class="card-body">
        <h5 class="card-title"><i class="bx bx-table me-1"></i> List Jadwal Kuliah</h5>
        <div id="loading" class="text-center my-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <div class="mt-2 text-muted">Memuat data jadwal...</div>
        </div>

        <div class="table-responsive text-nowrap">
            <table id="jadwal-table" class="table table-hover table-bordered mt-3" style="display: none;">
                <thead class="table-primary border-bottom">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th style="width: 25%">Mata Kuliah</th>
                        <th class="text-center" style="width: 5%">SMT</th>
                        <th style="width: 15%">Jam Mulai</th>
                        <th style="width: 15%">Jam Selesai</th>
                        <th style="width: 15%">Hari</th>
                        <th style="width: 15%">Ruangan</th>
                        <th class="text-center" style="width: 5%">Kelas</th>
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

        fetch(`{{ route('admin.jadwal.filter') }}?programStudi=${programStudi}&semester=${semester}&jenis_kelas=${jenis_kelas}`)
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
                                <p class="mb-0">${data.message} <br>Anda dapat menggunakan form <strong>Tarik Data Jadwal Kuilah</strong> di atas untuk men-generate jadwal secara otomatis.</p>
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
                    data = data.data || []; // fallback jika kebetulan masih ke-paginate (prevent bug)
                }

                if (data.length === 0) {
                    alertContainer.innerHTML = `<div class="alert alert-info"><i class="bx bx-info-circle me-1"></i> Tidak ada jadwal yang ditemukan.</div>`;
                    return;
                }

                data.forEach((jadwal, index) => {
                    let ruanganOptions = `<option value="">-- Pilih --</option>`;
                    @foreach($ruangan as $r)
                        ruanganOptions += `<option value="{{ $r->ruangan_id }}" ${"{{ $r->ruangan_id }}" == jadwal.ruangan_id ? 'selected' : ''}>{{ $r->nama }}</option>`;
                    @endforeach

                    let dHari = jadwal.hari || '';
                    let dMulai = jadwal.jam_mulai || '';
                    let dSelesai = jadwal.jam_selesai || '';

                    let row = `<tr data-id="${jadwal.id}">
                        <td class="text-center">${index + 1}</td>
                        <td class="fw-semibold text-wrap">${jadwal.nama_matakuliah}</td>
                        <td class="text-center">${jadwal.semester}</td>

                        <!-- Editable Jam Mulai -->
                        <td>
                            <input type="time" class="form-control form-control-sm update-field" data-field="jam_mulai" value="${dMulai}" />
                        </td>
                        <!-- Editable Jam Selesai -->
                        <td>
                            <input type="time" class="form-control form-control-sm update-field" data-field="jam_selesai" value="${dSelesai}" />
                        </td>
                        <!-- Editable Hari -->
                        <td>
                            <select class="form-select form-select-sm update-field" data-field="hari">
                                <option value="" ${dHari === '' ? 'selected' : ''}>-- Hari --</option>
                                <option value="Senin" ${dHari === 'Senin' ? 'selected' : ''}>Senin</option>
                                <option value="Selasa" ${dHari === 'Selasa' ? 'selected' : ''}>Selasa</option>
                                <option value="Rabu" ${dHari === 'Rabu' ? 'selected' : ''}>Rabu</option>
                                <option value="Kamis" ${dHari === 'Kamis' ? 'selected' : ''}>Kamis</option>
                                <option value="Jumat" ${dHari === 'Jumat' ? 'selected' : ''}>Jumat</option>
                                <option value="Sabtu" ${dHari === 'Sabtu' ? 'selected' : ''}>Sabtu</option>
                                <option value="Minggu" ${dHari === 'Minggu' ? 'selected' : ''}>Minggu</option>
                            </select>
                        </td>

                        <!-- Editable Ruangan -->
                        <td>
                            <select class="form-select form-select-sm update-field" data-field="ruangan_id">
                                ${ruanganOptions}
                            </select>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-label-info">${String(jadwal.jenis_kelas).toLowerCase() === 'karyawan' ? 'Reguler B' : 'Reguler A'}</span>
                        </td>
                    </tr>`;

                    tbody.innerHTML += row;
                });

                table.style.display = "table";
            })
            .catch(error => {
                loading.style.display = "none";
                alertContainer.innerHTML = `<div class="alert alert-danger"><i class="bx bx-x-circle me-1"></i> Terjadi kesalahan saat mengambil data jadwal. Pastikan koneksi stabil.</div>`;
                console.error('Error:', error);
            });
    });

    // Auto update inline fields
    document.addEventListener('change', async function (e) {
        if (e.target.classList.contains('update-field')) {
            let row = e.target.closest('tr');
            let id = row.getAttribute('data-id');
            let field = e.target.getAttribute('data-field');
            let value = e.target.value;

            // Tambahkan disabled agar menghindari spam click
            e.target.disabled = true;
            let originalBg = e.target.style.backgroundColor;
            e.target.style.backgroundColor = '#fff3cd'; // Kasih warna kuning loading

            await updateJadwalAjax(id, field, value, e.target, originalBg);
        }
    });

    async function updateJadwalAjax(id, field, value, element, originalBg) {
        try {
            let response = await fetch(`{{ url('/admin/jadwal-kuliah/update') }}/${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ field: field, value: value })
            });

            let data = await response.json();
            element.disabled = false;

            if (data.success) {
                element.style.backgroundColor = '#d1e7dd'; // hijau success
                setTimeout(() => { element.style.backgroundColor = originalBg; }, 1000);

                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: 'Disimpan',
                    text: data.message,
                    showConfirmButton: false,
                    timer: 2000
                });
            } else {
                element.style.backgroundColor = '#f8d7da'; // merah error
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: data.message
                });
            }
        } catch (error) {
            element.disabled = false;
            element.style.backgroundColor = '#f8d7da';
            Swal.fire({
                icon: 'error',
                title: 'Error Server',
                text: 'Terjadi kegagalan jaringan saat menyimpan data.'
            });
        }
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
                text: "Menu ini akan me-generate baris jadwal kosong untuk semua mata kuliah sesuai Kurikulum yang berlaku.",
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
    Swal.fire({
        title: 'Berhasil!',
        text: "{{ session('success') }}",
        icon: 'success',
        timer: 3000,
        showConfirmButton: false
    });
    @endif

    @if(session('error'))
    Swal.fire({
        title: 'Gagal!',
        text: "{{ session('error') }}",
        icon: 'error',
        timer: 4000
    });
    @endif
</script>
@endsection
