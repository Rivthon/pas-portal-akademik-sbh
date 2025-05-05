@extends('layouts.master')
@section('title', 'Lista Permintaan Mahasiswa')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Permintaan Mahasiswa (Help Desk)
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Untuk melihat permintaan mahasiswa, silahkan pilih menu yang ada di sebelah kiri.
                    <br>
                    Anda dapat melihat status permintaan mahasiswa dan melakukan tindakan yang diperlukan.
                </p>
                <!-- CTA Button -->

            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/chat.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card bg-light">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Mahasiswa</th>
                        <th>Jenis Permintaan</th>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($permintaan as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->mahasiswa->nama ?? '-' }}</td>
                        <td>{{ ucfirst($item->jenis_permintaan) }}</td>
                        <td>{{ $item->judul }}</td>
                        <td><span class="badge bg-info">{{ ucfirst($item->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.helpdesk.show', $item->id) }}"
                                class="btn btn-sm btn-primary">Detail</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection