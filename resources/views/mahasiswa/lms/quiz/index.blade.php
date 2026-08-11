@extends('layouts.mahasiswa')

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
@endphp
<div class="container-fluid">
    <div class="mb-3"><a href="{{ route('mahasiswa.lms.show',$jadwal) }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali ke Kelas</a></div>
    <div class="card border-0 shadow-sm overflow-hidden mb-4 quiz-hero"><div class="card-body p-4 text-white"><span class="badge bg-white text-primary mb-2">QUIZ LMS</span><h3 class="text-white fw-bold mb-1">{{ $mataKuliah?->nama ?? '-' }}</h3><p class="text-white-50 mb-0">{{ $mataKuliah?->matakuliah_id ?? '-' }}</p></div></div>
    <div class="row g-4">
        @forelse($quizList as $quiz)
            @php
                $attempt=$quiz->attempts->first(); $belum=$quiz->mulai_at&&now()->lt($quiz->mulai_at); $lewat=$quiz->deadline&&now()->gt($quiz->deadline);
                $status=!$attempt?($belum?'Belum Dimulai':($lewat?'Berakhir':'Belum Dikerjakan')):($attempt->status==='graded'?'Sudah Dinilai':($attempt->status==='submitted'?'Sudah Dikirim':'Draft'));
            @endphp
            <div class="col-md-6 col-xl-4"><div class="card border-0 shadow-sm h-100 quiz-card"><div class="card-body p-4">
                <div class="d-flex justify-content-between mb-3"><span class="avatar-initial rounded bg-label-primary p-3"><i class="bx bx-question-mark fs-3"></i></span><span class="badge bg-label-{{ $attempt?->status==='graded'?'success':($attempt?->status==='submitted'?'warning':'primary') }}">{{ $status }}</span></div>
                <h5 class="fw-bold">{{ $quiz->judul }}</h5><p class="text-muted small">{{ str($quiz->deskripsi)->limit(100) }}</p>
                <div class="d-flex gap-2 mb-3"><span class="badge bg-label-info">{{ $quiz->soal_count }} Soal</span>@if($quiz->durasi_menit)<span class="badge bg-label-secondary">{{ $quiz->durasi_menit }} Menit</span>@endif</div>
                <small class="text-muted d-block mb-3"><i class="bx bx-time me-1"></i>{{ $quiz->deadline?->translatedFormat('d M Y H:i') ?? 'Tanpa deadline' }}</small>
                <a href="{{ route('mahasiswa.lms.quiz.show',$quiz) }}" class="btn btn-primary w-100">{{ $attempt?'Lihat Quiz':'Mulai Quiz' }}</a>
            </div></div></div>
        @empty
            <div class="col-12"><div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bx bx-question-mark" style="font-size:4rem"></i><h5>Belum ada quiz aktif</h5></div></div></div>
        @endforelse
    </div>
</div>
<style>.quiz-hero{border-radius:.9rem;background:linear-gradient(135deg,#696cff,#5a5de4 55%,#8592ff)}.quiz-card{transition:.2s}.quiz-card:hover{transform:translateY(-4px);box-shadow:0 .5rem 1.5rem rgba(67,89,113,.15)!important}</style>
@endsection
