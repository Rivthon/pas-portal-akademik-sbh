@extends('layouts.mahasiswa')
@section('title', 'Nilai Ujian Tengah Semester')
@section('content')

<!-- Title Card -->
<div class="card shadow-sm mb-4">
    <div class="row g-0 align-items-center">
        <!-- Text Section -->
        <div class="col-md-7">
            <div class="card-body">
                <h5 class="card-title text-primary fw-bold mb-3">
                    Nilai UTS (Mahasiswa)
                </h5>
                <p class="text-muted mb-4" style="line-height: 1.6;">
                    <strong>{{ $mahasiswa->nama }} <br>
                        {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun Ajaran: {{
                        $ta->nama }}</strong>
                    <hr>
                    Nilai UTS ini merupakan nilai yang diperoleh dari ujian tengah semester yang diambil oleh
                    mahasiswa.
                </p>
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

<!-- Table Card -->
<div class="card bg-light shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th style="text-align: center;">NO</th>
                        <th>Kode Kuliah</th>
                        <th>Mata Kuliah</th>
                        <th>SKS</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nilai as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->kurikulum->mataKuliah->matakuliah_id }}</td>
                        <td>{{ $item->kurikulum->mataKuliah->nama }}</td>
                        <td class="text-center">{{ $item->kurikulum->mataKuliah->sks }}</td>
                        <td class="text-center">{{ $item->uas }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Tidak ada mata kuliah yang diambil.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection