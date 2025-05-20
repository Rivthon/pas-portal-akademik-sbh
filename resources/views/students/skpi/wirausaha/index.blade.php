@extends('layouts.mahasiswa')
@section('title', 'Program Wirausaha Mahasiswa')

@section('content')
<div class="container">
    <!-- Card Section -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="row g-0 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="text-primary fw-bold mb-3">Program Pembinaan Mahasiswa Wirausaha (PWMV/P2MW) </h5>
                    <!-- Breadcrumbs -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb small">
                            <li class="breadcrumb-item">
                                <a href="{{ route('mahasiswa.dashboard') }}" class="breadcrumb-link">
                                    <i class="bx bx-home"></i>
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('mahasiswa.skpi.index') }}" class="breadcrumb-link">
                                    Aktivitas & Prestasi
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                <span class="breadcrumb-active"
                                    style="color: #0d6efd; text-decoration: underline; cursor: pointer;">
                                    Program Pembinaan Mahasiswa
                                    Wirausaha (PWMV/P2MW)
                                </span>
                            </li>

                        </ol>
                    </nav>
                    <style>
                        .breadcrumb-link {
                            transition: color 0.2s;
                        }

                        .breadcrumb-link:hover {
                            color: #0d6efd !important;
                            text-decoration: underline;
                        }
                    </style>
                    <!-- Mahasiswa Info -->
                    <div class="mb-3">
                        <strong class="fs-5 d-block">{{ $mahasiswa->nama }}</strong>
                        <span class="text-secondary small">
                            {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun Ajaran: {{
                            $ta->nama }}
                        </span>
                    </div>

                    <!-- Nilai Bobot -->
                    <div class="d-flex align-items-center mb-4">
                        <span class="fw-semibold me-2 text-dark" style="font-size: 1.1rem;">Total Bobot Nilai:</span>
                        <span class="badge bg-label-primary text-primary fw-bold"
                            style="font-size: 1.25rem; padding: 0.6em 1.2em;">
                            {{ $wirausaha->sum('bobot') ?? 0 }}
                        </span>
                        15
                    </div>

                    <!-- CTA -->
                    <a href="#" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal"
                        data-bs-target="#ajukanSertifikasiModal">
                        <i class="bx bx-send"></i> Ajukan Program Wirausaha Mahasiswa
                    </a>
                </div>

            </div>

            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-4">
                    <svg width="100%" height="180" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M48 16a18 18 0 0118 18c0 6.9-3.9 12.9-9.6 16H39.6C33.9 46.9 30 40.9 30 34a18 18 0 0118-18z"
                            stroke="#FF6B6B" stroke-width="4" fill="white" />
                        <rect x="40" y="37" width="4" height="9" fill="#FF6B6B" />
                        <rect x="48" y="33" width="4" height="13" fill="#FFD93D" />
                        <rect x="56" y="29" width="4" height="17" fill="#FF6B6B" />
                        <rect x="40" y="58" width="16" height="6" rx="2" fill="#FF6B6B" />
                        <rect x="38" y="64" width="20" height="6" rx="3" fill="#FF6B6B" />
                    </svg>
                </div>
            </div>

        </div>

    </div>
    <!-- Modal Trigger Button -->
    {{-- <div class="mb-3">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajukanSertifikasiModal">
            Ajukan Sertifikasi / Kompetensi
        </button>
    </div> --}}
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Berhasil!</strong> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Gagal!</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    {{-- Error validation khusus untuk modal --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var modal = new bootstrap.Modal(document.getElementById('ajukanSertifikasiModal'));
            modal.show();
        });
    </script>
    @endif
    <!-- Modal -->
    <div class="modal fade" id="ajukanSertifikasiModal" tabindex="-1" aria-labelledby="ajukanSertifikasiModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-uppercase" id="ajukanSertifikasiModalLabel">Ajukan Program
                        Wirausaha Mahasiswa</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route ('mahasiswa.wirausaha.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-3">
                            {{-- Kolom 1 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nama Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="nama_usaha" class="form-control"
                                    value="{{ old('nama_usaha') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Jenis Usaha <span class="text-danger">*</span></label>
                                <input type="text" name="jenis_usaha" class="form-control"
                                    value="{{ old('jenis_usaha') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                                <input type="text" name="penyelenggara" class="form-control"
                                    value="{{ old('penyelenggara') }}" required>
                            </div>
                            {{-- Kolom 2 --}}
                            <div class="col-12 col-md-6">
                                <label class="form-label">Status Pendanaan <span class="text-danger">*</span></label>
                                <select name="status_pendanaan" class="form-select" required>
                                    <option value="" selected disabled>-- pilih --</option>
                                    @foreach(['Didanai', 'Tidak Didanai'] as $status)
                                    <option value="{{ $status }}" @selected(old('status_pendanaan')==$status)>{{
                                        $status
                                        }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}"
                                    required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">File Lampiran (Google Drive) <span
                                        class="text-danger">*</span></label>
                                <input type="url" name="file_lampiran" class="form-control"
                                    placeholder="https://drive.google.com/..." value="{{ old('file_lampiran') }}"
                                    required>
                                <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki
                                    link.</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">File Sertifikat (Google Drive) <span
                                        class="text-danger">*</span></label>
                                <input type="url" name="file_sertifikat" class="form-control"
                                    placeholder="https://drive.google.com/..." value="{{ old('file_sertifikat') }}"
                                    required>
                                <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki
                                    link.</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-primary" type="submit">Kirim Pengajuan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card mx-auto mt-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="mb-0">Daftar Program Wirausaha Mahasiswa</p>

                {{-- Filter Status (optional) --}}
                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach(['Menunggu','Disetujui','Ditolak','Ditinjau'] as $s)
                        <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-secondary" type="submit">
                        <i class="bx bx-filter-alt"></i>
                    </button>
                </form>
            </div>

            <table class="table table-hover table-bordered align-middle">
                <thead class="table-primary">
                    <tr>
                        <th style="width: 60px">No</th>
                        <th>Nama Usaha</th>
                        <th>Jenis Usaha</th>
                        <th>Penyelenggara</th>
                        <th>Status Pendanaan</th>
                        <th>Tanggal</th>
                        <th style="width: 130px">Status</th>
                        <th style="width: 90px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($wirausaha as $i => $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nama_usaha }}</td>
                        <td>{{ $row->jenis_usaha }}</td>
                        <td>{{ $row->penyelenggara }}</td>
                        <td>{{ $row->status_pendanaan }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                        </td>
                        <td>
                            @php
                            $badge = match ($row->status_validasi) {
                            'Disetujui' => 'success',
                            'Ditolak' => 'danger',
                            'Ditinjau' => 'info',
                            default => 'warning'
                            };
                            @endphp
                            <span class="badge bg-{{ $badge }}">
                                {{ $row->status_validasi }}
                            </span>
                        </td>
                        <td class="text-center">
                            <!-- Detail Button triggers modal -->
                            <button type="button" class="btn btn-sm btn-icon btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#detailWirausahaModal{{ $row->id }}" title="Detail">
                                <i class="bx bx-search-alt-2"></i>
                            </button>
                            <!-- Delete Button -->
                            <form action="{{ route('mahasiswa.delete.wirausaha', $row->id) }}" method="POST"
                                class="d-inline delete-wirausaha-form">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                    class="btn btn-sm btn-icon btn-outline-danger btn-delete-wirausaha" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailWirausahaModal{{ $row->id }}" tabindex="-1"
                                aria-labelledby="detailWirausahaModalLabel{{ $row->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"
                                                id="detailWirausahaModalLabel{{ $row->id }}">
                                                Detail Program Wirausaha Mahasiswa
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row mb-0 text-start">
                                                <dt class="col-sm-4">Nama Usaha</dt>
                                                <dd class="col-sm-8">{{ $row->nama_usaha }}</dd>

                                                <dt class="col-sm-4">Jenis Usaha</dt>
                                                <dd class="col-sm-8">{{ $row->jenis_usaha }}</dd>

                                                <dt class="col-sm-4">Penyelenggara</dt>
                                                <dd class="col-sm-8">{{ $row->penyelenggara }}</dd>

                                                <dt class="col-sm-4">Status Pendanaan</dt>
                                                <dd class="col-sm-8">{{ $row->status_pendanaan }}</dd>

                                                <dt class="col-sm-4">Tanggal</dt>
                                                <dd class="col-sm-8">
                                                    {{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}
                                                </dd>

                                                <dt class="col-sm-4">File Lampiran</dt>
                                                <dd class="col-sm-8">
                                                    @if ($row->file_lampiran)
                                                    <a href="{{ $row->file_lampiran }}" target="_blank" rel="noopener">
                                                        Lihat Lampiran
                                                    </a>
                                                    @else
                                                    -
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">File Sertifikat</dt>
                                                <dd class="col-sm-8">
                                                    @if ($row->file_sertifikat)
                                                    <a href="{{ $row->file_sertifikat }}" target="_blank"
                                                        rel="noopener">
                                                        Lihat Sertifikat
                                                    </a>
                                                    @else
                                                    -
                                                    @endif
                                                </dd>



                                                <dt class="col-sm-4">Bobot</dt>
                                                <dd class="col-sm-8">: {{ $row->bobot ?? '-' }}</dd>

                                                <dt class="col-sm-4">Status</dt>
                                                <dd class="col-sm-8">
                                                    : <span class="badge bg-{{ $badge }}">{{ $row->status_validasi
                                                        }}</span>
                                                    @if ($row->status_validasi === 'Ditinjau')
                                                    <a href="{{ route('mahasiswa.edit.wirausaha', $row->id) }}"
                                                        class="badge bg-warning text-dark ms-2">
                                                        Ajukan Ulang
                                                    </a>
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">Catatan Validator</dt>
                                                <dd class="col-sm-8">{{ $row->catatan_validator ?? '-' }}</dd>
                                            </dl>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">Belum ada pengajuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $wirausaha->withQueryString()->links() }}
            </div>
        </div>
    </div>
    @endsection