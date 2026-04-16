@extends('layouts.dosen')
@section('title', 'Tambah RPS')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/error-404.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">

                </h5>
                <!-- Conditional Alert -->
                <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3" role="alert">
                    <div>
                        <strong>Perhatian:</strong> Masih dalam proses pengembangan.
                    </div>
                    <i class="bx bx-info-circle fs-4 text-warning"></i>
                </div>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">

                </p>
                <!-- Additional Message -->
                <p class="text-danger fw-bold mb-4">
                    Silahkan untuk menunggu pengembangan lebih lanjut.
                </p>
                <!-- CTA Button -->
                <div class="mb-3">
                    <a href="#" class="btn btn-primary disabled" aria-disabled="true">
                        Tambah RPS
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection