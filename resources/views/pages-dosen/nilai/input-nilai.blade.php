@extends('layouts.dosen')
@section('title', 'Input Nilai Dosen')
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
    <h4 class="mb-3">Input Nilai</h4>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Tahun Ajaran</label>
                <select id="tahun-ajaran" class="form-select w-100">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    @foreach ($tahunAjaran as $ta)
                    <option value="{{ $ta->ta_id }}">{{ $ta->nama }} ({{ $ta->semester }})</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Program Studi</label>
                <select id="program-studi" class="form-select w-100" disabled>
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Pilih Mata Kuliah</label>
                <select id="mata-kuliah" class="form-select select2 w-100" disabled>
                    <option value="">-- Pilih Mata Kuliah --</option>
                </select>
            </div>
        </div>
    </div>



    <h5 class="mt-4">Daftar Mahasiswa</h5>
    <form id="form-nilai" method="POST" action="{{ route('dosen.nilai.save') }}">
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
                        <td colspan="6" class="text-center text-muted">Silakan pilih mata kuliah</td>
                    </tr>

                </tbody>
            </table>
        </div>
        <button type="submit" class="btn btn-primary mt-3" style="display: none;" id="save-nilai">Simpan Nilai</button>
    </form>
</div>
@endsection
