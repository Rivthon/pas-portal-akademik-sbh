@extends('layouts.dosen')
@section('title', 'Kelola LMS - ' . ($jadwal->kurikulum?->mataKuliah?->nama ?? 'Mata Kuliah'))

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
    $programStudi = $jadwal->kurikulum?->programStudi;
    $totalMateri = $pertemuan->sum(fn ($data) => $data->materi->count());
    $totalTugas = $pertemuan->sum(fn ($data) => $data->tugas->count());
@endphp

<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        {{-- Navigasi Atas --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <a href="{{ route('dosen.lms.index') }}" class="btn btn-outline-primary rounded-pill shadow-sm btn-sm px-3">
                <i class="bx bx-arrow-back me-1"></i> Kembali ke LMS
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('dosen.lms.quiz.index', $jadwal) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="bx bx-question-mark me-1"></i> Quiz
                </a>
                <a href="{{ route('dosen.lms.gradebook', $jadwal) }}" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm">
                    <i class="bx bx-table me-1"></i> Gradebook
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <i class="bx bx-error-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Hero Banner Detail Mata Kuliah --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden lms-hero" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="bx bx-code-alt me-1"></i>{{ $mataKuliah?->matakuliah_id ?? 'Mata Kuliah' }}
                        </span>
                        <h3 class="text-white fw-bold mt-1 mb-2">{{ $mataKuliah?->nama ?? '-' }}</h3>
                        <p class="text-white-50 mb-3">
                            <i class="bx bx-buildings me-1"></i>{{ $programStudi?->nama ?? '-' }} &bull; <span class="badge bg-label-warning text-Black rounded-pill px-2">{{ strtoupper($jadwal->jenis_kelas ?? '-') }}</span>
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-layer me-1"></i>{{ $pertemuan->count() }} Pertemuan</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-file me-1"></i>{{ $totalMateri }} Materi</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-task me-1"></i>{{ $totalTugas }} Tugas</span>
                            <span class="badge bg-white text-primary rounded-pill px-3"><i class="bx bx-calendar me-1"></i>{{ $jadwal->hari ?? '-' }}, {{ $jadwal->jam_mulai ?? '-' }} - {{ $jadwal->jam_selesai ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center d-none d-md-block">
                        <i class="bx bx-book-open text-white" style="font-size: 7rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Section Title --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
            <div>
                <h5 class="fw-bold mb-1 text-dark"><i class="bx bx-layer text-primary me-2"></i>Kelola Pertemuan</h5>
                <small class="text-muted">Klik judul pertemuan untuk membuka atau menutup materi dan tugas.</small>
            </div>
            <span class="badge bg-label-primary rounded-pill px-3 py-2 fw-semibold">{{ $pertemuan->count() }} Pertemuan</span>
        </div>

        {{-- Accordion List Pertemuan --}}
        @forelse($pertemuan as $item)
            <div class="card border-0 shadow-sm mb-3 meeting-card bg-white">
                <button type="button"
                        class="card-header bg-white border-0 w-100 d-flex justify-content-between align-items-center gap-3 text-start meeting-toggle {{ $loop->first ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse"
                        data-bs-target="#meetingDosen{{ $item->pertemuan_id }}"
                        aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                        aria-controls="meetingDosen{{ $item->pertemuan_id }}">
                    <div class="d-flex align-items-center">
                        <span class="avatar-initial rounded-circle bg-label-primary me-3 meeting-number fw-bold">{{ $loop->iteration }}</span>
                        <span class="fw-bold text-dark fs-6">
                            Pertemuan {{ $loop->iteration }} &mdash; {{ $item->topik ?: 'Topik belum diisi' }}
                        </span>
                    </div>
                    <span class="d-flex align-items-center flex-wrap justify-content-end gap-2 text-muted">
                        <small class="fw-medium text-dark"><i class="bx bx-calendar me-1"></i>{{ \Carbon\Carbon::parse($item->tanggal_pertemuan)->translatedFormat('d F Y') }}</small>
                        <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-file me-1"></i>{{ $item->materi->count() }} Materi</span>
                        <span class="badge bg-label-warning rounded-pill px-3"><i class="bx bx-task me-1"></i>{{ $item->tugas->count() }} Tugas</span>
                        <span class="meeting-chevron ms-2"><i class="bx bx-chevron-down fs-3"></i></span>
                    </span>
                </button>

                <div id="meetingDosen{{ $item->pertemuan_id }}" class="collapse {{ $loop->first ? 'show' : '' }}">
                    <div class="card-body border-top bg-light pt-4">

                        {{-- Tabel Rincian Pertemuan --}}
                        <div class="table-responsive bg-white rounded-3 shadow-sm border p-3 mb-4">
                            <table class="table table-borderless table-sm mb-0 align-middle">
                                <tr class="border-bottom">
                                    <th width="180" class="text-muted fw-semibold py-2">Topik Pertemuan</th>
                                    <td class="fw-bold text-dark py-2">: {{ $item->topik ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-semibold py-2">Sub Topik</th>
                                    <td class="py-2">: {{ $item->sub_topik ?? '-' }}</td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-semibold py-2">Waktu Perkuliahan</th>
                                    <td class="py-2">: <span class="badge bg-label-secondary rounded-pill"><i class="bx bx-time-five me-1"></i>{{ $item->jam_mulai }} - {{ $item->jam_selesai }}</span></td>
                                </tr>
                                <tr class="border-bottom">
                                    <th class="text-muted fw-semibold py-2">Metode PBM</th>
                                    <td class="py-2">: <span class="badge bg-label-{{ strtolower($item->metode_pbm ?: 'offline') === 'online' ? 'primary' : 'secondary' }} rounded-pill"><i class="bx {{ strtolower($item->metode_pbm ?: 'offline') === 'online' ? 'bx-wifi' : 'bx-building' }} me-1"></i>{{ ucfirst($item->metode_pbm ?: 'offline') }}</span></td>
                                </tr>
                                <tr>
                                    <th class="text-muted fw-semibold py-2">Status Upload Materi</th>
                                    <td class="py-2">:
                                        @if($item->materi && $item->materi->count() > 0)
                                            <span class="badge bg-success rounded-pill px-3"><i class="bx bx-check-circle me-1"></i>Sudah Upload</span>
                                        @else
                                            <span class="badge bg-danger rounded-pill px-3"><i class="bx bx-x-circle me-1"></i>Belum Upload</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>

                        {{-- Tombol Aksi Tambah --}}
                        <div class="d-flex gap-2 mb-4">
                            <button class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#uploadMateri{{ $item->pertemuan_id }}">
                                <i class="fas fa-upload me-1"></i> Upload Materi
                            </button>

                            <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                data-bs-target="#modalTugas{{ $item->pertemuan_id }}">
                                <i class="fas fa-plus me-1"></i> Tambah Tugas
                            </button>
                        </div>

                        <!-- SECTION DAFTAR MATERI -->
                        @if($item->materi && $item->materi->count() > 0)
                            <div class="bg-white p-3 rounded-3 border shadow-sm mb-4">
                                <h6 class="fw-bold text-dark mb-3">
                                    <i class="fas fa-folder-open text-primary me-2"></i>Daftar Materi Perkuliahan
                                </h6>

                                @foreach($item->materi as $materi)
                                    <div class="d-flex justify-content-between align-items-center border rounded-3 p-3 mb-2 bg-light">
                                        <div>
                                            <strong class="text-dark d-block mb-1">{{ $materi->judul }}</strong>
                                            <span class="badge bg-label-info rounded-pill">
                                                {{ strtoupper($materi->tipe) }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-1 flex-wrap">
                                            @if($materi->file)
                                                <a href="{{ route('dosen.lms.materi.download', $materi->materi_id) }}" class="btn btn-sm btn-outline-success rounded-pill px-3"
                                                    target="_blank">
                                                    <i class="fas fa-eye me-1"></i> Lihat
                                                </a>
                                            @elseif($materi->youtube_url)
                                                <a href="{{ Str::startsWith($materi->youtube_url, ['http://', 'https://']) ? $materi->youtube_url : 'https://' . $materi->youtube_url }}"
                                                    target="_blank" class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fab fa-youtube me-1"></i> Buka Link
                                                </a>
                                            @endif

                                            <!-- TOMBOL EDIT MATERI -->
                                            <button type="button" class="btn btn-warning btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                                data-bs-target="#editMateri{{ $materi->materi_id }}">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </button>

                                            <!-- TOMBOL HAPUS MATERI -->
                                            <form action="{{ route('dosen.lms.materi.destroy', $materi->materi_id) }}" method="POST"
                                                class="d-inline form-delete" data-title="Hapus Materi?"
                                                data-text="File materi juga akan dihapus dari server.">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                    <i class="fas fa-trash me-1"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <!-- MODAL EDIT MATERI -->
                                    <div class="modal fade" id="editMateri{{ $materi->materi_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('dosen.lms.materi.update', $materi->materi_id) }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold">Edit Materi</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Judul Materi</label>
                                                            <input type="text" name="judul" value="{{ $materi->judul }}" class="form-control"
                                                                required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Deskripsi</label>
                                                            <textarea name="deskripsi" class="form-control" rows="3">{{ $materi->deskripsi }}</textarea>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Ganti File</label>
                                                            <input type="file" name="file" class="form-control mb-2">

                                                            @if($materi->file)
                                                                <div class="alert alert-light border d-flex align-items-center p-2 rounded mb-0 mt-2"
                                                                    style="font-size: 0.85rem;">
                                                                    <i class="fas fa-file-alt text-primary me-2 fs-5"></i>
                                                                    <div>
                                                                        <span class="text-muted">File saat ini:</span>
                                                                        <strong class="text-dark">{{ basename($materi->file) }}</strong>
                                                                        <span class="mx-2">|</span>
                                                                        <a href="{{ route('dosen.lms.materi.download', $materi->materi_id) }}"
                                                                            target="_blank" class="fw-bold text-success text-decoration-none">
                                                                            <i class="fas fa-external-link-alt me-1"></i> Klik untuk Lihat File
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @else
                                                                <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>Materi ini
                                                                    sebelumnya tidak menggunakan file berkas.</small>
                                                            @endif
                                                            <small class="text-muted d-block mt-2">Kosongkan kolom upload file jika tidak ingin
                                                                mengganti berkas sebelumnya.</small>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Youtube URL / Link Eksternal</label>
                                                            <input type="url" name="youtube_url" value="{{ $materi->youtube_url }}"
                                                                class="form-control" placeholder="https://youtube.com/...">
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="submit" class="btn btn-success rounded-pill px-4">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <!-- SECTION DAFTAR TUGAS -->
                        <div class="bg-white p-3 rounded-3 border shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold text-dark mb-0">
                                    <i class="fas fa-tasks text-primary me-2"></i>Daftar Tugas Perkuliahan
                                </h6>

                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                    data-bs-target="#modalTugas{{ $item->pertemuan_id }}">
                                    <i class="fas fa-plus me-1"></i> Tambah Tugas
                                </button>
                            </div>

                            @if($item->tugas && $item->tugas->count())
                                @foreach($item->tugas as $tugas)
                                    <div class="card mb-3 border shadow-none bg-light">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold text-dark mb-2">
                                                <i class="fas fa-file-signature text-primary me-2"></i>{{ $tugas->judul }}
                                            </h6>

                                            <p class="mb-2 text-muted small">
                                                {{ $tugas->deskripsi }}
                                            </p>

                                            <div class="row g-2 mb-3 small">
                                                <div class="col-md-6">
                                                    <span class="text-muted">Deadline:</span>
                                                    <strong class="text-danger"><i class="bx bx-time-five me-1"></i>{{ \Carbon\Carbon::parse($tugas->deadline)->format('d M Y H:i') }}</strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <span class="text-muted">Nilai Maksimal:</span>
                                                    <strong class="text-dark">{{ $tugas->nilai_maksimal }}</strong>
                                                </div>
                                            </div>

                                            <!-- CARD BADGES STATUS TUGAS -->
                                            <div class="mb-3 d-flex flex-wrap gap-1">
                                                @if($tugas->izinkan_terlambat)
                                                    <span class="badge bg-label-warning rounded-pill">
                                                        Pengumpulan terlambat diizinkan
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-secondary rounded-pill">
                                                        Ditutup setelah deadline
                                                    </span>
                                                @endif

                                                @if($tugas->izinkan_upload_ulang)
                                                    <span class="badge bg-label-info rounded-pill">
                                                        Upload ulang diizinkan
                                                    </span>
                                                @else
                                                    <span class="badge bg-label-dark rounded-pill">
                                                        Satu kali pengumpulan
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="d-flex flex-wrap gap-2">
                                                @if($tugas->lampiran)
                                                    <a href="{{ Storage::url($tugas->lampiran) }}" target="_blank" class="btn btn-outline-success btn-sm rounded-pill px-3">
                                                        <i class="fas fa-paperclip me-1"></i> Lampiran
                                                    </a>
                                                @endif

                                                <!-- TOMBOL EDIT TUGAS -->
                                                <button class="btn btn-warning btn-sm rounded-pill px-3" data-bs-toggle="modal"
                                                    data-bs-target="#editTugas{{ $tugas->tugas_id }}">
                                                    <i class="fas fa-edit me-1"></i> Edit
                                                </button>

                                                <!-- TOMBOL HAPUS TUGAS -->
                                                <form action="{{ route('dosen.lms.tugas.destroy', $tugas->tugas_id) }}" method="POST"
                                                    class="d-inline form-delete" data-title="Hapus Tugas?"
                                                    data-text="Data tugas dan pengumpulan mahasiswa akan dihapus.">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">
                                                        <i class="fas fa-trash me-1"></i> Hapus
                                                    </button>
                                                </form>

                                                <!-- TOMBOL PENGUMPULAN -->
                                                <a href="{{ route('dosen.lms.tugas.pengumpulan', $tugas->tugas_id) }}" class="btn btn-info btn-sm rounded-pill px-3 ms-auto">
                                                    <i class="fas fa-users me-1"></i> Pengumpulan
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- MODAL EDIT TUGAS -->
                                    <div class="modal fade" id="editTugas{{ $tugas->tugas_id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('dosen.lms.tugas.update', $tugas->tugas_id) }}" method="POST"
                                                    enctype="multipart/form-data">
                                                    @csrf
                                                    @method('PUT')

                                                    <div class="modal-header border-bottom">
                                                        <h5 class="modal-title fw-bold">Edit Tugas</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>

                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Judul Tugas</label>
                                                            <input type="text" name="judul" value="{{ $tugas->judul }}" class="form-control"
                                                                required>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Deskripsi</label>
                                                            <textarea name="deskripsi" class="form-control" rows="3">{{ $tugas->deskripsi }}</textarea>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Deadline</label>
                                                                <input type="datetime-local" name="deadline"
                                                                    value="{{ \Carbon\Carbon::parse($tugas->deadline)->format('Y-m-d\TH:i') }}"
                                                                    class="form-control" required>
                                                            </div>

                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Nilai Maksimal</label>
                                                                <input type="number" name="nilai_maksimal" value="{{ $tugas->nilai_maksimal }}"
                                                                    class="form-control">
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Ganti Lampiran</label>
                                                            <input type="file" name="lampiran" class="form-control mb-1">

                                                            @if($tugas->lampiran)
                                                                <small class="text-muted d-block">
                                                                    Lampiran saat ini:
                                                                    <a href="{{ Storage::url($tugas->lampiran) }}" target="_blank" class="fw-bold">
                                                                        {{ basename($tugas->lampiran) }}
                                                                    </a>
                                                                </small>
                                                            @endif
                                                            <small class="text-muted">Kosongkan jika tidak ingin mengganti lampiran.</small>
                                                        </div>

                                                        <!-- OPSI PENGUMPULAN (EDIT) -->
                                                        <div class="form-check mt-3">
                                                            <input class="form-check-input" type="checkbox" name="izinkan_terlambat" value="1"
                                                                id="editIzinkanTerlambat{{ $tugas->tugas_id }}"
                                                                {{ old('izinkan_terlambat', $tugas->izinkan_terlambat ?? 0) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="editIzinkanTerlambat{{ $tugas->tugas_id }}">
                                                                Izinkan pengumpulan setelah deadline
                                                            </label>
                                                        </div>

                                                        <div class="form-check mt-2">
                                                            <input class="form-check-input" type="checkbox" name="izinkan_upload_ulang" value="1"
                                                                id="editIzinkanUploadUlang{{ $tugas->tugas_id }}"
                                                                {{ old('izinkan_upload_ulang', $tugas->izinkan_upload_ulang ?? 1) ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="editIzinkanUploadUlang{{ $tugas->tugas_id }}">
                                                                Izinkan mahasiswa mengganti jawaban
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="modal-footer border-top bg-light">
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-light border mb-0 text-muted">
                                    <i class="bx bx-info-circle me-1"></i>Belum ada tugas pada pertemuan ini.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            <!-- MODAL UPLOAD MATERI -->
            <div class="modal fade" id="uploadMateri{{ $item->pertemuan_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('dosen.lms.materi.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="pertemuan_id" value="{{ $item->pertemuan_id }}">
                            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">

                            <div class="modal-header border-bottom">
                                <h5 class="modal-title fw-bold">Upload Materi</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Judul Materi</label>
                                    <input type="text" name="judul" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Upload File <br><small class="text-muted">PDF, PPT, Word, Excel, Gambar, Video, Zip</small></label>
                                    <input type="file" name="file" class="form-control">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Youtube URL / Link Eksternal</label>
                                    <input type="url" name="youtube_url" class="form-control" placeholder="https://youtube.com/...">
                                </div>
                            </div>

                            <div class="modal-footer border-top bg-light">
                                <button type="submit" class="btn btn-success rounded-pill px-4">Simpan Materi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- MODAL TAMBAH TUGAS -->
            <div class="modal fade" id="modalTugas{{ $item->pertemuan_id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <form action="{{ route('dosen.lms.tugas.store') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="jadwal_id" value="{{ $jadwal->id }}">
                            <input type="hidden" name="pertemuan_id" value="{{ $item->pertemuan_id }}">

                            <div class="modal-header border-bottom">
                                <h5 class="modal-title fw-bold">Tambah Tugas Baru</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Judul Tugas</label>
                                    <input type="text" name="judul" class="form-control" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Deskripsi</label>
                                    <textarea name="deskripsi" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Deadline</label>
                                        <input type="datetime-local" name="deadline" class="form-control" required>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nilai Maksimal</label>
                                        <input type="number" name="nilai_maksimal" value="100" class="form-control">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Lampiran Berkas</label>
                                    <input type="file" name="lampiran" class="form-control">
                                </div>

                                <!-- OPSI PENGUMPULAN (TAMBAH) -->
                                <div class="form-check mt-3">
                                    <input class="form-check-input" type="checkbox" name="izinkan_terlambat" value="1" id="addIzinkanTerlambat{{ $item->pertemuan_id }}">
                                    <label class="form-check-label" for="addIzinkanTerlambat{{ $item->pertemuan_id }}">
                                        Izinkan pengumpulan setelah deadline
                                    </label>
                                </div>

                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="izinkan_upload_ulang" value="1" id="addIzinkanUploadUlang{{ $item->pertemuan_id }}" checked>
                                    <label class="form-check-label" for="addIzinkanUploadUlang{{ $item->pertemuan_id }}">
                                        Izinkan mahasiswa mengganti jawaban
                                    </label>
                                </div>
                            </div>

                            <div class="modal-footer border-top bg-light">
                                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Tugas</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bx bx-calendar-x text-muted mb-3" style="font-size: 4rem;"></i>
                    <h5 class="fw-bold">Belum Ada Pertemuan</h5>
                    <p class="text-muted mb-0">Pertemuan perkuliahan belum tersedia untuk jadwal ini.</p>
                </div>
            </div>
        @endforelse

    </div>
</div>

<style>
    .lms-hero {
        border-radius: .9rem;
    }
    .meeting-card {
        border-radius: .85rem;
        overflow: hidden;
        transition: box-shadow .2s ease, transform .2s ease;
    }
    .meeting-card:hover {
        box-shadow: 0 .5rem 1.35rem rgba(67, 89, 113, .13) !important;
    }
    .meeting-toggle {
        min-height: 72px;
        color: #566a7f;
        cursor: pointer;
        transition: background-color .2s ease;
    }
    .meeting-toggle:hover {
        background-color: rgba(30, 60, 114, .045) !important;
    }
    .meeting-toggle:focus {
        outline: 0;
        box-shadow: inset 0 0 0 2px rgba(30, 60, 114, .18);
    }
    .meeting-number {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .meeting-chevron {
        color: #1e3c72;
        line-height: 1;
        transition: transform .25s ease;
    }
    .meeting-toggle:not(.collapsed) .meeting-chevron {
        transform: rotate(180deg);
    }
    @media (max-width: 767.98px) {
        .meeting-toggle {
            align-items: flex-start !important;
            flex-direction: column;
        }
        .meeting-toggle > span {
            width: 100%;
            justify-content: flex-start !important;
            padding-left: 3.25rem;
        }
    }
</style>
@endsection
