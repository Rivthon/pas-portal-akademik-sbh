@extends('layouts.mahasiswa')
@section('title', 'Kegiatan Tambahan')

@section('content')
<div class="container">
    <!-- Card Section -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="row g-0 align-items-center">
            <!-- Content Section -->
            <div class="col-md-7">
                <div class="card-body">
                    <!-- Title -->
                    <h5 class="text-primary fw-bold mb-3">Kegiatan Tambahan</h5>
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
                                    Kegiatan Tambahan
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
                            {{ $tambahan->sum('bobot') ?? 0 }}
                        </span>
                        {{-- <small class="text-muted ms-2">(maksimal 30)</small> --}}
                    </div>

                    <!-- CTA -->
                    <a href="#" class="btn btn-primary d-inline-flex align-items-center gap-1" data-bs-toggle="modal"
                        data-bs-target="#ajukanSertifikasiModal">
                        <i class="bx bx-send"></i> Ajukan Kegiatan Tambahan
                    </a>
                </div>

            </div>

            <!-- Image Section -->
            <div class="col-md-5 text-center">
                <div class="p-4">
                    <svg width="100%" height="180" viewBox="0 0 96 96" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="44" y="24" width="8" height="48" rx="2" fill="#6366F1" />
                        <rect x="24" y="44" width="48" height="8" rx="2" fill="#6366F1" />
                        <circle cx="48" cy="16" r="4" fill="#10B981" />
                        <circle cx="48" cy="80" r="4" fill="#10B981" />
                        <circle cx="16" cy="48" r="4" fill="#10B981" />
                        <circle cx="80" cy="48" r="4" fill="#10B981" />
                        <circle cx="24" cy="24" r="4" fill="#10B981" />
                        <circle cx="72" cy="24" r="4" fill="#10B981" />
                        <circle cx="24" cy="72" r="4" fill="#10B981" />
                        <circle cx="72" cy="72" r="4" fill="#10B981" />
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
    {{-- Tampilkan error/success di luar modal --}}
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
                    <h5 class="modal-title fw-bold text-uppercase" id="ajukanSertifikasiModalLabel">Ajukan Kegiatan
                        Tambahan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route ('mahasiswa.tambahan.store') }}" method="POST" novalidate>
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                        <div class="row gy-3">
                            {{-- Kolom 1 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-select" required>
                                    <option value="" selected disabled>-- pilih kategori --</option>
                                    <option value="Akademik" @selected(old('kategori')=='Akademik' )>Akademik</option>
                                    <option value="Non Akademik" @selected(old('kategori')=='Non Akademik' )>Non
                                        Akademik</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Nama Kegiatan <span class="text-danger">*</span></label>
                                <input type="text" name="nama_kegiatan" class="form-control"
                                    value="{{ old('nama_kegiatan') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Bentuk Kegiatan <span class="text-danger">*</span></label>
                                <input list="bentuk-kegiatan-list" name="bentuk_kegiatan" class="form-control"
                                    value="{{ old('bentuk_kegiatan') }}" required
                                    placeholder="Pilih atau ketik bentuk kegiatan">
                                <datalist id="bentuk-kegiatan-list">
                                    <option value="Seminar">
                                    <option value="Workshop">
                                    <option value="Pelatihan">
                                    <option value="Lomba Akademik">
                                    <option value="Lomba Non-Akademik">
                                    <option value="Magang">
                                    <option value="Kunjungan Industri">
                                    <option value="Kuliah Umum">
                                    <option value="Webinar">
                                    <option value="Simposium">
                                    <option value="Diskusi Ilmiah">
                                    <option value="Pengabdian Masyarakat">
                                    <option value="Studi Banding">
                                    <option value="Organisasi Mahasiswa">
                                    <option value="Kepanitiaan">
                                    <option value="LKMM">
                                    <option value="Lainnya">
                                </datalist>
                            </div>
                            {{-- Kolom 2 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                                <select name="tingkat" class="form-select" required>
                                    <option value="" selected disabled>-- pilih --</option>
                                    @foreach(['Lokal','Regional','Nasional','Internasional'] as $t)
                                    <option value="{{ $t }}" @selected(old('tingkat')==$t)>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Penyelenggara <span class="text-danger">*</span></label>
                                <input type="text" name="penyelenggara" class="form-control"
                                    value="{{ old('penyelenggara') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">Peran <span class="text-danger">*</span></label>
                                <select name="peran" class="form-select" required>
                                    <option value="" selected disabled>-- pilih --</option>
                                    @foreach(['Ketua', 'Wakil Ketua', 'Sekretaris', 'Bendahara', 'Anggota', 'Peserta',
                                    'Panitia', 'Pembicara', 'Moderator', 'Lainnya'] as $peran)
                                    <option value="{{ $peran }}" @selected(old('peran')==$peran)>{{ $peran }}</option>
                                    @endforeach
                                </select>
                            </div>
                            {{-- Kolom 3 --}}
                            <div class="col-12 col-md-4">
                                <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal" class="form-control" value="{{ old('tanggal') }}"
                                    required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">File Sertifikat (Google Drive) <span
                                        class="text-danger">*</span></label>
                                <input type="url" name="file_sertifikat" class="form-control"
                                    placeholder="https://drive.google.com/..." value="{{ old('file_sertifikat') }}"
                                    required>
                                <small class="text-muted">Pastikan link dapat diakses oleh siapa saja yang memiliki link
                                    (Anyone with the link can view).</small>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label">File Lampiran (Google Drive)</label>
                                <input type="url" name="file_lampiran" class="form-control"
                                    placeholder="https://drive.google.com/..." value="{{ old('file_lampiran') }}">
                                <small class="text-muted">Opsional. Pastikan link dapat diakses oleh siapa saja yang
                                    memiliki link.</small>
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
                <p class="mb-0">Daftar Pengajuan Kegiatan Tambahan</p>

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
                        <th>Nama Kegiatan</th>
                        <th>Bentuk Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Sertifikat</th>
                        <th>Lampiran</th>
                        <th style="width: 130px">Status</th>
                        <th style="width: 90px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tambahan as $i => $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>{{ $row->nama_kegiatan }}</td>
                        <td>{{ $row->bentuk_kegiatan }}</td>

                        <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</td>
                        <td>
                            @if ($row->file_sertifikat)
                            <a href="{{ $row->file_sertifikat }}" target="_blank" rel="noopener"
                                class="badge bg-success text-decoration-none">
                                <i class="bx bx-link-external"></i> Lihat
                            </a>
                            @else
                            <span class="badge bg-secondary">-</span>
                            @endif
                        </td>
                        <td>
                            @if ($row->file_lampiran)
                            <a href="{{ $row->file_lampiran }}" target="_blank" rel="noopener"
                                class="badge bg-info text-decoration-none">
                                <i class="bx bx-link-external"></i> Lihat
                            </a>
                            @else
                            <span class="badge bg-secondary">-</span>
                            @endif
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
                            <!-- Delete Button -->
                            <form action="{{ route('mahasiswa.delete.tambahan', $row->id) }}" method="POST"
                                class="d-inline delete-sertifikasi-form">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-tambahan"
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
                                                Detail Kegiatan Tambahan
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Tutup"></button>
                                        </div>
                                        <div class="modal-body">
                                            <dl class="row mb-0 text-start">
                                                <dt class="col-sm-4">Kategori</dt>
                                                <dd class="col-sm-8">: {{ $row->kategori }}</dd>

                                                <dt class="col-sm-4">Nama Kegiatan</dt>
                                                <dd class="col-sm-8">: {{ $row->nama_kegiatan }}</dd>

                                                <dt class="col-sm-4">Bentuk Kegiatan</dt>
                                                <dd class="col-sm-8">: {{ $row->bentuk_kegiatan }}</dd>

                                                <dt class="col-sm-4">Tingkat</dt>
                                                <dd class="col-sm-8">: {{ $row->tingkat }}</dd>

                                                <dt class="col-sm-4">Penyelenggara</dt>
                                                <dd class="col-sm-8">: {{ $row->penyelenggara }}</dd>

                                                <dt class="col-sm-4">Peran</dt>
                                                <dd class="col-sm-8">: {{ $row->peran }}</dd>

                                                <dt class="col-sm-4">Tanggal</dt>
                                                <dd class="col-sm-8">: {{
                                                    \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') }}</dd>

                                                <dt class="col-sm-4">Sertifikat</dt>
                                                <dd class="col-sm-8">
                                                    :
                                                    @if ($row->file_sertifikat)
                                                    <a href="{{ $row->file_sertifikat }}" target="_blank"
                                                        rel="noopener">Lihat Sertifikat</a>
                                                    @else
                                                    -
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">Lampiran</dt>
                                                <dd class="col-sm-8">
                                                    :
                                                    @if ($row->file_lampiran)
                                                    <a href="{{ $row->file_lampiran }}" target="_blank"
                                                        rel="noopener">Lihat Lampiran</a>
                                                    @else
                                                    -
                                                    @endif
                                                </dd>

                                                <dt class="col-sm-4">Status</dt>
                                                <dd class="col-sm-8">
                                                    : <span class="badge bg-{{ $badge }}">{{ $row->status_validasi
                                                        }}</span>
                                                    @if ($row->status_validasi === 'Ditinjau')
                                                    <a href="{{ route('mahasiswa.edit.tambahan', $row->id) }}"
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
                        <td colspan="12" class="text-center text-muted">Belum ada pengajuan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $tambahan->withQueryString()->links() }}
            </div>
        </div>
    </div>
</div>
@endsection