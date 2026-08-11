@extends('layouts.mahasiswa')

@section('content')
@php
    $totalBobot = (float) $quiz->soal->sum('bobot');
@endphp
<div class="container-fluid">
    <div class="mb-3"><a href="{{ route('mahasiswa.lms.quiz.index',$quiz->jadwal) }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Daftar Quiz</a></div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><strong>Jawaban belum dapat dikirim:</strong><ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
    <div class="card border-0 shadow-sm mb-4"><div class="card-body p-4"><div class="d-flex flex-wrap justify-content-between gap-3"><div><span class="badge bg-label-primary mb-2">QUIZ</span><h3 class="fw-bold mb-1">{{ $quiz->judul }}</h3><p class="text-muted mb-0">{{ $quiz->jadwal?->kurikulum?->mataKuliah?->nama }}</p></div><div class="text-end"><span class="badge bg-label-info">{{ $quiz->soal->count() }} Soal</span><span class="badge bg-label-secondary">Bobot {{ number_format($totalBobot,2) }}</span><small class="d-block text-muted mt-2">Deadline {{ $quiz->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small></div></div>@if($quiz->deskripsi)<hr><div>{!! nl2br(e($quiz->deskripsi)) !!}</div>@endif</div></div>

    @if($tidakAdaSoal)<div class="alert alert-warning"><i class="bx bx-info-circle me-2"></i>Quiz belum memiliki soal. Silakan hubungi dosen.</div>
    @elseif($belumMulai)<div class="alert alert-info"><i class="bx bx-time me-2"></i>Quiz dimulai {{ $quiz->mulai_at->translatedFormat('d M Y H:i') }}.</div>
    @elseif($durasiBerakhir)<div class="alert alert-danger"><i class="bx bx-time-five me-2"></i>Durasi pengerjaan quiz telah berakhir.</div>
    @elseif(!$bolehEdit)
        <div class="alert alert-{{ $attempt?->status==='graded'?'success':'warning' }}"><i class="bx bx-lock me-2"></i>Jawaban telah dikirim dan terkunci. @if($attempt?->status==='submitted')Menunggu penilaian dosen.@else Quiz sudah dinilai.@endif</div>
        @if($attempt?->izinkan_ulang)<div class="alert alert-primary">Dosen telah memberikan izin ulang. Muat ulang halaman untuk mengedit.</div>@endif
    @elseif($attempt?->izinkan_ulang)<div class="alert alert-warning"><i class="bx bx-lock-open me-2"></i>Izin ulang aktif. Setelah dikirim, jawaban akan terkunci kembali dan nilai essay sebelumnya dihapus.</div>
    @endif

    @if($bolehEdit)
    <form action="{{ route('mahasiswa.lms.quiz.submit',$quiz) }}" method="POST" enctype="multipart/form-data" onsubmit="return confirm('Kirim jawaban? Setelah dikirim jawaban akan terkunci.')">@csrf
    @endif
    @if($attempt && !$belumMulai)
        @foreach($quiz->soal as $soal)
            @php
                $jawaban = $jawabanBySoal->get($soal->soal_id);
            @endphp
            <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
                <div class="d-flex justify-content-between gap-2 mb-3"><h6 class="fw-bold mb-0">{{ $loop->iteration }}. {!! nl2br(e($soal->pertanyaan)) !!}</h6><span class="badge bg-label-secondary">Bobot {{ $soal->bobot }}</span></div>
                @if($soal->tipe==='pilihan_ganda')
                    @foreach($soal->opsi ?? [] as $i=>$opsi)
                        <label class="d-flex align-items-center border rounded p-3 mb-2 quiz-option"><input type="radio" name="pilihan[{{ $soal->soal_id }}]" value="{{ $i }}" class="form-check-input me-3" {{ (string)old('pilihan.'.$soal->soal_id, $jawaban?->pilihan_jawaban)===(string)$i?'checked':'' }} {{ $bolehEdit?'':'disabled' }} required><span><strong class="me-2">{{ chr(65+$i) }}.</strong>{{ $opsi }}</span></label>
                    @endforeach
                    @if($attempt?->status==='graded')
                        <div class="alert alert-{{ $jawaban?->nilai>0?'success':'danger' }} mt-3 mb-0">Nilai: {{ $jawaban?->nilai ?? 0 }} / {{ $soal->bobot }}</div>
                    @endif
                @else
                    <label class="form-label">Jawaban Teks</label><textarea name="jawaban_text[{{ $soal->soal_id }}]" class="form-control mb-3" rows="6" placeholder="Tulis jawaban di sini..." {{ $bolehEdit?'':'disabled' }}>{{ old('jawaban_text.'.$soal->soal_id, $jawaban?->jawaban_text) }}</textarea>
                    <label class="form-label">Atau Upload File</label><input type="file" name="jawaban_file[{{ $soal->soal_id }}]" class="form-control" {{ $bolehEdit?'':'disabled' }} accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.rar,.jpg,.jpeg,.png">
                    @if($jawaban?->file)<a href="{{ route('mahasiswa.lms.quiz.jawaban.download',$jawaban) }}" class="btn btn-sm btn-outline-primary mt-2"><i class="bx bx-download me-1"></i>File Jawaban Saat Ini</a>@endif
                    @if($attempt?->status==='graded')
                        <div class="alert alert-primary mt-3 mb-0"><strong>Nilai: {{ $jawaban?->nilai ?? 0 }} / {{ $soal->bobot }}</strong>@if($jawaban?->feedback)<div class="mt-1">Feedback: {{ $jawaban->feedback }}</div>@endif</div>
                    @endif
                @endif
            </div></div>
        @endforeach
    @endif
    @if($bolehEdit)
        <div class="d-flex justify-content-end"><button class="btn btn-primary btn-lg"><i class="bx bx-send me-1"></i>Kirim Jawaban & Kunci</button></div></form>
    @elseif($attempt?->status==='graded')
        <div class="card border-0 shadow-sm grade-result"><div class="card-body p-4 text-center"><small>Nilai Akhir Quiz</small><h2 class="fw-bold text-primary">{{ number_format($attempt->nilai_total,2) }} / {{ number_format($totalBobot,2) }}</h2>@if($attempt->feedback)<p class="mb-0">{{ $attempt->feedback }}</p>@endif</div></div>
    @endif
</div>
<style>.quiz-option{cursor:pointer;transition:.15s}.quiz-option:hover{border-color:#696cff!important;background:rgba(105,108,255,.04)}.grade-result{border-top:4px solid #696cff!important}</style>
@endsection
