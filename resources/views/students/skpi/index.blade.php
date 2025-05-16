@extends('layouts.mahasiswa')
@section('title', 'Daftar Permintaan Mahasiswa')

@section('content')
<div class="container">
    <!-- Card Section -->
    <div class="card shadow-sm mb-4">
        <div class="d-flex align-items-center item g-0">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="card-title text-primary mb-3 fw-bold">
                        Aktivitas dan Prestasi SKPI
                    </h5>
                    <p class="text-muted mb-4" style="line-height: 1.6;">

                        <hr>
                        <strong>{{ $mahasiswa->nama }} <br>
                            {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun Ajaran: {{
                            $ta->nama }}</strong>
                        <hr>
                        Aktivitas dan prestasi SKPI ini digunakan untuk mengajukan permohonan pengisian data SKPI.
                        <br>
                        {{--
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        Apabila tidak terkirim, cek kembali nomor telepon WA yang terdaftar di sistem. Hubungi
                        ICT untuk info lebih lanjut.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div> --}}
                    </p>
                    <!-- CTA Button -->
                    <div class="mb-3">
                        <a href="#" class="btn btn-success">Syarat dan Ketentuan</a>
                    </div>
                </div>
            </div>
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-3">
                    <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                        alt="Illustration of a schedule" style="max-height: 200px;">
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
</div>

@endsection