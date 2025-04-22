@extends('layouts.mahasiswa')
@section('title', 'Jadwal Kuliah')
@section('content')

<div class="row mt-4">
    @php
    $user = auth()->guard('mahasiswa')->user();
    $krs_status = $user->status_krs;
    @endphp
    @if($krs_status == 0)
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
                        Kartu Rencana Studi (Mahasiswa)
                    </h5>
                    <!-- Conditional Alert -->
                    <div class="alert alert-warning d-flex justify-content-between align-items-center mb-3"
                        role="alert">
                        <div>
                            <strong>Perhatian:</strong> Status KRS belum aktif.
                        </div>
                        <i class="bx bx-info-circle fs-4 text-warning"></i>
                    </div>
                    <!-- Description -->
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Temukan kartu rencana studi Anda dengan mudah. Kartu ini mencakup informasi mata kuliah,
                        semester, dan jumlah SKS yang diambil.
                    </p>
                    <!-- Additional Message -->
                    <p class="text-danger fw-bold mb-4">
                        Silahkan untuk melakukan verifikasi pembayaran kepada BAUK agar bisa mengisi dan
                        melihat KRS (Kartu Rencana Studi).
                    </p>
                    <!-- CTA Button -->
                    <div class="mb-3">
                        <a href="#" class="btn btn-primary disabled" aria-disabled="true">
                            Cetak Jadwal Ujian Tengah Semester
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @else($krs_status == 1)
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">
                            Kartu Rencana Studi (Mahasiswa)
                        </h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Sebelum Memilih Kartu Rencana Studi, Pastikan Anda Sudah Memilih Mata Kuliah Yang Akan
                            Diambil, dan Perhatikan Semester yang sedang di tempuh.
                        </p>
                        <!-- CTA Button -->
                        <div class="mb-3">
                            <a href="{{route('mahasiswa.status.krs.index')}}" class="btn btn-primary">
                                Lihat Status KRS
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
        <div class="card bg-light">
            <div class="card-body">
                <div class="table-responsive text-nowrap">
                    <form id="krsForm">

                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>#</th>
                                    <th>No</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Semester</th>
                                    <th>Kategori</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($krs as $key => $item)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="krs[]" value="{{ $item->kurikulum_id }}"
                                            data-krs-id="{{ $item->krs_id ?? '' }}">
                                    </td>
                                    <td>{{ $key + 1 }}</td>
                                    <td>{{ $item->mataKuliah->nama }}</td>
                                    <td>{{ $item->mataKuliah->sks }}</td>
                                    <td>{{ $item->mataKuliah->smt }}</td>
                                    <td>
                                        <span
                                            class="badge
                                                {{ $item->mataKuliah->kategori_mk == 1 ? 'bg-success' : ($item->kategori_mk == 0 ? 'bg-primary' : 'bg-secondary') }}">
                                            {{ $item->mataKuliah->kategori_mk == 1 ? 'Pilihan' :
                                            ($item->mataKuliah->kategori_mk ==
                                            0 ? 'Wajib' : 'Tidak Diketahui') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada jadwal tersedia untuk semester
                                        ini.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </form>
                    <div class="mt-3">
                        <button id="saveKrs" class="btn btn-primary">Simpan KRS</button>
                    </div>
                    <div class="mt-2">
                        <strong>Total SKS: <span id="totalSks">0</span></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>
</div>
@endsection