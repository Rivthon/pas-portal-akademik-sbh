@extends('layouts.dosen')
@section('title', 'Input Nilai Dosen')
@section('content')
<div class="card p-4 shadow-sm mb-4 border-0" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
    <div class="row g-4 align-items-center">
        <!-- Content Section -->
        <div class="col-md-7 text-white">
            <div class="card-body">
                <h4 class="card-title text-white fw-bold mb-3"><i class="bx bx-edit-alt me-2"></i>Input Nilai Mahasiswa</h4>
                <p class="text-white-50" style="line-height: 1.6;">
                    Klik pada kartu mata kuliah yang Anda ajar di semester ini untuk mulai memasukkan nilai mahasiswa. Anda juga dapat menggunakan opsi <strong>Pencarian Arsip</strong> di bawah jika perlu mengisi nilai semester lalu.
                </p>
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center d-none d-md-block">
            <img src="{{ asset('assets/img/illustrations/nilai.png') }}" class="img-fluid" alt="Illustration"
                style="max-height: 150px;">
        </div>
    </div>
</div>

<!-- KELAS AKTIF (DASHBOARD KARTU) -->
<h5 class="fw-bold mb-3 text-dark"><i class="bx bx-book-open me-2 text-primary"></i>Kelas Anda Saat Ini (TA: {{ $activeTA ? $activeTA->nama : 'Belum Ditentukan' }})</h5>

@if($mataKuliahAktif->isEmpty())
<div class="alert alert-info text-center py-4 mb-4 border-0 shadow-sm">
    <i class="bx bx-info-circle fs-2 mb-2"></i><br>
    Belum ada mata kuliah yang terjadwal untuk Anda pada Tahun Akademik ini.
</div>
@else
<div class="row mb-4">
    @foreach($mataKuliahAktif as $mk)
    @php
        $mataKuliah = $mk->kurikulum?->mataKuliah;
        $programStudiAktif = $mk->kurikulum?->programStudi;
    @endphp
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow-sm h-100 border-0 hover-scale mk-card" style="cursor:pointer; transition: all 0.2s ease-in-out;"
            data-jadwal-id="{{ $mk->id }}"
            data-mk-nama="{{ $mataKuliah?->nama }} - {{ $programStudiAktif?->nama }} ({{ jenis_kelas_label($mk->jenis_kelas) }})">
            <div class="card-body d-flex flex-column">
                <h6 class="fw-bold text-primary mb-2 border-start border-primary border-3 ps-2">{{ $mataKuliah?->nama }}</h6>
                <small class="text-muted mb-2"><i class="bx bx-buildings me-1"></i>{{ $programStudiAktif?->nama ?? '-' }}</small>
                <div class="mb-3 mt-1">
                    <span class="badge bg-label-primary rounded-pill">{{ $mataKuliah?->matakuliah_id }}</span>
                    <span class="badge bg-label-info rounded-pill ms-1">SMT {{ $mataKuliah?->smt ?? $mataKuliah?->semester }}</span>
                    <span class="badge bg-label-{{ strtolower((string) $mk->jenis_kelas) === 'karyawan' ? 'warning' : 'success' }} rounded-pill ms-1">
                        {{ jenis_kelas_label($mk->jenis_kelas) }}
                    </span>
                </div>
                <div class="mt-auto text-end">
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold btn-pilih"><i class="bx bx-edit-alt me-1"></i>Input Nilai</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<!-- ARSIP & FILTER MANUAL (ACCORDION) -->
<!-- <div class="accordion mb-4 shadow-sm" id="accordionArsip">
  <div class="accordion-item border-0">
    <h2 class="accordion-header" id="headingArsip">
      <button class="accordion-button collapsed fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#collapseArsip" aria-expanded="false" aria-controls="collapseArsip" style="background-color: #f8f9fa;">
        <i class="bx bx-archive text-warning me-2"></i> Pencarian Arsip / Filter Klasik
      </button>
    </h2>
    <div id="collapseArsip" class="accordion-collapse collapse" aria-labelledby="headingArsip" data-bs-parent="#accordionArsip">
      <div class="accordion-body bg-white pt-4">
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
      </div>
    </div>
  </div>
</div> -->

<!-- TABEL PENGISIAN NILAI -->
<div class="card p-4 shadow-sm" id="panel-penilaian" style="display: none;">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h5 class="fw-bold text-primary mb-2 mb-md-0"><i class="bx bx-list-check me-2"></i>Daftar Mahasiswa: <span id="label-mk-terpilih" class="text-dark"></span></h5>
        <button id="btnTutupPanel" type="button" class="btn btn-sm btn-outline-danger shadow-sm rounded-pill px-3"><i class="bx bx-x"></i> Tutup Panel</button>
    </div>
    <div class="mb-3" id="bobot-panel-container"></div>

    <form id="form-nilai" method="POST" action="{{ route('dosen.nilai.save') }}">
        @csrf
        <input type="hidden" name="jadwal_id" id="nilai-jadwal-id">
        <div class="table-responsive text-nowrap">
            <table id="table-mahasiswa" class="table table-bordered table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2" style="width: 50px; text-align: center; vertical-align: middle;">#</th>
                        <th rowspan="2" style="text-align: center; vertical-align: middle; min-width: 250px;">Nama Mahasiswa</th>
                        <th colspan="7" style="text-align: center;">Komponen Nilai</th>
                    </tr>
                    <tr>
                        <th style="width: 100px; text-align: center;">UTS</th>
                        <th style="width: 100px; text-align: center;">UAS</th>
                        <th style="width: 100px; text-align: center;">TUGAS</th>
                        <th style="width: 100px; text-align: center;">ABSEN</th>
                        <th style="width: 100px; text-align: center;">PRAKTIK</th>
                        <th style="width: 120px; text-align: center;">Nilai Akhir</th>
                        <th style="width: 100px; text-align: center;">Huruf</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            Silakan pilih kelas terlebih dahulu.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-4 py-2 text-uppercase fw-bold" id="save-nilai"><i class="bx bx-save me-2"></i>Simpan Semua Nilai</button>
        </div>
    </form>
</div>
<style>
    .hover-scale:hover {
        transform: translateY(-5px) !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    .mk-card.selected-card {
        border: 2px solid #696cff !important;
        box-shadow: 0 0.5rem 1.5rem rgba(105, 108, 255, 0.3) !important;
        background-color: #f8f9fa !important;
    }
    .mk-card.selected-card .btn-pilih {
        background-color: #696cff;
        color: white;
    }
</style>
@endsection
