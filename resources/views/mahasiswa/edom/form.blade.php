@extends('layouts.mahasiswa')

@section('content')
<style>
    .rating-wrapper {
        display: flex;
        gap: 15px;
        justify-content: center;
        flex-wrap: wrap;
    }
    .rating-item {
        flex: 1;
        min-width: 80px;
        max-width: 120px;
    }
    .rating-label {
        display: block;
        padding: 10px;
        border: 1px solid #d9dee3;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        height: 100%;
        background: #fff;
    }
    .rating-item input:checked + label {
        border-color: #696cff;
        background-color: rgba(105, 108, 255, 0.05);
        box-shadow: 0 0 0 1px #696cff;
    }
    .rating-item input:checked + label .emoji {
        filter: grayscale(0%) scale(1.2);
    }
    .rating-item input:checked + label small {
        font-weight: 700;
        color: #696cff !important;
    }
    .rating-label:hover {
        border-color: #696cff;
    }
    .rating-label:hover .emoji {
        filter: grayscale(0%) scale(1.1);
    }
    .step-card {
        border-left: 4px solid #d9dee3;
        transition: border-color 0.3s ease;
    }
    .step-card.answered {
        border-left-color: #71dd37;
    }
</style>
<div class="container">
    <div class="col-md-12">
        <div class="row mt-4">
            <div class="col-md-12">
                <!-- Header Section -->
                <div class="card shadow-sm mb-4">
                    <div class="d-flex align-items-center g-0">
                        <!-- Content Section -->
                        <div class="col-md-7">
                            <div class="card-body">
                                <h2 class="card-title text-primary mb-3 fw-bold">
                                    Formulir Evaluasi Dosen Mengajar
                                </h2>
                                <p class="mb-4 text-muted" style="line-height: 1.6;">
                                    Semua pertanyaan wajib diisi dengan benar dan jujur.
                                </p>
                                <div class="mb-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-book-open me-2"></i>
                                        <p class="mb-0"><strong>Mata Kuliah:</strong> {{
                                            $krs->kurikulum->matakuliah->nama }}
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bx bx-building me-2"></i>
                                        <p class="mb-0"><strong>Program Studi:</strong> {{
                                            $krs->kurikulum->programStudi->nama }}</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-user me-2"></i>
                                        <p class="mb-0"><strong>Dosen:</strong> {{ $dosen->nama }}</p>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <i class="bx bx-label me-2"></i>
                                        <p class="mb-0"><strong>Jenis Dosen:</strong> {{ ucfirst($dosen->jenis_dosen) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Image Section -->
                        <div class="col-md-5 text-center">
                            <div class="p-3">
                                <img src="{{ asset('assets/img/illustrations/chat.png') }}" class="img-fluid"
                                    alt="Illustration of a schedule" style="max-height: 200px;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Error Section -->
                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Progress Section -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold text-dark">Progress Pengisian</span>
                            <span class="badge bg-primary" id="progress-text">0 / {{ count($evaluasis) }} Pertanyaan</span>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

                <!-- Form Section -->
                <form id="formEdom"
                    action="{{ route('mahasiswa.edom.submit', ['krs_id' => $krs->krs_id, 'dosen_id' => $dosen->dosen_id]) }}"
                    method="POST">
                    @csrf
                    <input type="hidden" name="krs_id" value="{{ $krs->krs_id }}">
                    <!-- Evaluation Questions -->
                    @foreach ($evaluasis as $index => $evaluasi)
                    <div class="card mb-3 step-card shadow-none border border-secondary border-start-0">
                        <div class="card-body">
                            <h6 class="mb-3 text-dark"><span class="badge bg-label-primary me-2">{{ $index + 1 }}</span> {{ $evaluasi->nama }}</h6>
                            <div class="rating-wrapper">
                                @foreach (range(1, 5) as $value)
                                <div class="rating-item text-center">
                                    <input class="form-check-input d-none" type="radio" name="responses[{{ $evaluasi->eval_id }}]"
                                        id="evaluasi_{{ $evaluasi->eval_id }}_{{ $value }}" value="{{ $value }}" {{
                                        old("responses.{$evaluasi->eval_id}") == $value ? 'checked' : '' }}
                                        required onchange="updateProgress(this)">
                                    <label class="rating-label" for="evaluasi_{{ $evaluasi->eval_id }}_{{ $value }}">
                                        <div class="emoji fs-2 mb-1" style="filter: grayscale(100%); transition: 0.2s; display: inline-block;">
                                            @switch($value)
                                                @case(1) 😞 @break
                                                @case(2) 😕 @break
                                                @case(3) 😐 @break
                                                @case(4) 🙂 @break
                                                @case(5) 😄 @break
                                            @endswitch
                                        </div>
                                        <small class="d-block text-muted" style="line-height: 1.2;">
                                            @switch($value)
                                                @case(1) Sangat Tidak Setuju @break
                                                @case(2) Tidak Setuju @break
                                                @case(3) Netral @break
                                                @case(4) Setuju @break
                                                @case(5) Sangat Setuju @break
                                            @endswitch
                                        </small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach

                    <!-- Suggestion Section -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="mb-3"><span class="badge bg-label-primary me-2"><i class="bx bx-message-square-dots"></i></span> Saran untuk Dosen:</h6>
                            <div class="form-group">
                                <textarea name="suggestion" id="suggestion" class="form-control" rows="4"
                                    placeholder="Tulis saran, kritik membangun, atau apresiasi Anda di sini..."
                                    required oninput="updateCharCount(this)">{{ old('suggestion') }}</textarea>
                                <div class="d-flex justify-content-between mt-2">
                                    <small class="text-muted"><i class="bx bx-info-circle"></i> Minimal 10 karakter.</small>
                                    <small class="text-muted"><span id="char-count">0</span>/255</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route('mahasiswa.edom.index') }}" class="btn btn-outline-secondary">
                                    <i class="bx bx-arrow-back me-1"></i> Kembali
                                </a>
                                <button type="button" class="btn btn-primary" onclick="confirmSubmit()">
                                    <i class="bx bx-send me-1"></i> Submit EDOM
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->

                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection

