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
                <div class="input-group">
                    <input type="text" name="search" id="search-matakuliah" class="form-control"
                        placeholder="Cari nama mata kuliah atau program studi" value="{{ request()->get('search') }}">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>


        </div>

        <!-- Wrapper List Mata Kuliah -->
        <div id="matakuliah-list">
            @include('matakuliah.partial_list', ['matakuliah' => $matakuliah])
        </div>
    </div>
</div>

@endsection