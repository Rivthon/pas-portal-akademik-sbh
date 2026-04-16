@extends('layouts.mahasiswa')
@section('title', 'Kartu Hasil Studi')
@section('content')
<div class="container">
    <div class="item mt-4">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="d-flex align-items-center item g-0">
                    <!-- Content Section -->
                    <div class="col-md-7">
                        <div class="card-body">
                            <!-- Title -->
                            <h5 class="card-title text-primary mb-3 fw-bold">
                                Evaluasi Dosen Mengajar (EDOM)
                            </h5>
                            <!-- Description -->
                            <p class="mb-4 text-muted" style="line-height: 1.6;">
                                {{ $mahasiswa->nama }} / Tahun Ajaran: {{
                                $activeTA->nama }} ({{ $activeTA->semester }})
                            </p>
                            <!-- CTA Button -->
                            <div class="mb-3">
                                Selamat datang di halaman Evaluasi Dosen Mengajar. Mohon untuk memilih dosen terlebih
                                dahulu sebelum mengisi EDOM.
                                Setelah semua selesai diisi, klik tombol submit untuk menyelesaikan pengisian EDOM.
                            </div>
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>
                    </div>
                    <!-- Image Section -->
                    <div class="col-md-5 text-center">
                        <div class="p-3">
                            <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid"
                                alt="Illustration of a schedule" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="card bg-light">
                <div class="card-body">

                    <div class="table-responsive text-nowrap">
                        @if ($mahasiswa->status_edom == 1)
                        <div class="container mt-4 mb-4">
                            <a href="{{ route('mahasiswa.kartu-hasil.index') }}" class="btn btn-primary">Lihat KHS</a>
                        </div>

                        @endif
                        @if ($allFilled)
                        <div class="alert alert-success text-center">
                            <i class="bx bxs-check-circle"></i>
                            Terima kasih, Anda telah mengisi semua EDOM.
                            Untuk melihat Kartu Hasil Studi (KHS) Anda, silakan konfirmasi pengisian EDOM.
                        </div>
                        @else
                        <div class="alert alert-info text-center my-3">
                            <i class="bx bxs-info-circle"></i>
                            Silakan isi EDOM untuk setiap dosen yang dipilih.
                            Setelah semua EDOM terisi, Anda dapat mengonfirmasi pengisian untuk melihat Kartu Hasil
                            Studi (KHS).
                        </div>
                        @endif
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th>#</th>
                                        <th>Kode Mata Kuliah</th>
                                        <th>Nama Mata Kuliah</th>
                                        <th>Dosen Teori</th>
                                        <th>Dosen Praktik</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($krsList as $index => $kurikulum)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $kurikulum['kode_matakuliah'] }}</td>
                                        <td>{{ $kurikulum['nama_matakuliah'] }}</td>

                                        <!-- Dosen Teori -->
                                        <td>
                                            @php
                                            $dosenTeori = collect($kurikulum['dosen'])->where('jenis_dosen', 'teori');
                                            @endphp

                                            @if ($dosenTeori->isNotEmpty())
                                            @foreach ($dosenTeori as $dosen)
                                            <div>
                                                <a class="badge bg-primary"
                                                    href="{{ route('mahasiswa.edom.form', ['krs_id' => $kurikulum['krs_id'], 'dosen_id' => $dosen['id']]) }}">
                                                    {{ $dosen['nama'] }}
                                                </a>

                                                @if ($dosen['is_rated'])
                                                <i class="bx bxs-check-circle text-success"></i>
                                                @else
                                                <span class="badge bg-warning">Belum Dinilai</span>
                                                @endif
                                            </div>
                                            @endforeach
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <!-- Dosen Praktik -->
                                        <td>
                                            @php
                                            $dosenPraktik = collect($kurikulum['dosen'])->where('jenis_dosen',
                                            'praktik');
                                            @endphp

                                            @if ($dosenPraktik->isNotEmpty())
                                            @foreach ($dosenPraktik as $dosen)
                                            <div>
                                                <a class="badge bg-success"
                                                    href="{{ route('mahasiswa.edom.form', ['krs_id' => $kurikulum['krs_id'], 'dosen_id' => $dosen['id']]) }}">
                                                    {{ $dosen['nama'] }}
                                                </a>

                                                @if ($dosen['is_rated'])
                                                <i class="bx bxs-check-circle text-success"></i>
                                                @else
                                                <span class="badge bg-warning">Belum Dinilai</span>
                                                @endif
                                            </div>
                                            @endforeach
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">Tidak ada data mata kuliah
                                            yang
                                            ditemukan.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($allFilled)

                    @if ($mahasiswa->status_edom != 1)
                    <div class="d-flex justify-content-center mt-4 mb-4">
                        <form action="{{ route('mahasiswa.edom.konfirmasi') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success">Konfirmasi Pengisian EDOM</button>
                        </form>
                    </div>
                    @endif

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push(('script'))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
        });
        });
</script>
@endpush