@extends('layouts.mahasiswa')
@section('title', 'Daftar Permintaan Mahasiswa')

@section('content')
    <div class="container">
        <!-- Card Section -->
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center item g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Permintaan Bantuan / Saran
                        </h5>
                        <p class="text-muted mb-4" style="line-height: 1.6;">

                            <hr>
                            <strong>{{ $mahasiswa->nama }} <br>
                                {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun Ajaran: {{
        $ta->nama }}</strong>
                            <hr>
                            Permintaan ini digunakan untuk mengajukan bantuan atau saran terkait sistem informasi akademik.
                            <br>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            Permintaan akan langsung masuk ke Helpdesk Admin. Status dan tanggapan Admin dapat dilihat
                            pada riwayat di bawah.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="{{ route('mahasiswa.permintaan.create') }}" class="btn btn-success">+ Buat Permintaan
                                Baru</a>
                        </div>
                    </div>
                </div>
                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">
                        <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                            alt="Illustration of a schedule" style="max-height: 200px;">
                    </div>
                </div>
            </div>
        </div>

        <!-- Success Message -->
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Table Card -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>#</th>
                                <th>Jenis</th>
                                <th>Judul</th>
                                <th>Prioritas</th>
                                <th>Status</th>
                                <th>Tanggapan</th>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permintaan as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ ucfirst($item->jenis_permintaan) }}</td>
                                    <td>{{ $item->judul }}</td>
                                    <td><span class="badge bg-info text-dark">{{ ucfirst($item->prioritas) }}</span></td>
                                    <td>
                                        @php
                                            $badge = [
                                                'pending' => 'secondary',
                                                'diproses' => 'warning',
                                                'selesai' => 'success',
                                                'ditolak' => 'danger',
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $badge[$item->status] ?? 'secondary' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td>{{ $item->komentar_admin ?? '-' }}</td>
                                    <td>{{ $item->created_at->format('d M Y') }}</td>
                                    <td>
                                        @if (in_array($item->status, ['menunggu', 'revisi'], true))
                                            <form action="{{ route('mahasiswa.permintaan.destroy', $item->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus permintaan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" type="submit"><i
                                                        class="bx bx-trash"></i></button>
                                            </form>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Belum ada permintaan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection