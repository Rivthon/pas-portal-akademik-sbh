@extends('layouts.mahasiswa')
@section('title', 'Edit Memahami sistem pembelajaran di perguruan tinggi (PPSM)')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Memahami sistem pembelajaran di perguruan tinggi (PPSM)</h5>

                <a href="{{ route('mahasiswa.skpi.sertifikasi') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <p class="card-text">Silakan isi data PPSM yang telah Anda ikuti.</p>
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

                <form action="{{ route('mahasiswa.update.ppsm', $sertifikasi->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nama Sertifikasi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" class="form-control"
                                value="{{ old('nama_kegiatan', $sertifikasi->nama_kegiatan) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tahun Kegiatan <span class="text-danger">*</span></label>
                            <input type="number" name="tahun_kegiatan" class="form-control"
                                value="{{ old('tahun_kegiatan', $sertifikasi->tahun_kegiatan) }}" required min="1900"
                                max="2100">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Keterangan</label>
                            <input type="text" name="keterangan" class="form-control"
                                value="{{ old('keterangan', $sertifikasi->keterangan) }}">
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-6">
                            <label class="form-label">File Sertifikat (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_sertifikat" class="form-control"
                                value="{{ old('file_sertifikat', $sertifikasi->file_sertifikat) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label">File Lampiran (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_lampiran" class="form-control"
                                value="{{ old('file_lampiran', $sertifikasi->file_lampiran) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-4" type="submit">
                        <i class="bx bx-save me-1"></i> Perbarui Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.skpi.ppsm') }}" class="btn btn-outline-secondary mt-4 ms-2">
                        Batal
                    </a>
                </form>
            </div>
        </div>

    </div>
    @endsection