@extends('layouts.mahasiswa')
@section('title', 'Penguasaan Bahasa Asing')

@section('content')
<div class="container">
    <!-- Card Section -->
    <div class="card shadow-sm mb-4">
        <div class="d-flex align-items-center item g-0">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="card-title text-primary mb-3 fw-bold">
                        Penguasaan Bahasa Asing
                    </h5>
                    <p class="text-muted mb-4" style="line-height: 1.6;">
                        {{-- Bobot Nilai SKPI: 0.5 --}}
                        <hr>
                        <strong>{{ $mahasiswa->nama }} <br>
                            {{ $mahasiswa->programStudi->nama }} Semester {{ $mahasiswa->semester }} - Tahun Ajaran: {{
                            $ta->nama }} <br> Bobot Nilai <span
                                class="badge bg-warning text-dark ms-2">0.5</span></strong>
                        <hr>


                    </p>
                    <!-- CTA Button -->
                    <div class="mb-3">
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#ajukanSertifikasiModal">Ajukan Sertifikasi Penguasaan Bahasa</a>
                    </div>
                </div>
            </div>
            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-3">
                    <img src="{{ asset('assets/img/illustrations/kartu-study.png') }}" class="img-fluid"
                        alt="Illustration of a schedule" style="max-height: 200px;">
                </div>
            </div>
        </div>
    </div>
</div>
<div class="container">
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
                    <h5 class="modal-title fw-bold text-uppercase" id="ajukanSertifikasiModalLabel">Ajukan Sertifikasi
                        Penguasaan Bahasa Asing</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route ('mahasiswa.bahasa.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-body">
                        <div class="row gy-3">
                            {{-- Kolom 1 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Bahasa <span class="text-danger">*</span></label>
                                <input type="text" name="nama_bahasa" class="form-control"
                                    value="{{ old('nama_bahasa') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Level <span class="text-danger">*</span></label>
                                <select name="level" class="form-select" required>
                                    <option value="" selected disabled>-- pilih --</option>
                                    @foreach(['Beginner', 'Elementary', 'Intermediate', 'Upper Intermediate',
                                    'Advanced'] as $level)
                                    <option value="{{ $level }}" @selected(old('level')==$level)>{{ $level }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                                <input type="text" name="penyelenggara" class="form-control"
                                    value="{{ old('penyelenggara') }}" required>
                            </div>
                            {{-- Kolom 2 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Skor <span class="text-danger">*</span></label>
                                <input type="number" name="skor" class="form-control" value="{{ old('skor') }}"
                                    required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_tes" class="form-control"
                                    value="{{ old('tanggal_tes') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Link Sertifikat (Google Drive) <span
                                        class="text-danger">*</span></label>
                                <input type="url" name="file_sertifikat" class="form-control"
                                    placeholder="https://drive.google.com/..." value="{{ old('file_sertifikat') }}"
                                    required>
                                <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                    (Anyone with the link can
                                    view).</small>
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
                <p class="mb-0">Daftar Pengajuan Bahasa Asing</p>

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
                        <th>Nama Bahasa</th>
                        <th>Penyelenggara</th>
                        <th style="width: 120px">Level</th>
                        <th style="width: 180px">Skor / Nilai</th>
                        <th style="width: 130px">Status</th>
                        <th style="width: 90px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($bahasa as $i => $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $row->nama_bahasa }}</td>
                        <td>{{ $row->penyelenggara }}</td>
                        <td>{{ $row->level }}</td>
                        <td>
                            {{ \Carbon\Carbon::parse($row->tanggal_tes)->format('d‑m‑Y') }}
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
                                data-bs-target="#detailSertifikasiModal{{ $row->id }}" title="Detail">
                                <i class="bx bx-search-alt-2"></i>
                            </button>
                            <!-- Edit Button -->
                            <!-- Delete Button -->
                            <form action="{{ route('mahasiswa.delete.bahasa', $row->id) }}" method="POST"
                                class="d-inline delete-bahasa-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-bahasa"
                                    title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>

                            <!-- Detail Modal -->
                            <div class="modal fade" id="detailSertifikasiModal{{ $row->id }}" tabindex="-1"
                                aria-labelledby="detailSertifikasiModalLabel{{ $row->id }}" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title fw-bold"
                                                id="detailSertifikasiModalLabel{{ $row->id }}">
                                                Detail Penguasaan Bahasa Asing
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row mb-0 text-start">
                                                <dt class="col-sm-4">Nama Bahasa</dt>
                                                <dd class="col-sm-8">{{ $row->nama_bahasa }}</dd>

                                                <dt class="col-sm-4">Penyelenggara</dt>
                                                <dd class="col-sm-8">{{ $row->penyelenggara }}</dd>

                                                <dt class="col-sm-4">Level</dt>
                                                <dd class="col-sm-8">{{ $row->level }}</dd>

                                                <dt class="col-sm-4">Skor / Nilai</dt>
                                                <dd class="col-sm-8">{{ $row->skor }}</dd>

                                                <dt class="col-sm-4">Tanggal</dt>
                                                <dd class="col-sm-8">
                                                    {{ \Carbon\Carbon::parse($row->tanggal_tes)->format('d‑m‑Y') }}
                                                </dd>

                                                <dt class="col-sm-4">Status Validasi</dt>
                                                <dd class="col-sm-8">
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
                                                </dd>
                                                <dt class="col-sm-4">Link Sertifikat</dt>
                                                <dd class="col-sm-8">
                                                    :
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
                                                    <a href="{{ route('mahasiswa.edit.bahasa', $row->id) }}"
                                                        class="badge bg-warning text-dark ms-2">
                                                        Ajukan Ulang
                                                    </a>
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">Catatan Validator</dt>
                                                <dd class="col-sm-8">: {{ $row->catatan_validator ?? '-' }}</dd>
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
                        <td colspan="7" class="text-center text-muted">Belum ada pengajuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $bahasa->withQueryString()->links() }}
            </div>
        </div>
    </div>
    @endsection