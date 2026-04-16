@extends('layouts.mahasiswa')
@section('title', 'Edit Sertifikasi / Kompetensi')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Sertifikasi / Kompetensi</h5>

                <a href="{{ route('mahasiswa.skpi.sertifikasi') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <p class="card-text">Silakan isi data sertifikasi / kompetensi yang telah Anda ikuti.</p>
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

                <form action="{{ route('mahasiswa.update.sertifikasi', $sertifikasi->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Jenis Sertifikat <span class="text-danger">*</span></label>
                            <select name="jenis_sertifikat" class="form-select" required>
                                <option value="" disabled {{ old('jenis_sertifikat', $sertifikasi->jenis_sertifikat) ?
                                    '' : 'selected' }}>-- pilih --</option>
                                <option value="Akademik" @selected(old('jenis_sertifikat', $sertifikasi->
                                    jenis_sertifikat) == 'Akademik')>Akademik</option>
                                <option value="Non Akademik" @selected(old('jenis_sertifikat', $sertifikasi->
                                    jenis_sertifikat) == 'Non Akademik')>Non Akademik</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nama Sertifikasi <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" class="form-control"
                                value="{{ old('nama_kegiatan', $sertifikasi->nama_kegiatan) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" class="form-control"
                                value="{{ old('penyelenggara', $sertifikasi->penyelenggara) }}" required>
                        </div>



                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Prestasi</label>
                            <input type="text" name="prestasi" class="form-control"
                                value="{{ old('prestasi', $sertifikasi->prestasi) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', $sertifikasi->tanggal) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Tingkat Kegiatan <span class="text-danger">*</span></label>
                            <select name="tingkat_kegiatan" class="form-select" required>
                                @foreach(['Lokal','Regional','Nasional','Internasional'] as $t)
                                <option value="{{ $t }}" @selected(old('tingkat_kegiatan', $sertifikasi->
                                    tingkat_kegiatan) ==
                                    $t)>
                                    {{ $t }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Kolom 3 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Link Sertifikat (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_sertifikat" class="form-control"
                                value="{{ old('file_sertifikat', $sertifikasi->file_sertifikat) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can
                                view).</small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Dokumen Pendukung (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="dokumen_pendukung" class="form-control"
                                value="{{ old('dokumen_pendukung', $sertifikasi->dokumen_pendukung) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can
                                view).</small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Catatan Validator <span class="text-danger">*</span></label>
                            <textarea name="catatan_validator" class="form-control fw-bold" rows="3" required
                                readonly>{{ old('catatan_validator', $sertifikasi->catatan_validator) }}</textarea>

                        </div>
                    </div>

                    <button class="btn btn-primary mt-4" type="submit">
                        <i class="bx bx-save me-1"></i> Perbarui Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.skpi.sertifikasi') }}" class="btn btn-outline-secondary mt-4 ms-2">
                        Batal
                    </a>
                </form>
            </div>
        </div>

    </div>
    @endsection