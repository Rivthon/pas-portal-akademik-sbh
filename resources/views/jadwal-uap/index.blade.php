@extends('layouts.master')
@section('title', 'Jadwal Ujian Akhir Program')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">Jadwal Ujian Akhir Program</h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah jadwal ujian tengah semester yang telah ditetapkan oleh program studi. Silakan
                    pilih program studi dan semester untuk melihat jadwal ujian tengah semester.
                </p>
                @can('jadwal-uap-create')
                <div class="mb-3">
                    <a href="{{ route('admin.jadwal-uap.create') }}" class="btn btn-primary">
                        Tambah Jadwal
                    </a>
                </div>
                @endcan
            </div>

            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                    alt="Illustration for morning schedule" style="max-height: 200px;">
            </div>
        </div>

        <!-- Selection Section -->
        <div class="row mt-4">
            <div class="col-md-6 mb-3">
                <label for="program-studi" class="form-label">Pilih Program Studi</label>
                <select id="program-studi" class="form-select">
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="semester" class="form-label">Pilih Semester</label>
                <select id="semester" class="form-select">
                    <option value="">-- Pilih Semester --</option>
                    @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                        @endfor
                </select>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            <button id="search-btn" class="btn btn-primary">Cari Jadwal</button>
        </div>

        <div id="alert-container" class="mt-3"></div> <!-- Alert Container -->
    </div>
</div>
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">List Jadwal UAP</h5>
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
                    <th>Semester</th>
                    <th>Jam</th>
                    <th>Tanggal</th>
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
        let alertContainer = document.getElementById('alert-container');
        let loading = document.getElementById('loading');
        let table = document.getElementById('jadwal-table');
        let tbody = table.querySelector('tbody');

        // Reset alert & table
        alertContainer.innerHTML = "";
        table.style.display = "none";
        tbody.innerHTML = "";

        if (!programStudi || !semester) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih program studi dan semester terlebih dahulu.</div>`;
            return;
        }

        loading.style.display = "block"; // Tampilkan loading

        fetch(`{{ route('admin.jadwal-uap.filter') }}?programStudi=${programStudi}&semester=${semester}`)
            .then(response => response.json())
            .then(data => {
                loading.style.display = "none"; // Sembunyikan loading

                if (data.message) {
                    alertContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                    return;
                }

              data.forEach(jadwal => {
                let editUrl = `{{ route('admin.jadwal-uap.edit', ':id') }}`.replace(':id', jadwal.id);
                let destroyUrl = `{{ route('admin.jadwal-uap.destroy', ':id') }}`.replace(':id', jadwal.id);

                let row = `<tr>
                    <td>${data.indexOf(jadwal) + 1}</td>
                    <td>${jadwal.nama_matakuliah}</td>
                    <td>${jadwal.semester}</td>

                    <td>${jadwal.jam}</td>
                    <td>${new Date(jadwal.tanggal).toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</td>
                    <td>${jadwal.nama_ruangan}</td>
                    <td>${jadwal.jenis_kelas}</td>
                    <td>
                        @can('jadwal-uap-edit')
                        <a href="${editUrl}" class="btn btn-sm btn-warning">Edit</a>
                        @endcan
                        @can('jadwal-uap-delete')
                        <form action="${destroyUrl}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">Delete</button>
                        </form>
                        @endcan
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
</script>
@endsection