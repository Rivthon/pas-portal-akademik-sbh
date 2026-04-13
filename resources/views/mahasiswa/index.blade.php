@extends('layouts.master')
@section('title', 'Data Mahasiswa')
@section('content')

<!-- Header Info dan Import -->
<div class="card shadow-sm mb-4 border-top border-5 border-success">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-8">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-success mb-3 fw-bold">
                    <i class="bx bx-import me-2"></i>Import Data Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah manajemen seluruh data mahasiswa yang terdaftar di sistem terpusat. Anda dapat melakukan import data mahasiswa secara massal dengan mengunggah file spreadsheet (.xlsx atau .csv) yang berisi biodata mahasiswa baru.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <!-- Import Form -->
                    <form action="{{ route('admin.mahasiswa.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <!-- File Upload Section -->
                        <div class="row align-items-end g-3">
                            <div class="col-md-6">
                                <label for="file" class="form-label fw-bold text-dark">Upload File Excel</label>
                                <input type="file" name="file" id="file" class="form-control" accept=".xlsx, .csv" required>
                                <small class="text-muted"><i class="bx bx-info-circle"></i> Pastikan file berformat sesuai template</small>
                            </div>
                            
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bx bx-upload me-1"></i> Mulai Import
                                </button>
                            </div>
                            <div class="col-md-3">
                                <a href="{{ route('admin.mahasiswa.download-template') }}" class="btn btn-outline-info w-100" title="Download Template Excel Kosong">
                                    <i class="bx bx-download me-1"></i> Template
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-4 text-center d-none d-md-block">
            <div class="p-3">
                <img src="../assets/img/illustrations/mahasiswa-2.png" class="img-fluid"
                    alt="Illustration of students" style="max-height: 180px;">
            </div>
        </div>
    </div>
</div>

<!-- Main Data Board -->
<div class="card border-top border-5 border-primary shadow-sm">
    <div class="card-header bg-white pb-0 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title text-primary fw-bold mb-0">Direktori Pencarian Mahasiswa</h5>
            <small class="text-muted">Gunakan filter di bawah untuk melakukan pencarian spesifik.</small>
        </div>
    </div>
    
    <div class="card-body mt-4">
        <div class="bg-label-primary p-4 rounded mb-4">
            <div class="row g-3">
                <!-- Input Search -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-search-alt"></i> Kata Kunci</label>
                    <input type="text" id="search" class="form-control border-primary text-primary" placeholder="Ketik Nama / NIM...">
                </div>
                
                <!-- Program Studi -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-book"></i> Program Studi</label>
                    <select id="program-studi" class="form-select border-primary text-primary">
                        <option value="">Semua Program Studi</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Tahun Masuk -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-calendar-event"></i> Tahun Masuk</label>
                    <select id="tahun-masuk" class="form-select border-primary text-primary">
                        <option value="">Semua Tahun</option>
                        @for ($year = 2019; $year <= date('Y'); $year++) 
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Status -->
                <div class="col-md-3">
                    <label class="form-label fw-bold text-primary"><i class="bx bx-user-check"></i> Status Mahasiswa</label>
                    <select id="status" class="form-select border-primary text-primary">
                        <option value="">Semua Status</option>
                        <option value="Aktif">Aktif</option>
                        <option value="Lulus">Lulus</option>
                        <option value="Nonaktif">Nonaktif</option>
                        <option value="Cuti">Cuti</option>
                        <option value="Dropout">Dropout</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="row mt-3 justify-content-end">
                <div class="col-auto">
                    <button id="search-btn" class="btn btn-primary px-4 fw-bold shadow-sm">
                        <i class="bx bx-search me-1"></i> Terapkan Filter
                    </button>
                    <button id="export-btn" class="btn btn-outline-success px-4 ms-2 fw-bold shadow-sm">
                        <i class="bx bx-file me-1"></i> Export Excel
                        <input type="hidden" id="export-url" value="{{ route('admin.export') }}">
                    </button>
                </div>
            </div>
        </div>

        <!-- Alert -->
        <div id="alert-container"></div>

        <!-- Tabel Hasil Pencarian -->
        <div class="table-responsive" id="table-container" style="min-height: 250px;">
            <div class="text-center py-5">
                <i class="bx bx-table text-muted mb-3" style="font-size: 3rem;"></i><br>
                <span class="text-muted fw-semibold">Silakan klik "Terapkan Filter" untuk mulai menampilkan data mahasiswa.</span>
            </div>
        </div>
    </div>
</div>

@endsection