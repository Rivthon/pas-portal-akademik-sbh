@extends('layouts.master')
@section('title', 'Detail Permintaan Mahasiswa (Help Desk)')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Detail Permintaan Mahasiswa (Help Desk)
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
<div class="card">
    <div class="card-header">
        <h5 class="card-title">Detail Permintaan</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>Field</th>
                        <th>Isi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Nama Mahasiswa</td>
                        <td>{{ $permintaan->mahasiswa->nama }}</td>
                    </tr>
                    <tr>
                        <td>Jenis Permintaan</td>
                        <td>{{ ucfirst($permintaan->jenis_permintaan) }}</td>
                    </tr>
                    <tr>
                        <td>Judul</td>
                        <td>{{ $permintaan->judul }}</td>
                    </tr>
                    <tr>
                        <td>Deskripsi</td>
                        <td>{{ $permintaan->deskripsi }}</td>
                    </tr>
                    <tr>
                        <td>Status Saat Ini</td>
                        <td><span class="badge bg-info">{{ ucfirst($permintaan->status) }}</span></td>
                    </tr>
                    <tr>
                        <td>Komentar Admin</td>
                        <td>{{ $permintaan->komentar_admin ?? '-' }}</td>
                    </tr>
                    @if($permintaan->file_lampiran)
                    <tr>
                        <td>Lampiran</td>
                        <td><a href="{{ asset('storage/' . $permintaan->file_lampiran) }}" target="_blank"
                                class="btn btn-sm btn-outline-primary">Lihat File</a></td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <hr>

        <form action="{{ route('admin.helpdesk.updateStatus', $permintaan->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="status" class="form-label">Ubah Status</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="pending" {{ $permintaan->status == 'pending' ? 'selected' : '' }}>Pending
                        </option>
                        <option value="diproses" {{ $permintaan->status == 'diproses' ? 'selected' : '' }}>Diproses
                        </option>
                        <option value="selesai" {{ $permintaan->status == 'selesai' ? 'selected' : '' }}>Selesai
                        </option>
                        <option value="ditolak" {{ $permintaan->status == 'ditolak' ? 'selected' : '' }}>Ditolak
                        </option>
                    </select>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="komentar_admin" class="form-label">Komentar Admin</label>
                    <textarea name="komentar_admin" id="komentar_admin" class="form-control"
                        rows="2">{{ $permintaan->komentar_admin }}</textarea>
                </div>
            </div>
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.helpdesk.index') }}" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary mt-2">Update</button>
            </div>

        </form>
    </div>
</div>
@endsection