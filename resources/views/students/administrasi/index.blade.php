@extends('layouts.mahasiswa')
@section('title', 'Kartu Hasil Studi')
@section('content')
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="d-flex align-items-center row g-0">
                <!-- Content Section -->
                <div class="col-md-7">
                    <div class="card-body">
                        <!-- Title -->
                        <h5 class="card-title text-primary mb-3 fw-bold">Administrasi Pembayaran Mahasiswa</h5>
                        <!-- Description -->
                        <p class="mb-4 text-muted" style="line-height: 1.6;">
                            Berikut adalah informasi administrasi pembayaran mahasiswa yang terdaftar di sistem.
                        </p>

                        <!-- Conditional CTA Button -->

                    </div>
                </div>

                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <div class="p-3">

                        <img src="../assets/img/illustrations/calender.png" class="img-fluid"
                            alt="Illustration for evening schedule" style="max-height: 200px;">

                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="card">
                <div class="card-header">
                    <h3>Administrasi Pembayaran</h3>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Mahasiswa</th>
                                <th>Semester</th>
                                <th>Tahun Akademik</th>
                                <th>Tenor Pembayaran</th>
                                <th>Jumlah Tagihan</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pembayaran as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->mahasiswa->nama }}</td>
                                <td>{{ $item->semester }}</td>
                                <td>{{ $item->tahunAjaran->nama }}</td>
                                <td>{{ $item->tenorPembayaran->tenor }}</td>
                                <td>Rp{{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->jatuh_tempo)->format('d-m-Y') }}</td>
                                <td>
                                    <span class="badge {{ $item->status == 'Lunas' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $item->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection