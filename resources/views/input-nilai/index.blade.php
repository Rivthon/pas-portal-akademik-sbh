@extends('layouts.master')
@section('tittle', 'Input Nilai Mahasiswa')
@section('content')
<div class="card p-4 shadow-sm">
    <div class="row g-4 align-items-center">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <h5 class="card-title text-primary fw-bold mb-3">Input Nilai Mahasiswa</h5>
                <p class="text-muted" style="line-height: 1.6;">
                    Pastikan Anda telah memilih program studi dan mata kuliah yang bersangkutan.
                    Setelah memilih mata kuliah, daftar mahasiswa yang mengambil mata kuliah tersebut akan muncul.
                </p>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid" alt="Illustration"
                style="max-height: 200px;">
        </div>
    </div>
</div>

<div class="card mt-4 p-4 shadow-sm">
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light border-bottom">
            <h4 class="mb-0 fs-5 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Input Nilai
            </h4>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="tahun-ajaran" class="form-label fw-bold">
                        <i class="fas fa-calendar-alt me-1"></i> Tahun Ajaran
                    </label>
                    <select id="tahun-ajaran" class="form-select">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach ($tahunAjaran as $ta)
                        <option value="{{ $ta->ta_id }}">{{ $ta->nama }} ({{ $ta->semester }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="program-studi" class="form-label fw-bold">
                        <i class="fas fa-graduation-cap me-1"></i> Program Studi
                    </label>
                    <select id="program-studi" class="form-select" disabled>
                        <option value="">-- Pilih Prodi --</option>
                        @foreach ($programStudi as $ps)
                        <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label for="mata-kuliah" class="form-label fw-bold">
                        <i class="fas fa-book me-1"></i> Mata Kuliah
                    </label>
                    <select id="mata-kuliah" class="form-select select2">
                        <option value="">-- Pilih Mata Kuliah --</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" id="clear-selection" class="btn btn-outline-danger w-100">
                        <i class="fas fa-times me-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light border-bottom">
            <h4 class="mb-0 fs-5 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>List Mahasiswa
            </h4>
        </div>
        <div class="container mb-5">
            <form id="form-nilai" method="POST" action="{{ route('admin.nilai.save') }}">
                @csrf
                <div class="table-responsive">
                    <table id="table-mahasiswa" class="table table-bordered mt-3">
                        <thead class="table-primary">
                            <tr>
                                <th rowspan="2" style="width: 50px; text-align: center;">#</th>
                                <th rowspan="2" style="text-align: center;">Nama</th>
                                <th colspan="7" style="text-align: center;">Nilai</th>
                            </tr>
                            <tr>
                                <th style="width: 150PX; text-align: center;">UTS</th>
                                <th style="width: 150PX; text-align: center;">UAS</th>
                                <th style="width: 150PX; text-align: center;">TUGAS</th>
                                <th style="width: 150PX; text-align: center;">ABSEN</th>
                                <th style="width: 150PX; text-align: center;">PRAKTIK</th>
                                <th style="width: 150PX; text-align: center;">Absolute</th>
                                <th style="width: 150PX; text-align: center;">Huruf Mutu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Silakan pilih mata kuliah</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <button type="submit" class="btn btn-primary mt-3" style="display: none;" id="save-nilai">Simpan
                    Nilai</button>
            </form>
        </div>

    </div>

</div>
@endsection
