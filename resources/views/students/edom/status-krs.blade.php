@extends('layouts.mahasiswa')
@section('title', 'Jadwal Kuliah')
@section('content')
<div class="container">
    <div class="row mt-4">
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
                                Status Kartu Rencana Studi
                            </p>
                            <!-- CTA Button -->
                            <div class="mb-3">
                                <a href="{{route('mahasiswa.krs.cetak-kapro')}}" class="btn btn-primary">
                                    Cetak KAPRO
                                </a>
                                <a href="{{route('mahasiswa.krs.cetak-krs-baak')}}" class="btn btn-primary">
                                    Cetak BAAK
                                </a>
                                <a href="{{route('mahasiswa.krs.cetak-krs-dospem')}}" class="btn btn-primary">
                                    Cetak DOSPEM
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
                    <h4 class="card-title">List Kartu Rencana Studi</h4>
                    <div class="table-responsive text-nowrap">
                        <table class="table table-hover">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Kode Mata Kuliah</th>
                                    <th>Nama Mata Kuliah</th>
                                    <th>SKS</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($krs as $index => $item)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                                    <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                                    <td>{{ $item->kurikulum->mataKuliah->sks }}</td>
                                    <td>
                                        <a href="{{ route('mahasiswa.hapus.krs', $item->krs_id) }}"
                                            class="btn btn-danger btn-sm">Hapus</a>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center">Tidak ada mata kuliah yang diambil.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection