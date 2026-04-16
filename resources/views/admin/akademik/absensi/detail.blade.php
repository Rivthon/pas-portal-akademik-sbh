@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Detail Absensi - {{ $jadwal->mataKuliah->name }}</h4>
                <p class="mb-0 text-muted">
                    Tanggal:
                    @if ($tanggal instanceof \Carbon\Carbon)
                        {{ $tanggal->isoFormat('dddd, D MMMM YYYY') }}
                    @else
                        Hari tidak valid
                    @endif
                </p>
            </div>
            <a href="{{ route('admin.pertemuan.index', $jadwal->jadwal_id) }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    {{ implode('', $errors->all(':message')) }}
                </div>
            @endif

            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Mahasiswa</th>
                        <th>NIM</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($absensi as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item->mahasiswa->name }}</td>
                            <td>{{ $item->mahasiswa->nim }}</td>
                            <td>
                                <span class="badge
                                    {{ $item->status == 'hadir' ? 'bg-success' :
                                       ($item->status == 'tidak hadir' ? 'bg-danger' :
                                       'bg-warning') }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <!-- Form untuk update status -->
                                <form action="{{ route('admin.absensi.updateStatus', $item->absensi_id) }}" method="POST" class="d-flex align-items-center">
                                    @csrf
                                    @method('PUT')

                                    <div class="form-check me-2">
                                        <input type="radio" name="status" id="hadir-{{ $item->id }}" value="hadir"
                                            {{ $item->status == 'hadir' ? 'checked' : '' }} class="form-check-input">
                                        <label for="hadir-{{ $item->id }}" class="form-check-label">Hadir</label>
                                    </div>

                                    <div class="form-check me-2">
                                        <input type="radio" name="status" id="tidak-hadir-{{ $item->id }}" value="tidak hadir"
                                            {{ $item->status == 'tidak hadir' ? 'checked' : '' }} class="form-check-input">
                                        <label for="tidak-hadir-{{ $item->id }}" class="form-check-label">Tidak Hadir</label>
                                    </div>

                                    <div class="form-check me-2">
                                        <input type="radio" name="status" id="sakit-{{ $item->id }}" value="sakit"
                                            {{ $item->status == 'sakit' ? 'checked' : '' }} class="form-check-input">
                                        <label for="sakit-{{ $item->id }}" class="form-check-label">Sakit</label>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-sm ms-2">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Tidak ada data absensi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
