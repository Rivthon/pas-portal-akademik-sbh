@extends('layouts.master')
@section('title', 'Penilaian Dosen')
@section('content')
<!-- Page Title and Description -->
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
                    Melakukan penilaian terhadap dosen yang mengajar pada mata kuliah tertentu. Pastikan Anda telah
                    memilih tahun ajaran, program studi, mata kuliah, dan dosen yang bersangkutan.
                </p>
                <div class="d-flex gap-2">
                    <form action="{{ route('admin.reset.edom') }}" method="POST" id="resetEdomForm">
                        @csrf
                        <button type="button" class="btn btn-info" onclick="confirmResetEdom()">Pengaturan Ulang
                            EDOM</button>
                    </form>
                    <form action="{{ route('admin.setup.edom') }}" method="POST" id="setupEdomForm">
                        @csrf
                        <button type="button" class="btn btn-success" onclick="confirmSetupEdom()">Pengaturan Pulihkan
                            EDOM</button>
                    </form>
                </div>
            </div>
        </div>
        <!-- Image Section -->
        <div class=" col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card shadow-sm mb-4">
    <form method="POST" action="{{ route('admin.penilaian.filter') }}">
        @csrf
        <div class="card shadow-sm">
            <div class="card-header bg-light">
                <h5 class="mb-0">Filter Penilaian</h5>
            </div>
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <!-- Tahun Ajaran -->
                    <div class="col-md-4">
                        <label for="ta_id" class="form-label">Tahun Ajaran</label>
                        <select name="ta_id" id="ta_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Pilih Tahun Ajaran --</option>
                            @foreach ($tahunAjaran as $ta)
                            <option value="{{ $ta->ta_id }}" {{ (isset($ta_id) && $ta_id==$ta->ta_id) ? 'selected' : ''
                                }}>
                                {{ $ta->nama }} - {{ $ta->semester }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Program Studi -->
                    @if (isset($programStudi))
                    <div class="col-md-4">
                        <label for="jurusan_id" class="form-label">Program Studi</label>
                        <select name="jurusan_id" id="jurusan_id" class="form-select select2">
                            <option value="">-- Pilih Program Studi --</option>
                            @foreach ($programStudi as $ps)
                            <option value="{{ $ps->jurusan_id }}" {{ (isset($jurusan_id) && $jurusan_id==$ps->
                                jurusan_id) ?
                                'selected' : '' }}>
                                {{ $ps->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Mata Kuliah -->
                    @if (isset($kurikulum))
                    <div class="col-md-4">
                        <label for="kurikulum_id" class="form-label">Mata Kuliah</label>
                        <select name="kurikulum_id" id="kurikulum_id" class="form-select select2">
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach ($kurikulum as $k)
                            <option value="{{ $k->kurikulum_id }}" {{ (isset($kurikulum_id) && $kurikulum_id==$k->
                                kurikulum_id) ? 'selected' : '' }}>
                                {{ $k->mataKuliah->nama }} - {{ $k->programStudi->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Dosen -->
                    @if (isset($dosen))
                    <div class="col-md-6">
                        <label for="dosen_id" class="form-label">Dosen</label>
                        <select name="dosen_id" id="dosen_id" class="form-select select2">
                            <option value="">-- Pilih Dosen --</option>
                            @foreach ($dosen as $d)
                            <option value="{{ $d->dosen_id }}" {{ (isset($dosen_id) && $dosen_id==$d->dosen_id) ?
                                'selected'
                                : '' }}>
                                {{ $d->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <!-- Jenis Dosen -->
                    <div class="col-md-6">
                        <label for="jenis_dosen" class="form-label">Jenis Dosen</label>
                        <select name="jenis_dosen" id="jenis_dosen" class="form-select">
                            <option value="">-- Pilih Jenis Dosen --</option>
                            <option value="teori" {{ (isset($jenis_dosen) && $jenis_dosen=='teori' ) ? 'selected' : ''
                                }}>
                                Teori</option>
                            <option value="praktik" {{ (isset($jenis_dosen) && $jenis_dosen=='praktik' ) ? 'selected'
                                : '' }}>Praktik</option>
                        </select>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </div>
        </div>
    </form>
    <!-- Display Data Table -->
    @if (isset($penilaian) && $ta_id && $jurusan_id && $kurikulum_id && $dosen_id)
    <div class="card mt-3">
        <div class="card-header">
            <h6 class="mb-0">Hasil Penilaian</h6>
        </div>
        <div class="card-body mt-2">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th>No</th>
                            <th>Aspek Penilaian</th>
                            <th>Rata-rata Nilai</th>
                            <th>Kriteria</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rataRataNilai as $evaluasiId => $nilai)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $penilaian->firstWhere('evaluasi_id', $evaluasiId)->evaluasi->nama }}</td>
                            <td>{{ number_format($nilai, 2) }}</td>
                            <td>{{ $kriteria($nilai) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @if ($sarans->isNotEmpty())
    <div class="card shadow-sm mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card-header ">
                    <p class="mb-0">List Saran</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Saran</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sarans as $key => $saran)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $saran->saran }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card-header ">
                    <p class="mb-0">List Nama Mahasiswa Yang Sudah mengisi EDOM</p>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-primary">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Mahasiswa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sarans as $key => $saran)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $saran->mahasiswa->nama ?? '-' }}</td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>


    </div>
    @endif
    @endif

</div>

@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Terapkan Select2 untuk dropdown
        $('.select2').select2({
            theme: 'bootstrap-5',
            placeholder: "Pilih salah satu",
            allowClear: true
        });

        // Auto-submit hanya untuk Tahun Ajaran
        document.getElementById("ta_id").addEventListener("change", function () {
            this.form.submit();
        });
    });

</script>
@endpush