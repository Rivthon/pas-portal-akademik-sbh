@extends('layouts.dosen')
@section('title', 'Cetak Absensi')
@section('content')

<div class="container">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-0 align-items-center">
                <!-- Content Section -->
                <div class="col-md-7">
                    <h5 class="card-title text-primary mb-3 fw-bold">Laporan Rekap Absensi </h5>
                    <p class="mb-4 text-muted" style="line-height: 1.6;">
                        Silakan pilih mata kuliah yang ingin Anda cetak absensinya. Anda dapat memilih mata kuliah yang
                        ingin anda cetak.
                    </p>

                </div>

                <!-- Image Section -->
                <div class="col-md-5 text-center">
                    <img src="{{ asset('assets/img/illustrations/report.png') }}" class="img-fluid"
                        alt="Illustration for morning schedule" style="max-height: 200px;">
                </div>
            </div>

            <!-- Selection Section -->
        </div>
    </div>
    <div class="card-body">
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <!-- Kolom Absensi Mahasiswa -->
            <div class="col">
                <div class="card border-light">
                    <div class="card-header bg-light text-white">
                        <h6 class="mb-0"><i class="bx bx-user"></i> Rekap Absensi Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dosen.absensi.generate-pdf') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="selectMatkulMahasiswa" class="form-label fw-bold">Pilih Mata Kuliah</label>
                                <select class="form-select" id="selectMatkulMahasiswa" name="jadwal_id">
                                    <option value="" disabled selected>🔍 Pilih Mata Kuliah...</option>
                                    @foreach ($jadwalList as $semester => $jadwalPerSemester)
                                    @foreach ($jadwalPerSemester as $jadwal)
                                    <option value="{{ $jadwal['jadwal_id'] }}">
                                        📘 {{ $jadwal['nama_matakuliah'] }} (Semester {{ $jadwal['semester'] }}) (Kelas
                                        {{ $jadwal['jenis_kelas'] }})
                                    </option>
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-download"></i> Cetak Absensi Mahasiswa
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Kolom Absensi Dosen -->
            <div class="col">
                <div class="card border-light">
                    <div class="card-header bg-light text-white">
                        <h6 class="mb-0"><i class="bx bx-user-check"></i> Rekap Absensi Dosen</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dosen.generate-pdf') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="selectMatkulDosen" class="form-label fw-bold">Pilih Mata Kuliah</label>
                                <select class="form-select" id="selectMatkulDosen" name="jadwal_id">
                                    <option value="" disabled selected>🔍 Pilih Mata Kuliah...</option>
                                    @foreach ($jadwalList as $semester => $jadwalPerSemester)
                                    @foreach ($jadwalPerSemester as $jadwal)
                                    <option value="{{ $jadwal['jadwal_id'] }}">
                                        📘 {{ $jadwal['nama_matakuliah'] }} (Semester {{ $jadwal['semester'] }}) (Kelas
                                        {{ $jadwal['jenis_kelas'] }})
                                    </option>
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-download"></i> Cetak Absensi Dosen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- End Row -->
    </div>
    <div class="card-body mt-4">
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <!-- Kolom Absensi Mahasiswa -->
            <div class="col">
                <div class="card border-light">
                    <div class="card-header bg-light text-white">
                        <h6 class="mb-0"><i class="bx bx-user"></i> Rekap Absensi Praktik Mahasiswa</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dosen.absensi-praktik.generate-pdf') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="selectMatkulMahasiswa" class="form-label fw-bold">Pilih Mata Kuliah</label>
                                <select class="form-select select2" id="selectMatkulMahasiswa" name="jadwal_praktik_id">
                                    <option value="" disabled selected>🔍 Pilih Mata Kuliah...</option>
                                    @foreach ($jadwalListPraktik as $semester => $jadwalPerSemester)
                                    @foreach ($jadwalPerSemester as $jadwal)
                                    <option value="{{ $jadwal['jadwal_praktik_id'] }}">
                                        📘 {{ $jadwal['nama_matakuliah'] }} (Semester {{ $jadwal['semester_matkul'] }})
                                        (Kelas
                                        {{ $jadwal['jenis_kelas'] }})
                                    </option>
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bx bx-download"></i> Cetak Absensi Mahasiswa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Kolom Absensi Dosen -->
            <div class="col">
                <div class="card border-light">
                    <div class="card-header bg-light text-white">
                        <h6 class="mb-0"><i class="bx bx-user-check"></i> Rekap Absensi Praktik Dosen</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dosen.praktik.generate-pdf') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="selectMatkulDosen" class="form-label fw-bold">Pilih Mata Kuliah</label>
                                <select class="form-select" id="selectMatkulDosen" name="jadwal_praktik_id">
                                    <option value="" disabled selected>🔍 Pilih Mata Kuliah...</option>
                                    @foreach ($jadwalListPraktik as $semester => $jadwalPerSemester)
                                    @foreach ($jadwalPerSemester as $jadwal)
                                    <option value="{{ $jadwal['jadwal_praktik_id'] }}">
                                        📘 {{ $jadwal['nama_matakuliah'] }} (Semester {{ $jadwal['semester_matkul'] }})
                                        (Kelas
                                        {{ $jadwal['jenis_kelas'] }})
                                    </option>
                                    @endforeach
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bx bx-download"></i> Cetak Absensi Dosen
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div> <!-- End Row -->
    </div>
</div>

@endsection