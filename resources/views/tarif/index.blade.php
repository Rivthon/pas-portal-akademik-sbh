@extends('layouts.master')
@section('title', 'Kurikulum')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Tarif Per Semester
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Daftar tarif per semester yang digunakan untuk menghitung biaya kuliah mahasiswa.
                </p>
                <!-- CTA Button -->
                {{-- <div class="mb-3">
                    <a href="{{ route('admin.tarif.create') }}" class="btn btn-primary">
                        Tambah Tarif
                    </a>
                </div> --}}
                <form action="{{ route('admin.tarif.import') }}" method="POST" enctype="multipart/form-data">
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
                                <i class="fa fa-upload"></i> Import Tarif Mahasiswa
                            </button>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="{{ route('admin.tarif.download-template') }}" class="btn btn-outline-info w-100">
                                <i class="fa fa-download"></i> Download Contoh File
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Cari Tarif Per Semester</h5>
    </div>
    <div class="card-body">
        <!-- Form Pencarian Mata Kuliah dengan AJAX -->
        <form id="search-form" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-tarif" class="form-control"
                    placeholder="Cari tarif atau program studi" value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
        <!-- Wrapper List Mata Kuliah -->
        <div id="tarif-list">
            @include('tarif.partial_list', ['tarif' => $tarif])
        </div>
    </div>
</div>

@endsection