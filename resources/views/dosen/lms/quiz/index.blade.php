@extends('layouts.dosen')

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('dosen.lms.kelola', $jadwal) }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali ke Kelas</a>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createQuiz"><i class="bx bx-plus me-1"></i>Buat Quiz</button>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4 quiz-hero">
        <div class="card-body p-4 text-white">
            <span class="badge bg-white text-primary mb-2">MANAJEMEN QUIZ</span>
            <h3 class="text-white fw-bold mb-1">{{ $mataKuliah?->nama ?? '-' }}</h3>
            <p class="text-white-50 mb-0">{{ $mataKuliah?->matakuliah_id ?? '-' }} &bull; {{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($quizList as $quiz)
            <div class="col-md-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100 quiz-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="avatar-initial rounded bg-label-primary p-3"><i class="bx bx-question-mark fs-3"></i></span>
                            <span class="badge bg-label-{{ $quiz->aktif ? 'success' : 'secondary' }}">{{ $quiz->aktif ? 'Aktif' : 'Draft' }}</span>
                        </div>
                        <h5 class="fw-bold">{{ $quiz->judul }}</h5>
                        <p class="text-muted small">{{ str($quiz->deskripsi)->limit(90) ?: 'Tidak ada deskripsi.' }}</p>
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="badge bg-label-info">{{ $quiz->soal_count }} Soal</span>
                            <span class="badge bg-label-warning">{{ $quiz->attempts_count }} Pengerjaan</span>
                        </div>
                        <small class="text-muted d-block mb-3"><i class="bx bx-time me-1"></i>Deadline: {{ $quiz->deadline?->translatedFormat('d M Y H:i') ?? 'Tanpa deadline' }}</small>
                        <div class="d-flex gap-2">
                            <a href="{{ route('dosen.lms.quiz.manage', $quiz) }}" class="btn btn-primary flex-grow-1">Kelola Soal</a>
                            <a href="{{ route('dosen.lms.quiz.hasil', $quiz) }}" class="btn btn-outline-primary"><i class="bx bx-bar-chart"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><i class="bx bx-question-mark text-muted" style="font-size:4rem"></i><h5>Belum ada quiz</h5><p class="text-muted">Klik “Buat Quiz” untuk memulai.</p></div></div></div>
        @endforelse
    </div>
</div>

<div class="modal fade" id="createQuiz" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form action="{{ route('dosen.lms.quiz.store', $jadwal) }}" method="POST">@csrf
            <div class="modal-header"><h5 class="modal-title">Buat Quiz Baru</h5><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Judul Quiz</label><input name="judul" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Pertemuan</label><select name="pertemuan_id" class="form-select"><option value="">Umum</option>@foreach($pertemuan as $p)<option value="{{ $p->pertemuan_id }}">Pertemuan {{ $loop->iteration }} — {{ $p->topik }}</option>@endforeach</select></div>
                    <div class="col-12"><label class="form-label">Deskripsi/Petunjuk</label><textarea name="deskripsi" class="form-control" rows="3"></textarea></div>
                    <div class="col-md-4"><label class="form-label">Mulai</label><input type="datetime-local" name="mulai_at" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Deadline</label><input type="datetime-local" name="deadline" class="form-control"></div>
                    <div class="col-md-4"><label class="form-label">Durasi (menit)</label><input type="number" name="durasi_menit" min="1" class="form-control"></div>
                    <div class="col-12"><div class="form-check"><input type="checkbox" name="aktif" value="1" checked class="form-check-input" id="quizActive"><label for="quizActive" class="form-check-label">Langsung tampilkan kepada mahasiswa</label></div></div>
                </div>
            </div>
            <div class="modal-footer"><button class="btn btn-primary">Buat & Tambahkan Soal</button></div>
        </form>
    </div></div>
</div>
<style>.quiz-hero{border-radius:.9rem;background:linear-gradient(135deg,#696cff,#5a5de4 55%,#8592ff)}.quiz-card{transition:.2s}.quiz-card:hover{transform:translateY(-4px);box-shadow:0 .5rem 1.5rem rgba(67,89,113,.15)!important}</style>
@endsection
