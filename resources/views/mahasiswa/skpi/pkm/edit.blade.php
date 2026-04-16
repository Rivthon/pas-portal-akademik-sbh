@extends('layouts.mahasiswa')
@section('title', 'Edit Program Kreativitas Mahasiswa')
@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title">Edit Program Kreativitas Mahasiswa</h5>

                <a href="{{ route('mahasiswa.skpi.pkm') }}" class="btn btn-outline-secondary">
                    <i class="bx bx-arrow-back"></i> Kembali
                </a>
            </div>
            <p class="card-text">Silakan isi data PKM / kompetensi yang telah Anda ikuti.</p>
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

                <form action="{{ route('mahasiswa.update.pkm', $pkm->id) }}" method="POST" novalidate>
                    @csrf
                    @method('PUT')
                    <div class="row gy-3">
                        {{-- Kolom 1 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Jenis PKM <span class="text-danger">*</span></label>
                            <select name="jenis_pkm" class="form-select" required>
                                <option value="" disabled {{ old('jenis_pkm', $pkm->jenis_pkm) ? '' : 'selected' }}>--
                                    pilih --</option>
                                <option value="PKM-P" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-P')>PKM-P
                                </option>
                                <option value="PKM-K" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-K')>PKM-K
                                </option>
                                <option value="PKM-M" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-M')>PKM-M
                                </option>
                                <option value="PKM-T" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-T')>PKM-T
                                </option>
                                <option value="PKM-GT" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-GT')>PKM-GT
                                </option>
                                <option value="PKM-AI" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-AI')>PKM-AI
                                </option>
                                <option value="PKM-R" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-R')>PKM-R
                                </option>
                                <option value="PKM-PI" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-PI')>PKM-PI
                                </option>
                                <option value="PKM-KC" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-KC')>PKM-KC
                                </option>
                                <option value="PKM-PM" @selected(old('jenis_pkm', $pkm->jenis_pkm) == 'PKM-PM')>PKM-PM
                                </option>
                                <option value="PKM-VGK" @selected(old('jenis_pkm', $pkm->jenis_pkm) ==
                                    'PKM-VGK')>PKM-VGK</option>
                                <option value="Lainnya" @selected(old('jenis_pkm', $pkm->jenis_pkm) ==
                                    'Lainnya')>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label">Judul Kegiatan <span class="text-danger">*</span></label>
                            <input type="text" name="judul_kegiatan" class="form-control"
                                value="{{ old('judul_kegiatan', $pkm->judul_kegiatan) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                            <input type="text" name="penyelenggara" class="form-control"
                                value="{{ old('penyelenggara', $pkm->penyelenggara) }}" required>
                        </div>

                        {{-- Kolom 2 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">Prestasi</label>
                            <input type="text" name="prestasi" class="form-control"
                                value="{{ old('prestasi', $pkm->prestasi) }}">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ old('tanggal', $pkm->tanggal) }}" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">File Laporan (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_laporan" class="form-control"
                                value="{{ old('file_laporan', $pkm->file_laporan) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
                        </div>

                        {{-- Kolom 3 --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label">File Lampiran (Google Drive) <span
                                    class="text-danger">*</span></label>
                            <input type="url" name="file_lampiran" class="form-control"
                                value="{{ old('file_lampiran', $pkm->file_lampiran) }}"
                                placeholder="https://drive.google.com/..." required>
                            <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                (Anyone with the link can view).</small>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label">Catatan Validator</label>
                            <textarea name="catatan_validator" class="form-control fw-bold" rows="3"
                                readonly>{{ old('catatan_validator', $pkm->catatan_validator) }}</textarea>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-4" type="submit">
                        <i class="bx bx-save me-1"></i> Perbarui Pengajuan
                    </button>
                    <a href="{{ route('mahasiswa.skpi.pkm') }}" class="btn btn-outline-secondary mt-4 ms-2">
                        Batal
                    </a>
                </form>
            </div>
        </div>

    </div>
    @endsection