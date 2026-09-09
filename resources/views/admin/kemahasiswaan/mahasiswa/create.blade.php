@extends('layouts.master')

@section('content')
<div class="container-fluid px-4 py-3">
    {{-- Header & Tombol Navigasi --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Tambah Mahasiswa Baru</h3>
            <p class="text-muted mb-0 small">Lengkapi formulir di bawah ini untuk mendaftarkan mahasiswa ke dalam sistem.</p>
        </div>
        <a href="{{ route('admin.mahasiswa.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- Alert Error Global --}}
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
        <div class="d-flex align-items-center">
            <i class="fa-solid fa-triangle-exclamation fs-4 me-3"></i>
            <div>
                <strong>Terdapat beberapa kesalahan pengisian:</strong>
                <ul class="mb-0 mt-1 ps-3 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    {{-- Card Form Utama --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('admin.mahasiswa.store') }}" method="POST">
                @csrf

                {{-- Bagian: Data Identitas --}}
                <div class="border-bottom pb-2 mb-4">
                    <h6 class="text-uppercase text-muted fw-semibold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-user me-1 text-primary"></i> Data Identitas
                    </h6>
                </div>

                <div class="row g-3 mb-4">
                    {{-- Nama --}}
                    <div class="col-md-4">
                        <label for="nama" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" id="nama" 
                               class="form-control @error('nama') is-invalid @enderror"
                               placeholder="Contoh: Budi Santoso" 
                               value="{{ old('nama') }}" required>
                        @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- NIM --}}
                    <div class="col-md-4">
                        <label for="nim" class="form-label fw-semibold small">NIM <span class="text-danger">*</span></label>
                        <input type="text" name="nim" id="nim" 
                               class="form-control @error('nim') is-invalid @enderror"
                               placeholder="Contoh: 2110114001" 
                               value="{{ old('nim') }}" required>
                        @error('nim')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="col-md-4">
                        <label for="email" class="form-label fw-semibold small">
                            Email <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="Kosongkan untuk email otomatis" 
                               value="{{ old('email') }}">
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Bagian: Informasi Akademik --}}
                <div class="border-bottom pb-2 mb-4">
                    <h6 class="text-uppercase text-muted fw-semibold mb-0" style="font-size: 0.8rem; letter-spacing: 0.5px;">
                        <i class="fa-solid fa-graduation-cap me-1 text-primary"></i> Informasi Akademik
                    </h6>
                </div>

                <div class="row g-3 mb-4">
                    {{-- Program Studi --}}
                    <div class="col-md-4">
                        <label for="jurusan_id" class="form-label fw-semibold small">Program Studi <span class="text-danger">*</span></label>
                        <select name="jurusan_id" id="jurusan_id" class="form-select @error('jurusan_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach ($programStudi as $program)
                                <option value="{{ $program->jurusan_id }}" @selected(old('jurusan_id') == $program->jurusan_id)>
                                    {{ $program->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('jurusan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kelas --}}
                    <div class="col-md-4">
                        <label for="kelas" class="form-label fw-semibold small">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas" id="kelas" class="form-select @error('kelas') is-invalid @enderror" required>
                            <option value="">-- Pilih Kelas --</option>
                            <option value="pagi" @selected(old('kelas') === 'pagi')>Reguler A (Pagi)</option>
                            <option value="karyawan" @selected(old('kelas') === 'karyawan')>Reguler B (Karyawan)</option>
                        </select>
                        @error('kelas')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Dosen PA --}}
                    <div class="col-md-4">
                        <label for="dosen_id" class="form-label fw-semibold small">
                            Dosen Pembimbing Akademik <span class="text-muted fw-normal">(opsional)</span>
                        </label>
                        <select name="dosen_id" id="dosen_id" class="form-select @error('dosen_id') is-invalid @enderror">
                            <option value="">-- Pilih Pembimbing --</option>
                            @foreach ($dosen as $pembimbing)
                                <option value="{{ $pembimbing->dosen_id }}" @selected(old('dosen_id') == $pembimbing->dosen_id)>
                                    {{ $pembimbing->nama }}
                                </option>
                            @endforeach
                        </select>
                        @error('dosen_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="reset" class="btn btn-light px-4">
                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Simpan Mahasiswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection