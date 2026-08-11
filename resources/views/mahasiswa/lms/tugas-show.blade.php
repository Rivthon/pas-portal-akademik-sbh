@extends('layouts.mahasiswa')
@section('title', 'Detail Tugas - ' . $tugas->judul)

@section('content')
<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        {{-- Navigasi Kembali --}}
        @if($tugas->jadwal)
            <div class="mb-3">
                <a href="{{ route('mahasiswa.lms.show', $tugas->jadwal) }}" class="btn btn-outline-primary rounded-pill shadow-sm btn-sm px-3">
                    <i class="bx bx-arrow-back me-1"></i> Kembali ke Kelas
                </a>
            </div>
        @endif

        {{-- Alert Notifikasi --}}
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

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 mb-3" role="alert">
                <div class="fw-bold mb-1"><i class="bx bx-error me-1"></i>Terjadi Kesalahan:</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Hero Header Tugas --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-9">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="bx bx-task me-1"></i>Tugas Perkuliahan
                        </span>
                        <h3 class="text-white fw-bold mt-1 mb-2">{{ $tugas->judul }}</h3>
                        <div class="d-flex flex-wrap gap-2 mt-3">
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="bx bx-time-five me-1 text-danger"></i>Deadline: {{ $tugas->deadline->format('d M Y H:i') }}
                            </span>
                            <span class="badge bg-white text-primary rounded-pill px-3 py-2">
                                <i class="bx bx-star me-1 text-warning"></i>Nilai Maksimal: {{ $tugas->nilai_maksimal }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center d-none d-md-block">
                        <i class="bx bx-file-find text-white" style="font-size: 6.5rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- Rincian Deskripsi & Lampiran Tugas --}}
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bx bx-detail text-primary me-2"></i>Deskripsi & Lampiran</h5>
                    </div>
                    <div class="card-body pt-4">
                        <div class="mb-4">
                            <label class="fw-bold text-muted small text-uppercase d-block mb-2">Instruksi Tugas</label>
                            <div class="p-3 bg-light rounded-3 text-dark style-description" style="line-height: 1.7;">
                                {!! nl2br(e($tugas->deskripsi ?: 'Tidak ada deskripsi tambahan untuk tugas ini.')) !!}
                            </div>
                        </div>

                        @if($tugas->lampiran)
                            <div class="pt-3 border-top">
                                <label class="fw-bold text-muted small text-uppercase d-block mb-2">Lampiran Berkas dari Dosen</label>
                                <a href="{{ Storage::url($tugas->lampiran) }}" target="_blank" class="btn btn-outline-primary rounded-pill px-3 shadow-sm">
                                    <i class="bx bx-paperclip me-1"></i> lihat / Download Lampiran Tugas
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Panel Status & Form Pengumpulan --}}
            <div class="col-lg-5 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="fw-bold text-dark mb-0"><i class="bx bx-upload text-primary me-2"></i>Status Pengumpulan</h5>
                    </div>
                    <div class="card-body pt-4">

                        {{-- Box Status Pengumpulan Saat Ini --}}
                        @if($pengumpulan)
                            @php
                                $terlambat = $pengumpulan->waktu_upload->gt($tugas->deadline);
                            @endphp

                            <div class="alert alert-success border-0 shadow-sm rounded-3 mb-3 p-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bx bx-check-circle fs-3 text-success me-2"></i>
                                    <strong class="fs-6 text-success">Sudah Mengumpulkan Jawaban</strong>
                                </div>
                                <hr class="my-2">
                                <div class="small text-dark mb-1">
                                    <span class="text-muted">Waktu Upload:</span> <strong>{{ $pengumpulan->waktu_upload->format('d M Y H:i') }}</strong>
                                </div>
                                <div class="small text-dark mb-2">
                                    <span class="text-muted">Status Kehadiran:</span>
                                    @if($terlambat)
                                        <span class="badge bg-danger rounded-pill px-2">Terlambat</span>
                                    @else
                                        <span class="badge bg-success rounded-pill px-2">Tepat Waktu</span>
                                    @endif
                                </div>

                                @if(!is_null($pengumpulan->nilai))
                                    <div class="p-2 bg-white rounded border mt-2">
                                        <span class="text-muted small d-block">Nilai Akhir:</span>
                                        <span class="fs-4 fw-bold text-primary">{{ $pengumpulan->nilai }}</span>
                                        <span class="text-muted">/ {{ $tugas->nilai_maksimal }}</span>
                                    </div>
                                @endif

                                @if($pengumpulan->feedback)
                                    <div class="p-2 bg-white rounded border mt-2">
                                        <span class="text-muted small d-block fw-bold"><i class="bx bx-comment-detail me-1"></i>Feedback Dosen:</span>
                                        <div class="small text-dark mt-1">{!! nl2br(e($pengumpulan->feedback)) !!}</div>
                                    </div>
                                @endif
                            </div>

                            <a href="{{ route('mahasiswa.lms.pengumpulan.download', $pengumpulan->pengumpulan_id) }}" class="btn btn-success rounded-pill btn-sm w-100 mb-3 shadow-sm">
                                <i class="bx bx-download me-1"></i> Download Berkas Jawaban Saya
                            </a>
                        @else
                            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="bx bx-info-circle fs-4 text-warning me-2"></i>
                                    <span>Anda belum mengumpulkan jawaban untuk tugas ini.</span>
                                </div>
                            </div>
                        @endif

                        {{-- Pesan Keterangan Deadline --}}
                        @if($deadlineTerlewat && !$tugas->izinkan_terlambat)
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-3">
                                <i class="bx bx-lock-alt me-1 fs-5 align-middle"></i>
                                <strong>Tutup:</strong> Deadline telah berakhir. Pengumpulan tugas sudah ditutup.
                            </div>
                        @elseif($deadlineTerlewat && $tugas->izinkan_terlambat)
                            <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-3">
                                <i class="bx bx-time-five me-1 fs-5 align-middle"></i>
                                <strong>Perhatian:</strong> Deadline telah berakhir, namun pengumpulan terlambat masih diizinkan.
                            </div>
                        @endif

                        {{-- Form Upload / Edit Jawaban --}}
                        @if($sudahDinilai)
                            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-0">
                                <div class="d-flex align-items-start">
                                    <i class="bx bx-lock-alt me-2 fs-4 text-info"></i>
                                    <div>
                                        <strong class="d-block mb-1">Jawaban sudah dikunci</strong>
                                        Jawaban tidak dapat diupload ulang karena tugas ini sudah dinilai oleh dosen.
                                    </div>
                                </div>
                            </div>
                        @elseif($bolehMengumpulkan && $bolehUploadUlang)
                            <form action="{{ route('mahasiswa.lms.tugas.kumpulkan', $tugas->tugas_id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-top">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">
                                        {{ $pengumpulan ? 'Ganti Berkas Jawaban' : 'Upload Berkas Jawaban' }}
                                    </label>
                                    <input type="file" name="file" class="form-control mb-1" required>
                                    <small class="text-muted d-block style-small">
                                        Maksimal 50 MB. Format: PDF, Word, PPT, Excel, ZIP, RAR, JPG, atau PNG.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Catatan Tambahan (Opsional)</label>
                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Tuliskan pesan atau catatan singkat untuk dosen...">{{ old('catatan', $pengumpulan?->catatan) }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-primary rounded-pill w-100 shadow-sm">
                                    <i class="bx bx-cloud-upload me-1"></i>
                                    {{ $pengumpulan ? 'Upload Ulang Jawaban' : 'Kumpulkan Tugas' }}
                                </button>
                            </form>
                        @elseif($pengumpulan && !$bolehUploadUlang)
                            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-0">
                                <i class="bx bx-info-circle me-1 fs-5 align-middle"></i>
                                Jawaban sudah dikumpulkan. Dosen tidak mengizinkan penggantian file jawaban.
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
    .style-description {
        font-size: 0.95rem;
    }
    .style-small {
        font-size: 0.8rem;
    }
</style>
@endsection
