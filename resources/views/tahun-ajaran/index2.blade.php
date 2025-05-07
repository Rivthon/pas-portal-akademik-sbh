@extends('layouts.master')
@section('title', 'Tahun Ajaran')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Tahun Ajaran
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar tahun ajaran yang tersedia. Silakan tambahkan tahun ajaran baru jika
                    diperlukan.
                </p>
                <!-- CTA Button -->
                @can('users-create')
                <div class="mb-3">
                    <a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary" data-bs-toggle="tooltip"
                        data-bs-original-title="Tambah">
                        Tambah
                    </a>
                </div>
                @endcan
            </div>

        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/lock.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>


    </div>
</div>
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Tahun Ajaran</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Tahun Ajaran
            </caption>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Semester</th>
                    <th>Status TA</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tahunAjarans as $ta)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $ta->nama }}</td>
                    <td>{{ $ta->semester }}</td>
                    <td>
                        <form action="{{ route('admin.tahun-ajaran.updateStatus', $ta->ta_id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm {{ $ta->status_ta == 1 ? 'btn-success' : 'btn-secondary' }}">
                                {{ $ta->status_ta == 1 ? 'Aktif' : 'Tidak Aktif' }}
                            </button>
                        </form>
                    </td>
                    <td>
                        <a href="{{ route('admin.tahun-ajaran.edit', $ta->ta_id) }}"
                            class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('admin.tahun-ajaran.destroy', $ta->ta_id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger"
                                onclick="return confirm('Hapus data ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Data tidak tersedia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {!! $tahunAjarans->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection