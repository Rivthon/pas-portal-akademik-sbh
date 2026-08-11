@extends('layouts.dosen')
@section('title', 'Cetak Laporan Rekap Absensi')
@section('content')

<div class="container-xxl flex-grow-1 container-p-y">
    {{-- Hero Profile Card --}}
    <div class="card shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
        <div class="card-body position-relative overflow-hidden">
            <div class="row g-0 align-items-center">
                <!-- Content Section -->
                <div class="col-md-7 text-white p-3 z-2">
                    <h4 class="card-title mb-3 fw-bold text-white"><i class="bx bx-printer me-2"></i>Cetak Laporan Kehadiran</h4>
                    <p class="mb-0 text-white-50" style="line-height: 1.6;">
                        Gunakan portal ini untuk mencetak rekapitulasi daftar hadir bagi setiap kelas yang Anda ampu.<br>
                        Tersedia mode cetak untuk mahasiswa maupun form monitoring absen Anda sendiri sebagai dosen.
                    </p>
                </div>
                <!-- Image Section -->
                <div class="col-md-5 text-center d-none d-md-block z-2">
                    <img src="{{ asset('assets/img/illustrations/report.png') }}" class="img-fluid"
                        alt="Illustration for Reports" style="max-height: 150px; filter: drop-shadow(0 10px 10px rgba(0,0,0,0.2)); opacity: 0.95;">
                </div>
            </div>
        </div>
    </div>


    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i class="bx bxs-book text-primary fs-3 me-2"></i>
                <h5 class="fw-bold text-dark mb-0">Laporan Absensi Teori</h5>
            </div>
        </div>

        <!-- Kolom Absensi Mahasiswa Teori -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h6 class="mb-0 fw-bold text-primary"><i class="bx bxs-group me-2"></i>Rekap Kehadiran Mahasiswa</h6>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('dosen.absensi.generate-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="selectMatkulMahasiswa" class="form-label text-muted fw-semibold">Pilih Mata Kuliah Teori</label>
                            <select class="form-select select2" id="selectMatkulMahasiswa" name="jadwal_id" required>
                                <option value="" disabled selected>🔍 Cari & Pilih Mata Kuliah...</option>
                                @foreach ($jadwalList as $semester => $jadwalPerSemester)
                                    <optgroup label="Semester {{ $semester }}">
                                    @foreach ($jadwalPerSemester as $jadwal)
                                        <option value="{{ $jadwal['jadwal_id'] }}">
                                            {{ $jadwal['nama_matakuliah'] }} • {{ $jadwal['nama_prodi'] }} (Kelas {{ Str::title($jadwal['jenis_kelas']) }})
                                        </option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 rounded-pill">
                            <i class="bx bxs-file-pdf me-1"></i> Cetak Laporan Mahasiswa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Absensi Dosen Teori -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h6 class="mb-0 fw-bold text-info"><i class="bx bxs-user-badge me-2"></i>BAP Pengajaran Dosen</h6>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('dosen.generate-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="selectMatkulDosen" class="form-label text-muted fw-semibold">Pilih Mata Kuliah Teori</label>
                            <select class="form-select select2" id="selectMatkulDosen" name="jadwal_id" required>
                                <option value="" disabled selected>🔍 Cari & Pilih Mata Kuliah...</option>
                                @foreach ($jadwalList as $semester => $jadwalPerSemester)
                                    <optgroup label="Semester {{ $semester }}">
                                    @foreach ($jadwalPerSemester as $jadwal)
                                        <option value="{{ $jadwal['jadwal_id'] }}">
                                            {{ $jadwal['nama_matakuliah'] }} • {{ $jadwal['nama_prodi'] }} (Kelas {{ Str::title($jadwal['jenis_kelas']) }})
                                        </option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-info w-100 rounded-pill text-white shadow-sm">
                            <i class="bx bxs-file-pdf me-1"></i> Cetak Berita Acara (BAP)
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- PRAKTIK SECTION --}}
    <div class="row">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i class="bx bxs-flask text-success fs-3 me-2"></i>
                <h5 class="fw-bold text-dark mb-0">Laporan Absensi Praktikum</h5>
            </div>
        </div>

        <!-- Kolom Absensi Mahasiswa Praktik -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h6 class="mb-0 fw-bold text-success"><i class="bx bxs-group me-2"></i>Rekap Kehadiran Praktik Mahasiswa</h6>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('dosen.absensi-praktik.generate-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="selectMatkulMahasiswaPrak" class="form-label text-muted fw-semibold">Pilih Mata Kuliah Praktik</label>
                            <select class="form-select select2" id="selectMatkulMahasiswaPrak" name="jadwal_praktik_id" required>
                                <option value="" disabled selected>🔍 Cari & Pilih Mata Kuliah Praktik...</option>
                                @foreach ($jadwalListPraktik as $semester => $jadwalPerSemester)
                                    <optgroup label="Semester {{ $semester }}">
                                    @foreach ($jadwalPerSemester as $jadwal)
                                        <option value="{{ $jadwal['jadwal_praktik_id'] }}">
                                            {{ $jadwal['nama_matakuliah'] }} • {{ $jadwal['nama_prodi'] }} (Kelas {{ Str::title($jadwal['jenis_kelas']) }})
                                        </option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success w-100 rounded-pill shadow-sm">
                            <i class="bx bxs-file-pdf me-1"></i> Cetak Laporan Praktik Mahasiswa
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Kolom Absensi Dosen Praktik -->
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom pt-4 pb-3">
                    <h6 class="mb-0 fw-bold" style="color: #696cff;"><i class="bx bxs-user-badge me-2"></i>BAP Pengajaran Praktik Dosen</h6>
                </div>
                <div class="card-body pt-4">
                    <form action="{{ route('dosen.praktik.generate-pdf') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="selectMatkulDosenPrak" class="form-label text-muted fw-semibold">Pilih Mata Kuliah Praktik</label>
                            <select class="form-select select2" id="selectMatkulDosenPrak" name="jadwal_praktik_id" required>
                                <option value="" disabled selected>🔍 Cari & Pilih Mata Kuliah Praktik...</option>
                                @foreach ($jadwalListPraktik as $semester => $jadwalPerSemester)
                                    <optgroup label="Semester {{ $semester }}">
                                    @foreach ($jadwalPerSemester as $jadwal)
                                        <option value="{{ $jadwal['jadwal_praktik_id'] }}">
                                            {{ $jadwal['nama_matakuliah'] }} • {{ $jadwal['nama_prodi'] }} (Kelas {{ Str::title($jadwal['jenis_kelas']) }})
                                        </option>
                                    @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn w-100 rounded-pill text-white shadow-sm" style="background-color: #696cff; border-color: #696cff;">
                            <i class="bx bxs-file-pdf me-1"></i> Cetak BAP Praktik
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
