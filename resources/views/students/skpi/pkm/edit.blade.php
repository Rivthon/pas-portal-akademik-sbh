@extends('layouts.mahasiswa')
@section('title', 'Edit Penguasaan Bahasa Asing')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Penguasaan Bahasa Asing</h5>

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
                <form action="{{ route('mahasiswa.update.bahasa', $bahasa->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Bahasa <span class="text-danger">*</span></label>
                            <input type="text" name="nama_bahasa" class="form-control"
                                value="{{ old('nama_bahasa', $bahasa->nama_bahasa) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Level <span class="text-danger">*</span></label>
                            <select name="level" class="form-select" required>
                                <option value="" disabled {{ old('level', $bahasa->level) ? '' : 'selected' }}>-- pilih
                                    --</option>
                                @foreach(['Beginner', 'Elementary', 'Intermediate', 'Upper Intermediate',
                                'Advanced'] as $t)
                                <option value="{{ $t }}" @selected(old('level', $bahasa->
                                    level) ==
                                    $t)>
                                    {{ $t }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" class="form-control"
                                value="{{ old('penyelenggara', $bahasa->penyelenggara) }}" required>
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Skor <span class="text-danger">*</span></label>
                            <input type="number" name="skor" class="form-control"
                                value="{{ old('skor', $bahasa->skor) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_tes" class="form-control"
                                value="{{ old('tanggal_tes', $bahasa->tanggal_tes) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Link Sertifikat (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_sertifikat" class="form-control"
                                placeholder="https://drive.google.com/..."
                                value="{{ old('file_sertifikat', $bahasa->file_sertifikat) }}" required>
                            <small class="text-muted">
                                Pastikan link dapat diakses oleh siapa saja yang memiliki link (Anyone with the link can
                                view).
                            </small>
                        </div>


                    </div>
                    <div class="col-12 mt-3">
                        <label class="form-label">Catatan Validator <span class="text-danger">*</span></label>
                        <textarea name="catatan_validator" class="form-control fw-bold" rows="3" required
                            readonly>{{ old('catatan_validator', $bahasa->catatan_validator) }}</textarea>
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