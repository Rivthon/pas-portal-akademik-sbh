@extends('layouts.master')
@section('title', 'Data Mahasiswa')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-8">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar mahasiswa yang terdaftar di sistem. Anda dapat melakukan import data mahasiswa
                    dengan mengunggah file excel yang berisi data mahasiswa.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <!-- Import Form -->
                    <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- File Upload Section -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <label for="file" class="form-label fw-bold">Upload File Excel</label>
                                <div class="input-group">
                                    <input type="file" name="file" id="file" class="form-control" required>
                                    <label class="input-group-text" for="file">Choose file</label>
                                </div>
                                <small class="text-muted">Pastikan file berformat .xlsx atau .csv</small>
                            </div>
                        </div>

                        <!-- Submit and Download Buttons on the Same Row -->
                        <div class="row">
                            <div class="col-md-8">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fa fa-upload"></i> Import Mahasiswa
                                </button>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <a href="{{ route('admin.mahasiswa.download-template') }}"
                                    class="btn btn-outline-info w-100">
                                    <i class="fa fa-download"></i> Download Contoh File
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-4 text-center">
            <div class="p-3">
                <img src="../assets/img/illustrations/mahasiswa-2.png" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <h5 class="card-title text-primary fw-bold">Pencarian Mahasiswa</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <!-- Input Search -->
                <div class="mb-3">
                    <input type="text" id="search" class="form-control" placeholder="Cari Nama, NIM, dll...">
                </div>

                <!-- Program Studi -->
                <div class="mb-3">
                    <select id="program-studi" class="form-select">
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tahun Masuk -->
                <div class="mb-3">
                    <select id="tahun-masuk" class="form-select">
                        <option value="">-- Pilih Tahun Masuk --</option>
                        @for ($year = 2019; $year <= date('Y'); $year++) <option value="{{ $year }}">{{ $year }}
                            </option>
                            @endfor
                    </select>
                </div>

                <!-- Status -->
                <div class="mb-3">
                    <select id="status" class="form-select">
                        <option value="">-- Pilih Status --</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Nonaktif">Nonaktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Dropout">Dropout</option>
                    </select>
                </div>

                <!-- Tombol Cari -->
                <div class="d-grid">
                    <button id="search-btn" class="btn btn-primary">
                        <i class="bx bx-search"></i> Cari Mahasiswa
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <div id="alert-container" class="mt-3"></div>

        <!-- Tabel Hasil Pencarian -->
        <div class="table-responsive mt-4" id="table-container"></div>
    </div>
</div>

@endsection