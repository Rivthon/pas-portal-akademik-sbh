@extends('layouts.mahasiswa')
@section('title', 'Edit Sertifikasi / Kompetensi')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Kegiatan Tambahan</h5>

                <a href="{{ route('mahasiswa.skpi.sertifikasi') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <p class="card-text">Silakan isi data Kegiatan Tambahan yang telah Anda ikuti.</p>
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

                <form action="{{ route('mahasiswa.update.tambahan', $tambahan->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori" class="form-select" required>
                                <option value="" disabled {{ old('kategori', $tambahan->kategori) ? '' : 'selected'
                                    }}>-- pilih --</option>
                                <option value="Akademik" @selected(old('kategori', $tambahan->kategori) ==
                                    'Akademik')>Akademik</option>
                                <option value="Non Akademik" @selected(old('kategori', $tambahan->kategori) == 'Non
                                    Akademik')>Non Akademik</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="nama_kegiatan" class="form-control"
                                value="{{ old('nama_kegiatan', $tambahan->nama_kegiatan) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Bentuk Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="bentuk_kegiatan" class="form-control"
                                value="{{ old('bentuk_kegiatan', $tambahan->bentuk_kegiatan) }}" required>
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                            <select name="tingkat" class="form-select" required>
                                @foreach(['Lokal','Regional','Nasional','Internasional'] as $t)
                                <option value="{{ $t }}" @selected(old('tingkat', $tambahan->tingkat) == $t)>
                                    {{ $t }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" class="form-control"
                                value="{{ old('penyelenggara', $tambahan->penyelenggara) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Peran <span class="text-danger">*</span></label>
                            <input type="text" name="peran" class="form-control"
                                value="{{ old('peran', $tambahan->peran) }}" required>
                        </div>

                        {{-- Kolom 3 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', $tambahan->tanggal) }}" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">File Sertifikat (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_sertifikat" class="form-control"
                                value="{{ old('file_sertifikat', $tambahan->file_sertifikat) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">File Lampiran (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_lampiran" class="form-control"
                                value="{{ old('file_lampiran', $tambahan->file_lampiran) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
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