@push('script')
<script>
    const totalQuestions = {{ count($evaluasis) }};

    function updateProgress(element = null) {
        // Count checked radios
        const checked = document.querySelectorAll('input[type="radio"]:checked').length;
        const percentage = Math.round((checked / totalQuestions) * 100);

        const progressBar = document.getElementById('progress-bar');
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);
        document.getElementById('progress-text').innerText = checked + ' / ' + totalQuestions + ' Pertanyaan';

        if (percentage === 100) {
            progressBar.classList.remove('bg-primary');
            progressBar.classList.add('bg-success');
        } else {
            progressBar.classList.remove('bg-success');
            progressBar.classList.add('bg-primary');
        }

        // Update card border
        document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
            input.closest('.step-card').classList.add('answered');
        });

        // Auto scroll to next unanswered slightly delayed to allow visual feedback
        if (element) {
            setTimeout(() => {
                const allCards = Array.from(document.querySelectorAll('.step-card'));
                const currentIndex = allCards.indexOf(element.closest('.step-card'));

                // If there is a next card, scroll to it
                if (currentIndex >= 0 && currentIndex < allCards.length - 1) {
                    const nextCard = allCards[currentIndex + 1];
                    // Only scroll if next card isn't answered yet
                    if (!nextCard.classList.contains('answered')) {
                        const y = nextCard.getBoundingClientRect().top + window.scrollY - 100; // offset for navbar
                        window.scrollTo({top: y, behavior: 'smooth'});
                    }
                } else if (currentIndex === allCards.length - 1) {
                    // If last card, scroll to suggestion
                    document.getElementById('suggestion').focus();
                }
            }, 300);
        }
    }

    function updateCharCount(el) {
        const len = el.value.length;
        document.getElementById('char-count').innerText = len;
        if(len > 255) {
            el.value = el.value.substring(0, 255);
            document.getElementById('char-count').innerText = 255;
        }
    }

    function confirmSubmit() {
        const checked = document.querySelectorAll('input[type="radio"]:checked').length;
        const suggestion = document.getElementById('suggestion').value.trim();

        if (checked < totalQuestions) {
            Swal.fire({
                icon: 'warning',
                title: 'Belum Selesai',
                text: `Harap jawab semua pertanyaan (${checked}/${totalQuestions} terjawab).`,
            });

            // Scroll to first unanswered
            const firstUnanswered = document.querySelector('.step-card:not(.answered)');
            if (firstUnanswered) {
                const y = firstUnanswered.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({top: y, behavior: 'smooth'});
            }
            return;
        }

        if (suggestion.length < 10) {
            Swal.fire({
                icon: 'warning',
                title: 'Saran Terlalu Pendek',
                text: 'Harap berikan saran minimal 10 karakter.',
            });
            document.getElementById('suggestion').focus();
            return;
        }

        Swal.fire({
            title: 'Kirim Penilaian?',
            text: "Pastikan penilaian yang Anda berikan objektif. Data yang sudah dikirim tidak dapat diubah lagi.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Ya, Kirim!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menyimpan...',
                    text: 'Mohon tunggu',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });
                document.getElementById('formEdom').submit();
            }
        });
    }

    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        updateProgress();
        const suggestionEl = document.getElementById('suggestion');
        if (suggestionEl) updateCharCount(suggestionEl);
    });
</script>
@endpush