@extends('layouts.master')
@section('title', 'Mata Kuliah')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">Mata Kuliah</h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Halaman ini berisi daftar matakuliah yang diajarkan pada program studi tertentu, semester tertentu.
                    Silakan pilih program studi dan semester terlebih dahulu untuk melihat daftar matakuliah yang
                    tersedia. Sesuai dengan tahun ajaran yang sedang aktif.
                    <br>
                    Tahun Ajaran {{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester
                    }})
                </p>

                @can('jadwal-uts-create')
                <div class="mb-3">
                    <a href="{{ route('admin.kurikulum.create') }}" class="btn btn-primary">
                        Tambah Mata Kuliah
                    </a>
                </div>
                @endcan
            </div>

            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid"
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
            <button id="search-btn" class="btn btn-primary">Cari Mata Kuliah</button>
        </div>

        <div id="alert-container" class="mt-3"></div> <!-- Alert Container -->
    </div>
</div>
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">List Mata Kuliah</h5>
        <div id="alert-container" class="mt-3"></div> <!-- Container untuk alert -->
        <div id="loading" class="text-center mt-3" style="display: none;">
            <span class="spinner-border text-primary"></span> <br>
            <small>Loading...</small>
        </div>

        <table id="kurikulum-table" class="table table-bordered table-striped mt-3" style="display: none;">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Kode Mata Kuliah</th>
                    <th>Mata Kuliah</th>
                    <th>Semester</th>
                    {{-- <th>Jam Mulai - Selesai</th>
                    <th>Hari</th>
                    <th>Ruangan</th> --}}
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
        let table = document.getElementById('kurikulum-table');
        let tbody = table.querySelector('tbody');

        // Reset alert & table sebelum melakukan fetch data
        alertContainer.innerHTML = "";
        table.style.display = "none";
        tbody.innerHTML = "";

        // Validasi input
        if (!programStudi || !semester) {
            alertContainer.innerHTML = `<div class="alert alert-warning">Silakan pilih program studi dan semester terlebih dahulu.</div>`;
            return;
        }

        // Tampilkan indikator loading
        loading.style.display = "block";

        // Fetch data dari server
        fetch(`{{ route('admin.kurikulum.filter') }}?programStudi=${programStudi}&semester=${semester}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Terjadi kesalahan saat mengambil data.');
                }
                return response.json();
            })
            .then(data => {
                loading.style.display = "none"; // Sembunyikan loading

                if (data.message) {
                    alertContainer.innerHTML = `<div class="alert alert-info">${data.message}</div>`;
                    return;
                }

                // Loop data kurikulum dan tambahkan ke tabel
                data.forEach(kurikulum => {
                    let editUrl = `{{ route('admin.kurikulum.edit', ':kurikulum_id') }}`.replace(':kurikulum_id', kurikulum.kurikulum_id);
                    let destroyUrl = `{{ route('admin.kurikulum.destroy', ':kurikulum_id') }}`.replace(':kurikulum_id', kurikulum.kurikulum_id);
                    let row = `<tr>
                        <td>${data.indexOf(kurikulum) + 1}</td>
                        <td>${kurikulum.matakuliah_id}</td>
                        <td>${kurikulum.nama_matakuliah}</td>
                        <td>Semester ${kurikulum.semester}</td>

                    <td>
                        @can('kurikulum-edit')
                        <a href="${editUrl}" class="btn btn-sm btn-warning">Edit</a>
                        @endcan
                        @can('kurikulum-delete')
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

                // Tampilkan tabel setelah data dimuat
                table.style.display = "table";
            })
            .catch(error => {
                loading.style.display = "none"; // Sembunyikan loading
                alertContainer.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
                console.error('Error:', error);
            });
    });
</script>
<script>
    document.addEventListener('click', function(event) {
    if (event.target.classList.contains('delete-btn')) {
        let id = event.target.getAttribute('data-id');
        let confirmation = confirm("Apakah Anda yakin ingin menghapus data ini?");
        if (confirmation) {
            fetch(`{{ route('admin.kurikulum.destroy', '') }}/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Data berhasil dihapus!");
                    event.target.closest('tr').remove(); // Hapus baris dari tabel
                } else {
                    alert("Gagal menghapus data.");
                }
            })
            .catch(error => console.error('Error:', error));
        }
    }
});
</script>
@endsection