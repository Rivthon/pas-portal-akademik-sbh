@extends('layouts.mahasiswa')
@section('title', 'Edit Program Wirausaha')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Program Wirausaha Mahasiswa</h5>

                <a href="{{ route('mahasiswa.skpi.bahasa') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <p class="card-text">Silahkan isi data Form Sesuai dengan catatan dari validatornya</p>
            <div class="card-body">
                @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
                @endif
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('mahasiswa.update.wirausaha', $wirausaha->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nama Usaha <span class="text-danger">*</span></label>
                            <input type="text" name="nama_usaha" class="form-control"
                                value="{{ old('nama_usaha', $wirausaha->nama_usaha) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Jenis Usaha <span class="text-danger">*</span></label>
                            <input type="text" name="jenis_usaha" class="form-control"
                                value="{{ old('jenis_usaha', $wirausaha->jenis_usaha) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" class="form-control"
                                value="{{ old('penyelenggara', $wirausaha->penyelenggara) }}" required>
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">Status Pendanaan <span class="text-danger">*</span></label>
                            <input type="text" name="status_pendanaan" class="form-control"
                                value="{{ old('status_pendanaan', $wirausaha->status_pendanaan) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', $wirausaha->tanggal) }}" required>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Link Lampiran (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_lampiran" class="form-control"
                                placeholder="https://drive.google.com/..."
                                value="{{ old('file_lampiran', $wirausaha->file_lampiran) }}" required>
                            <small class="text-muted">
                                Pastikan link dapat diakses oleh siapa saja yang memiliki link (Anyone with the link can
                                view).
                            </small>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label">Link Sertifikat (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_sertifikat" class="form-control"
                                placeholder="https://drive.google.com/..."
                                value="{{ old('file_sertifikat', $wirausaha->file_sertifikat) }}" required>
                            <small class="text-muted">
                                Pastikan link dapat diakses oleh siapa saja yang memiliki link (Anyone with the link can
                                view).
                            </small>
                        </div>
                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label">Catatan Validator <span class="text-danger">*</span></label>
                        <textarea name="catatan_validator" class="form-control fw-bold" rows="3" required
                            readonly>{{ old('catatan_validator', $wirausaha->catatan_validator) }}</textarea>
                    </div>
                    <button class="btn btn-primary mt-4" type="submit">
                        <i class="bx bx-save me-1"></i> Perbarui Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.skpi.bahasa') }}" class="btn btn-outline-secondary mt-4 ms-2">
                        Batal
                    </a>
                </form>
            </div>
        </div>

    </div>
    @endsection