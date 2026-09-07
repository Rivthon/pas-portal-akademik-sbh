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
                    Kurikulum (Mahasiswa)
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah jadwal ujian tengah semester yang telah ditetapkan oleh program studi.
                    Silakan
                    pilih program studi dan semester untuk melihat jadwal ujian tengah semester.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary">
                        Tambah Kurikulum
                    </a>
                </div>
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
    <div class="card-body">
        <!-- Form Pencarian -->
        <div class="card-header">
            <h5 class="mb-0">Cari Kurikulum</h5>
        </div>
        <div class="card-body">
            <!-- Form Pencarian Mata Kuliah dengan AJAX -->
            <form id="search-form" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-7">
                        <label for="search-matakuliah" class="form-label fw-semibold">Pencarian</label>
                        <input type="text" name="search" id="search-matakuliah" class="form-control"
                            placeholder="Cari kode, nama mata kuliah, atau program studi"
                            value="{{ request()->get('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="kategori-matakuliah" class="form-label fw-semibold">Kategori</label>
                        <select name="kategori_mk" id="kategori-matakuliah" class="form-select">
                            <option value="">Semua Kategori</option>
                            <option value="0" @selected((string) request('kategori_mk') === '0')>Wajib</option>
                            <option value="1" @selected((string) request('kategori_mk') === '1')>Pilihan</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-grid">
                        <button type="submit" class="btn btn-primary">
                            <i class="bx bx-filter-alt me-1"></i>Terapkan
                        </button>
                    </div>
                </div>
                @if(request()->filled('search') || request()->filled('kategori_mk'))
                    <div class="mt-2">
                        <a href="{{ route('admin.matakuliah.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bx bx-reset me-1"></i>Reset Filter
                        </a>
                    </div>
                @endif
            </form>


        </div>

        <!-- Wrapper List Mata Kuliah -->
        <div id="matakuliah-list">
            @include('admin.akademik.matakuliah.partial_list', ['matakuliah' => $matakuliah])
        </div>
    </div>
</div>

@endsection
