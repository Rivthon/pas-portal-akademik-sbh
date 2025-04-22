@extends('layouts.master')
@section('title', 'Tenor Pembayaran')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Tenor Pembayaran
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Tenor pembayaran adalah jangka waktu pembayaran yang diberikan kepada mahasiswa untuk membayar biaya
                    kuliah. Tenor pembayaran ini dapat disesuaikan dengan kemampuan mahasiswa dalam membayar biaya
                    kuliah.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <a href="{{ route('admin.tenor-pembayaran.create') }}" class="btn btn-primary">
                        Tambah
                    </a>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="../assets/img/illustrations/kartu-study.png" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height:200px;">
            </div>
        </div>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Cari Tenor Pembayaran</h5>
    </div>
    <div class="card-body">
        <!-- Form Pencarian Mata Kuliah dengan AJAX -->
        <form id="search-form" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" id="search-tenor" class="form-control"
                    placeholder="Cari tenor atau program studi" value="{{ request()->get('search') }}">
                <button type="submit" class="btn btn-primary">Cari</button>
            </div>
        </form>
        <!-- Wrapper List Mata Kuliah -->
        <div id="tarif-list">
            @include('tenor-pembayaran.partial_list', ['tenor' => $tenor])
        </div>
    </div>
</div>

@endsection