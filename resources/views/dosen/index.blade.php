@extends('layouts.master')
@section('title', 'Program Studi')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-8">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Dosen Pengajar
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar dosen yang terdaftar di sistem. Silakan tambahkan dosen baru jika diperlukan.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    @can('dosen-create')
                    <a href="{{ route('admin.dosen.create') }}" class="btn btn-primary">
                        Tambah Dosen
                    </a>
                    @endcan

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
<div class="card mt-5">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Dosen</h5>
    </div>
    <div class="container mt-2">
        <!-- Form Pencarian -->
        <form id="search-dosen" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-input" class="form-control"
                    placeholder="Cari nama Dosen atau program studi" value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
    </div>
    <!-- Wrapper List Mata Kuliah -->
    <div id="dosen-list">
        @include('dosen.partials_list', ['dosen' => $dosen])
    </div>
    @endsection