@extends('layouts.master')

@section('title', 'Arsip KRS Mahasiswa')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Akademik /</span> Arsip KRS</h4>
            <p class="text-muted mb-0">Riwayat KRS seluruh mahasiswa dari semester awal sampai akhir.</p>
        </div>
        <span class="badge bg-label-primary px-3 py-2">
            <i class="bx bx-archive me-1"></i>{{ number_format($stats['arsip']) }} arsip ditemukan
        </span>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-initial rounded bg-label-primary p-3"><i class="bx bx-folder fs-4"></i></span>
                    <div><small class="text-muted">Arsip sesuai filter</small><h4 class="mb-0">{{ number_format($stats['arsip']) }}</h4></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-initial rounded bg-label-success p-3"><i class="bx bx-user fs-4"></i></span>
                    <div><small class="text-muted">Mahasiswa dalam arsip</small><h4 class="mb-0">{{ number_format($stats['mahasiswa']) }}</h4></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="avatar-initial rounded bg-label-info p-3"><i class="bx bx-book fs-4"></i></span>
                    <div><small class="text-muted">SKS pada halaman ini</small><h4 class="mb-0">{{ number_format($stats['total_sks']) }}</h4></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0"><i class="bx bx-filter-alt me-2 text-primary"></i>Filter Arsip</h5>
        </div>
        <div class="card-body pt-4">
            <form method="GET" action="{{ route('admin.krs-archive.index') }}">
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Cari mahasiswa</label>
                        <input type="search" name="search" class="form-control" value="{{ $filters['search'] }}"
                            placeholder="Nama atau NIM">
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <label class="form-label">Program studi</label>
                        <select name="jurusan_id" class="form-select">
                            <option value="">Semua Prodi</option>
                            @foreach ($programStudiList as $prodi)
                                <option value="{{ $prodi->jurusan_id }}" @selected($filters['jurusan_id'] === (int) $prodi->jurusan_id)>
                                    {{ $prodi->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Angkatan</label>
                        <select name="angkatan" class="form-select">
                            <option value="">Semua Angkatan</option>
                            @foreach ($angkatanList as $angkatan)
                                <option value="{{ $angkatan }}" @selected($filters['angkatan'] === (int) $angkatan)>{{ $angkatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Tahun akademik</label>
                        <select name="ta_id" class="form-select">
                            <option value="">Semua Tahun</option>
                            @foreach ($tahunAkademikList as $ta)
                                <option value="{{ $ta->ta_id }}" @selected($filters['ta_id'] === (int) $ta->ta_id)>
                                    {{ $ta->nama }} {{ $ta->semester ? '('.$ta->semester.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <label class="form-label">Semester KRS</label>
                        <select name="semester" class="form-select">
                            <option value="">Semua Semester</option>
                            @for ($semester = 1; $semester <= 14; $semester++)
                                <option value="{{ $semester }}" @selected($filters['semester'] === $semester)>
                                    Semester {{ $semester }}
                                </option>
                            @endfor
                        </select>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search me-1"></i>Tampilkan
                    </button>
                    <a href="{{ route('admin.krs-archive.index') }}" class="btn btn-outline-secondary">
                        <i class="bx bx-reset me-1"></i>Reset
                    </a>
                    @can('krs-archive-export')
                        <button type="submit" class="btn btn-success ms-sm-auto"
                            formaction="{{ route('admin.krs-archive.download-semester') }}"
                            @disabled($filters['semester'] === null)
                            title="{{ $filters['semester'] === null ? 'Pilih Semester KRS terlebih dahulu' : 'Unduh semua arsip sesuai filter' }}">
                            <i class="bx bx-package me-1"></i>Unduh Semester dalam ZIP
                        </button>
                    @endcan
                </div>
                @if ($filters['semester'] === null && auth()->user()->can('krs-archive-export'))
                    <small class="d-block text-muted mt-2 text-sm-end">Pilih Semester KRS untuk mengaktifkan unduhan ZIP.</small>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Mahasiswa</th>
                        <th>Prodi / Kelas</th>
                        <th class="text-center">Angkatan</th>
                        <th>Tahun Akademik</th>
                        <th class="text-center">Semester</th>
                        <th class="text-center">Isi KRS</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Unduh</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($archives as $archive)
                        <tr>
                            <td>
                                <div class="fw-semibold text-dark">{{ $archive->nama }}</div>
                                <small class="text-muted"><i class="bx bx-id-card me-1"></i>{{ $archive->nim }}</small>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $archive->prodi_nama ?? '-' }}</div>
                                <small class="text-muted">{{ jenis_kelas_label($archive->kelas) }}</small>
                            </td>
                            <td class="text-center">{{ $archive->tahun_masuk ?: '-' }}</td>
                            <td>
                                <div class="fw-medium">{{ $archive->tahun_akademik }}</div>
                                <small class="text-muted">{{ $archive->periode_akademik ?: '-' }}</small>
                            </td>
                            <td class="text-center"><span class="badge bg-label-primary">Semester {{ $archive->semester_krs }}</span></td>
                            <td class="text-center">
                                <div class="fw-semibold">{{ $archive->total_mk }} MK</div>
                                <small class="text-muted">{{ $archive->total_sks }} SKS</small>
                            </td>
                            <td class="text-center">
                                @if ((int) $archive->menunggu_acc === 0)
                                    <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Disetujui</span>
                                @else
                                    <span class="badge bg-label-warning"><i class="bx bx-time me-1"></i>Belum ACC</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('krs-archive-export')
                                    <a class="btn btn-sm btn-outline-danger"
                                        href="{{ route('admin.krs-archive.download', [$archive->mahasiswa_id, $archive->ta_id, $archive->semester_krs]) }}">
                                        <i class="bx bxs-file-pdf me-1"></i>PDF
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-folder-open display-5 text-muted"></i>
                                <h6 class="mt-2 mb-1">Arsip KRS tidak ditemukan</h6>
                                <p class="text-muted mb-0">Ubah filter atau pastikan mahasiswa sudah mengambil KRS.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($archives->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $archives->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection
