@extends('layouts.dosen')
@section('title', 'Jadwal Mengajar Dosen')
@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-0 align-items-center">
                    <!-- Content Section -->
                    <div class="col-md-7">
                        <h5 class="card-title text-primary mb-3 fw-bold">Jadwal Perkuliahan </h5>
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Berikut adalah jadwal perkuliahan yang telah ditetapkan untuk tahun ajaran {{
                            $activeTA->nama
                            }} - {{ $activeTA->semester }}. Jadwal ini dapat berubah sewaktu-waktu, pastikan untuk
                            selalu memantau jadwal perkuliahan Anda.
                        </p>

                    </div>

                    <!-- Image Section -->
                    <div class="col-md-5 text-center">
                        <img src="{{ asset('assets/img/illustrations/calender.png') }}" class="img-fluid"
                            alt="Illustration for morning schedule" style="max-height: 200px;">
                    </div>
                </div>

                <!-- Selection Section -->
            </div>
        </div>
        <div class="card shadow-sm mb-4 mt-4">
            <div class="container mt-4">

                <!-- 🔍 Input Pencarian -->
                <div class="mb-4">
                    <input type="text" id="searchPraktik" class="form-control"
                        placeholder="Cari berdasarkan nama mata kuliah...">
                </div>

                <div id="jadwalContainer">
                    @include('dosen.jadwal-praktik.partial_list')
                    <!-- Menampilkan jadwal dengan partial view -->
                </div>
            </div>
        </div>


    </div>

    @endsection