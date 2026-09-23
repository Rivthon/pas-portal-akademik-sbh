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
                            <i class="bx bx-task me-1"></i>{{ $tugas->tipe === 'pilihan_ganda' ? 'Tugas Pilihan Ganda A-E' : 'Tugas Perkuliahan' }}
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
                        @if($tugas->tipe === 'pilihan_ganda')
                            @if($tugas->deskripsi)
                                <div class="alert alert-light border mb-4">{!! nl2br(e($tugas->deskripsi)) !!}</div>
                            @endif
                            @php($bolehIsiPg = $bolehMengumpulkan && $bolehUploadUlang && !$sudahDinilai)
                            @if($bolehIsiPg && $tugas->soal->isNotEmpty())
                                <form action="{{ route('mahasiswa.lms.tugas.kumpulkan', $tugas) }}" method="POST">@csrf
                            @endif
                            @forelse($tugas->soal as $soal)
                                @php($jawabanSaatIni = old('jawaban_pg.'.$soal->soal_id, data_get($pengumpulan?->jawaban_pg, (string) $soal->soal_id)))
                                <div class="border rounded-3 p-3 mb-3">
                                    <div class="d-flex justify-content-between gap-2 mb-3">
                                        <h6 class="fw-bold mb-0">{{ $loop->iteration }}. {!! nl2br(e($soal->pertanyaan)) !!}</h6>
                                        <span class="badge bg-label-secondary">Bobot {{ $soal->bobot }}</span>
                                    </div>
                                    @foreach($soal->opsi as $i => $opsi)
                                        <label class="d-flex align-items-center border rounded p-2 mb-2 task-option">
                                            <input type="radio" name="jawaban_pg[{{ $soal->soal_id }}]" value="{{ $i }}"
                                                class="form-check-input me-3" @checked((string) $jawabanSaatIni === (string) $i)
                                                {{ $bolehIsiPg ? '' : 'disabled' }} required>
                                            <span><strong class="me-2">{{ chr(65 + $i) }}.</strong>{{ $opsi }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @empty
                                <div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i>Tugas pilihan ganda belum memiliki soal.</div>
                            @endforelse
                            @if($bolehIsiPg && $tugas->soal->isNotEmpty())
                                <div class="mb-3"><label class="form-label fw-semibold">Catatan (Opsional)</label><textarea name="catatan" class="form-control" rows="2">{{ old('catatan', $pengumpulan?->catatan) }}</textarea></div>
                                <button class="btn btn-primary w-100"><i class="bx bx-send me-1"></i>{{ $pengumpulan ? 'Simpan Perubahan Jawaban' : 'Kumpulkan Jawaban' }}</button>
                                </form>
                            @endif
                        @else
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

                            @if($pengumpulan->file)
                                <a href="{{ route('mahasiswa.lms.pengumpulan.download', $pengumpulan->pengumpulan_id) }}" class="btn btn-success rounded-pill btn-sm w-100 mb-3 shadow-sm">
                                    <i class="bx bx-download me-1"></i> Download Berkas Jawaban Saya
                                </a>
                            @endif
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
                        @elseif($pengumpulan && $deadlineTerlewat)
                            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-0">
                                <i class="bx bx-lock-alt me-1 fs-5 align-middle"></i>
                                Batas waktu pengumpulan telah berakhir. Jawaban tidak dapat diubah atau diunggah ulang.
                            </div>
                        @elseif($tugas->tipe !== 'pilihan_ganda' && $bolehMengumpulkan && $bolehUploadUlang)
                            @php($temporaryUploadEnabled = (bool) config('lms.temporary_task_upload.enabled', true))
                            <form action="{{ route('mahasiswa.lms.tugas.kumpulkan', $tugas->tugas_id) }}" method="POST" enctype="multipart/form-data" class="pt-2 border-top" id="task-submission-form">
                                @csrf
                                <input type="hidden" name="temporary_upload_token" id="temporary-upload-token">

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">
                                        {{ $pengumpulan ? 'Ganti Berkas Jawaban' : 'Upload Berkas Jawaban' }}
                                    </label>
                                    <input type="file" name="file" id="task-file-input" class="form-control mb-1" required
                                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png">
                                    <small class="text-muted d-block style-small">
                                        Maksimal 10 MB. Format: PDF, Word, PPT, Excel, ZIP, RAR, JPG, atau PNG.
                                    </small>
                                    @if($temporaryUploadEnabled)
                                        <div id="task-upload-progress-wrap" class="progress mt-2 d-none" style="height: 8px;">
                                            <div id="task-upload-progress" class="progress-bar progress-bar-striped progress-bar-animated" style="width: 0%"></div>
                                        </div>
                                        <div id="task-upload-status" class="small mt-2 text-muted" aria-live="polite">
                                            File akan diamankan segera setelah dipilih.
                                        </div>
                                        <button type="button" id="task-change-file" class="btn btn-sm btn-outline-secondary mt-2 d-none">
                                            <i class="bx bx-refresh me-1"></i>Ganti file
                                        </button>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark">Catatan Tambahan (Opsional)</label>
                                    <textarea name="catatan" class="form-control" rows="3" placeholder="Tuliskan pesan atau catatan singkat untuk dosen...">{{ old('catatan', $pengumpulan?->catatan) }}</textarea>
                                </div>

                                <button type="submit" id="task-submit-button" class="btn btn-primary rounded-pill w-100 shadow-sm" @disabled($temporaryUploadEnabled)>
                                    <i class="bx bx-cloud-upload me-1"></i>
                                    {{ $pengumpulan ? 'Upload Ulang Jawaban' : 'Kumpulkan Tugas' }}
                                </button>
                            </form>
                        @elseif($pengumpulan && !$bolehUploadUlang)
                            <div class="alert alert-info border-0 shadow-sm rounded-3 mb-0">
                                <i class="bx bx-info-circle me-1 fs-5 align-middle"></i>
                                Jawaban sudah dikumpulkan. Dosen tidak mengizinkan penggantian jawaban.
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@if(($temporaryUploadEnabled ?? false) && $tugas->tipe !== 'pilihan_ganda')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('task-submission-form');
    const fileInput = document.getElementById('task-file-input');
    const tokenInput = document.getElementById('temporary-upload-token');
    const submitButton = document.getElementById('task-submit-button');
    const progressWrap = document.getElementById('task-upload-progress-wrap');
    const progressBar = document.getElementById('task-upload-progress');
    const status = document.getElementById('task-upload-status');
    const changeButton = document.getElementById('task-change-file');
    const uploadUrl = @json(route('mahasiswa.lms.tugas.upload-sementara', $tugas->tugas_id));
    const csrfToken = @json(csrf_token());
    const maxBytes = {{ (int) config('lms.temporary_task_upload.max_kilobytes', 10240) * 1024 }};
    let activeRequest = null;

    function resetSelection(message = 'Silakan pilih ulang file jawaban.') {
        tokenInput.value = '';
        fileInput.disabled = false;
        fileInput.value = '';
        fileInput.required = true;
        submitButton.disabled = true;
        changeButton.classList.add('d-none');
        progressWrap.classList.add('d-none');
        progressBar.style.width = '0%';
        progressBar.classList.remove('bg-success', 'bg-danger');
        status.className = 'small mt-2 text-danger';
        status.textContent = message;
    }

    function errorMessage(xhr) {
        if (xhr.status === 413) {
            return 'File ditolak server karena terlalu besar. Pastikan ukurannya maksimal 10 MB.';
        }

        try {
            const payload = JSON.parse(xhr.responseText);
            const validationMessage = payload.errors
                ? Object.values(payload.errors).flat()[0]
                : null;
            return validationMessage || payload.message || 'Upload sementara gagal. Silakan coba kembali.';
        } catch (error) {
            return xhr.status === 0
                ? 'Koneksi terputus saat upload. Periksa jaringan lalu pilih ulang file.'
                : 'Upload sementara gagal. Silakan coba kembali.';
        }
    }

    async function fileCanStillBeRead(file) {
        const sampleSize = Math.min(file.size, 64 * 1024);
        await file.slice(0, sampleSize).arrayBuffer();
        if (file.size > sampleSize) {
            await file.slice(file.size - sampleSize).arrayBuffer();
        }
    }

    fileInput.addEventListener('change', async function () {
        const file = fileInput.files[0];
        if (!file) {
            resetSelection('File belum dipilih.');
            return;
        }

        if (file.size > maxBytes) {
            resetSelection('Ukuran file melebihi batas 10 MB.');
            return;
        }

        try {
            await fileCanStillBeRead(file);
        } catch (error) {
            resetSelection('File berubah atau tidak dapat dibaca. Pilih ulang file dan jangan pindahkan/ubah file selama proses upload.');
            return;
        }

        if (activeRequest) {
            activeRequest.abort();
        }

        tokenInput.value = '';
        submitButton.disabled = true;
        progressWrap.classList.remove('d-none');
        progressBar.classList.remove('bg-success', 'bg-danger');
        progressBar.style.width = '0%';
        status.className = 'small mt-2 text-primary';
        status.textContent = 'Mengamankan file ke server...';

        const body = new FormData();
        body.append('_token', csrfToken);
        body.append('file', file);
        const xhr = new XMLHttpRequest();
        activeRequest = xhr;
        xhr.open('POST', uploadUrl, true);
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.upload.addEventListener('progress', function (event) {
            if (event.lengthComputable) {
                const percentage = Math.round((event.loaded / event.total) * 100);
                progressBar.style.width = percentage + '%';
                status.textContent = 'Mengunggah file... ' + percentage + '%';
            }
        });
        xhr.addEventListener('load', function () {
            activeRequest = null;
            if (xhr.status < 200 || xhr.status >= 300) {
                progressBar.classList.add('bg-danger');
                resetSelection(errorMessage(xhr));
                return;
            }

            const payload = JSON.parse(xhr.responseText);
            tokenInput.value = payload.token;
            fileInput.disabled = true;
            fileInput.required = false;
            submitButton.disabled = false;
            changeButton.classList.remove('d-none');
            progressBar.style.width = '100%';
            progressBar.classList.add('bg-success');
            status.className = 'small mt-2 text-success fw-semibold';
            status.textContent = 'File siap dikumpulkan: ' + payload.name;
        });
        xhr.addEventListener('error', function () {
            activeRequest = null;
            resetSelection('Koneksi terputus saat upload. Periksa jaringan lalu pilih ulang file.');
        });
        xhr.addEventListener('abort', function () {
            activeRequest = null;
        });
        xhr.send(body);
    });

    changeButton.addEventListener('click', function () {
        resetSelection('Silakan pilih file pengganti.');
        fileInput.click();
    });

    form.addEventListener('submit', function (event) {
        if (!tokenInput.value) {
            event.preventDefault();
            status.className = 'small mt-2 text-danger';
            status.textContent = 'Tunggu sampai upload file selesai sebelum mengumpulkan tugas.';
        }
    });
});
</script>
@endif

<style>
    .style-description {
        font-size: 0.95rem;
    }
    .style-small {
        font-size: 0.8rem;
    }
    .task-option { cursor: pointer; transition: .15s; }
    .task-option:hover { border-color: #696cff !important; background: rgba(105, 108, 255, .04); }
</style>
@endsection
