@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Pertemuan untuk Jadwal: <strong>{{ $jadwal->matakuliah->name }}</strong></h1>

    <!-- Tampilkan pesan sukses -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Form Tambah Pertemuan -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            Tambah Pertemuan
        </div>
        <div class="card-body">
            <form action="{{ route('admin.pertemuan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="jadwal_id" value="{{ $jadwal->jadwal_id }}">

                <div class="mb-3">
                    <label for="tanggal_pertemuan" class="form-label">Tanggal Pertemuan</label>
                    <input type="date" name="tanggal_pertemuan" id="tanggal_pertemuan" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="topik" class="form-label">Topik</label>
                    <input type="text" name="topik" id="topik" class="form-control" placeholder="Masukkan topik pertemuan">
                </div>

                <button type="submit" class="btn btn-primary">Tambah Pertemuan</button>
            </form>
        </div>
    </div>

    <!-- Daftar Pertemuan -->
    <div class="card">
        <div class="card-header bg-secondary text-white">
            Daftar Pertemuan
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Topik</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pertemuan as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($item->tanggal_pertemuan)->format('d M Y') }}</td>
                        <td>{{ $item->topik }}</td>
                        <td>
                            <!-- Toggle button for active/inactive status -->
                           <form action="{{ route('admin.pertemuan.toggleStatus', $item->pertemuan_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <button type="submit" class="btn btn-sm btn-{{ $item->status == 1 ? 'success' : 'danger' }}">
                                    {{ $item->status == 1 ? 'Aktif' : 'Tidak Aktif' }}
                                </button>
                            </form>


                        </td>
                        <td>
                                <a href="{{ route('admin.absensi.detail', [$item->jadwal_id, $item->pertemuan_id]) }}" class="btn btn-sm btn-success">
                                    Lihat Siswa
                                </a>

                                <!-- Edit Button -->
                                <a href="{{ route('admin.pertemuan.edit', [$item->jadwal_id, $item->pertemuan_id]) }}" class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <!-- Delete Form -->
                                <form action="{{ route('admin.pertemuan.destroy', [$item->jadwal_id, $item->pertemuan_id]) }}" method="POST" class="d-inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pertemuan ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada pertemuan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        </div>
    </div>
</div>
@endsection

