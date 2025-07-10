@extends('layouts.master')
@section('title', 'Jadwal Ujian Akhir Semester')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">Jadwal Ujian Akhir Semester</h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah jadwal ujian akhir semester yang telah ditetapkan oleh program studi. Silakan
                    pilih program studi dan semester untuk melihat jadwal ujian tengah semester.
                    Tersedia ada fitur lihat jadwal dan import jadwal UTS ke dari Mata Kuliah.
                </p>

            </div>

            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                    alt="Illustration for morning schedule" style="max-height: 200px;">
            </div>
        </div>

        <!-- Selection Section -->
    </div>
</div>

<div class="card">
    <div class="container mt-4">
        <div id="jadwal-uas-container" class="row mb-4">
            <div class="col-md-6">
                <h5 class="card-title text-primary mb-3 fw-bold">Cari Jadwal Ujian Akhir Semester</h5>
                <div class="form-group">
                    <label for="program-studi" class="form-label">Pilih Program Studi</label>
                    <select id="program-studi" class="form-select">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="semester" class="form-label">Pilih Semester</label>
                    <select id="semester" class="form-select">
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                    </select>
                </div>
                <div class="form-group mt-3">
                    <label for="jenis_kelas" class="form-label">Pilih Jenis Kelas:</label>
                    <select name="jenis_kelas" id="jenis_kelas_cari" class="form-select" required>
                        <option value="reguler">Reguler</option>
                        <option value="karyawan">Karyawan</option>
                    </select>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button id="search-btn" class="btn btn-primary">Lihat Jadwal</button>
                </div>
                <div id="alert-container" class="mt-3"></div> <!-- Alert Container -->
            </div>
            <div class="col-md-6">
                <h5 class="card-title text-primary mb-3 fw-bold">Import Data Jadwal Ujian Akhir Semester</h5>
                @can('jadwal-uas-create')
                <form id="generateForm" action="{{ route('admin.jadwal-uas.generate') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="jurusan_id" class="form-label">Pilih Program Studi:</label>
                        <select name="jurusan_id" id="jurusan_id" class="form-select" required>
                            @foreach($programStudi as $j)
                            <option value="{{ $j->jurusan_id }}">{{ $j->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mt-3">
                        <label for="jenis_kelas" class="form-label">Pilih Jenis Kelas:</label>
                        <select name="jenis_kelas" id="jenis_kelas" class="form-select" required>
                            <option value="Reguler">Reguler</option>
                            <option value="Karyawan">Karyawan</option>
                        </select>
                    </div>
                    <button type="button" id="submitBtngenerate" class="btn btn-primary mt-4">Tarik Data</button>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">List Jadwal UAS</h5>
        <div id="loading" class="text-center my-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <table id="jadwal-table" class="table table-bordered mt-3" style="display: none;">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Mata Kuliah</th>
                    <th>SMT</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Hari</th>
                    <th>Ruangan</th>
                    <th>Jenis Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data akan diisi dengan AJAX -->
            </tbody>
        </table>
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

        // Reset alert & table
        alertContainer.innerHTML = "";
        table.style.display = "none";
        tbody.innerHTML = "";

        if (!programStudi) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih program studi terlebih dahulu.</div>`;
            return;
        }

        if (!semester) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih semester terlebih dahulu.</div>`;
            return;
        }

        if (!jenis_kelas) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih jenis kelas terlebih dahulu.</div>`;
            return;
        }

        loading.style.display = "block"; // Tampilkan loading

        fetch(`{{ route('admin.jadwal-uas.filter') }}?programStudi=${programStudi}&semester=${semester}&jenis_kelas=${jenis_kelas}`)
            .then(response => response.json())
            .then(data => {
                loading.style.display = "none"; // Sembunyikan loading

                if (data.message) {
                    alertContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                    return;
                }

                tbody.innerHTML = ""; // Reset isi tabel

                data.forEach((jadwal, index) => {
                    let ruanganOptions = `@foreach($ruangan as $r) <option value="{{ $r->ruangan_id }}">{{ $r->nama }}</option> @endforeach`;

                    let row = `<tr data-id="${jadwal.id}">
                        <td>${index + 1}</td>
                        <td>${jadwal.nama_matakuliah}</td>
                        <td>${jadwal.semester}</td>

                        <!-- Editable Jam -->
                        <td>
                            <input type="time" class="form-control update-field" data-field="jam_mulai" value="${jadwal.jam_mulai}" />
                        </td>
                        <td>
                            <input type="time" class="form-control update-field" data-field="jam_selesai" value="${jadwal.jam_selesai}" />
                        </td>
                        <!-- Editable Tanggal -->
                        <td>
                            <input type="date" class="form-control update-field" data-field="tanggal" value="${jadwal.tanggal || ''}" />
                        </td>

                        <!-- Editable Ruangan -->
                        <td>
                            <select class="form-control update-field" data-field="ruangan_id">
                                <option value="">Pilih Ruangan</option>
                                ${ruanganOptions.replace(`value="${jadwal.ruangan_id}"`, `value="${jadwal.ruangan_id}" selected`)}
                            </select>
                        </td>

                       <td>
                        <span class="badge bg-primary">${jadwal.jenis_kelas === 'Reguler' ? 'reg' : 'kar'}</span>
                    </td>
                        <td>
                            <button class="btn btn-danger btn-sm delete-btn" data-id="${jadwal.id}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>`;

                    tbody.innerHTML += row;
                });

                table.style.display = "table";
            })
            .catch(error => {
                loading.style.display = "none"; // Sembunyikan loading
                alertContainer.innerHTML = `<div class="alert alert-danger">Terjadi kesalahan saat mengambil data. Coba lagi nanti.</div>`;
                console.error('Error:', error);
            });
    });

    // Event Listener untuk Update Otomatis
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('update-field')) {
            let row = e.target.closest('tr');
            let id = row.getAttribute('data-id');
            let field = e.target.getAttribute('data-field');
            let value = e.target.value;

            updateJadwalUTS(id, field, value);
        }
    });

    function updateJadwalUTS(id, field, value) {
        fetch(`{{ url('/admin/jadwal-uas/update') }}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ field: field, value: value })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    toast: true,
                    position: 'bottom-end',
                    icon: 'success',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            } else {
                Swal.fire({
                    toast: true,
                    position: 'bottom-start',
                    icon: 'error',
                    title: data.message,
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                });
            }
        })
        .catch(error => console.error('Error:', error));
    }
        document.getElementById('submitBtngenerate').addEventListener('click', function () {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Apakah Anda yakin ingin generate jadwal UAS?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Submit form jika dikonfirmasi
                    document.getElementById('generateForm').submit();
                }
            });
        });

        // Tampilkan notifikasi sukses jika ada
        @if(session('success'))
            Swal.fire({
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
                // Event Listener untuk Hapus Jadwal tanpa reload
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('delete-btn')) {
        let row = e.target.closest('tr');
        let id = row.getAttribute('data-id');

        Swal.fire({
            title: "Apakah Anda yakin?",
            text: "Data jadwal praktik ini akan dihapus secara permanen!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Ya, Hapus!",
            cancelButtonText: "Batal"
        }).then((result) => {
            if (result.isConfirmed) {
                deleteJadwalUTS(id, row);
            }
        });
    }
});

// Fungsi untuk menghapus jadwal praktik tanpa reload
function deleteJadwalUTS(id, row) {
    fetch(`/admin/jadwal-uas/delete/${id}`, {
        method: 'DELETE',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                title: "Berhasil!",
                text: data.message,
                icon: "success",
                confirmButtonText: "OK"
            });

            // Efek fade-out sebelum menghapus row
            row.style.transition = "opacity 0.3s";
            row.style.opacity = "0";

            setTimeout(() => row.remove(), 300);
        } else {
            Swal.fire({
                title: "Gagal!",
                text: data.message,
                icon: "error",
                confirmButtonText: "OK"
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: "Terjadi Kesalahan!",
            text: "Gagal menghapus data",
            icon: "error",
            confirmButtonText: "OK"
        });
    });
}
</script>
@endsection