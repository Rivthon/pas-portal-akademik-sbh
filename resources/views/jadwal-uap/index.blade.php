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
    </div>
</div>
<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">List Jadwal UAP</h5>
        <p class="card-text">Berikut adalah daftar jadwal UAP yang telah ditetapkan. Silakan pilih program studi
            dan semester untuk melihat jadwal UAP.</p>
        <table class="table table-bordered mt-3">
            <thead class="table-primary">
                <tr>
                    <th>#</th>
                    <th>Tahun Akademik</th>
                    <th>Program Studi</th>
                    <th>Nama</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jadwalUap as $index => $jadwal)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $jadwal->tahunAkademik->nama ?? '-' }}</td>
                    <td>{{ $jadwal->programStudi->nama ?? '-' }}</td>
                    <td>{{ $jadwal->nama }}</td>
                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}</td>
                    <td>
                        @can('jadwal-uap-edit')
                        <a href="{{ route('admin.jadwal-uap.edit', $jadwal->id) }}"
                            class="btn btn-sm btn-warning">Edit</a>
                        @endcan
                        @can('jadwal-uap-delete')
                        <form action="{{ route('admin.jadwal-uap.destroy', $jadwal->id) }}" method="POST"
                            style="display:inline-block;"
                            onsubmit="return confirm('Yakin ingin menghapus jadwal ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data jadwal UAP.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection