@extends('layouts.dosen')

@section('content')
@php
    $totalBobot = (float) $quiz->soal->sum('bobot');
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <a href="{{ route('dosen.lms.quiz.manage', $quiz) }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Kelola Soal</a>
        <a href="{{ route('dosen.lms.quiz.index', $quiz->jadwal) }}" class="btn btn-sm btn-outline-primary">Daftar Quiz</a>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4 result-hero"><div class="card-body p-4 text-white">
        <span class="badge bg-white text-primary mb-2">HASIL & PENILAIAN QUIZ</span><h3 class="text-white fw-bold mb-1">{{ $quiz->judul }}</h3>
        <p class="text-white-50 mb-0">{{ $quiz->jadwal?->kurikulum?->mataKuliah?->nama }} &bull; {{ $quiz->soal->count() }} soal &bull; Total bobot {{ number_format($totalBobot, 2) }}</p>
    </div></div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-group text-primary me-2"></i>Daftar Mahasiswa</h5></div>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>No</th><th>NIM</th><th>Mahasiswa</th><th>Status</th><th>Dikirim</th><th class="text-center">Nilai</th><th class="text-center">Aksi</th></tr></thead>
            <tbody>
            @forelse($peserta as $mahasiswa)
                @php
                    $attempt = $attemptByMahasiswa->get($mahasiswa->mahasiswa_id);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td><td>{{ $mahasiswa->nim ?? '-' }}</td><td><strong>{{ $mahasiswa->nama ?? '-' }}</strong></td>
                    <td>
                        @if(!$attempt)<span class="badge bg-label-secondary">Belum Mengerjakan</span>
                        @elseif($attempt->status==='graded')<span class="badge bg-label-success">Sudah Dinilai</span>
                        @elseif($attempt->status==='submitted')<span class="badge bg-label-warning">Menunggu Penilaian</span>
                        @else<span class="badge bg-label-info">Draft</span>@endif
                        @if($attempt?->izinkan_ulang)<span class="badge bg-label-primary">Izin Ulang Aktif</span>@endif
                    </td>
                    <td>{{ $attempt?->submitted_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                    <td class="text-center">{{ $attempt?->nilai_total !== null ? number_format($attempt->nilai_total,2).' / '.number_format($totalBobot,2) : '-' }}</td>
                    <td class="text-center">
                        @if($attempt && in_array($attempt->status,['submitted','graded']))
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#grade{{ $attempt->attempt_id }}"><i class="bx bx-search-alt me-1"></i>Periksa</button>
                        @else <span class="text-muted">-</span> @endif
                    </td>
                </tr>

                @if($attempt && in_array($attempt->status,['submitted','graded']))
                <div class="modal fade" id="grade{{ $attempt->attempt_id }}" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content">
                    <form action="{{ route('dosen.lms.quiz.attempt.nilai', $attempt) }}" method="POST">@csrf @method('PUT')
                    <div class="modal-header"><div><h5 class="modal-title">Jawaban {{ $mahasiswa->nama }}</h5><small class="text-muted">{{ $mahasiswa->nim }}</small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        @foreach($quiz->soal as $soal)
                            @php
                                $jawaban = $attempt->jawaban->firstWhere('soal_id', $soal->soal_id);
                            @endphp
                            <div class="border rounded p-3 mb-3">
                                <div class="d-flex justify-content-between gap-2 mb-2"><strong>{{ $loop->iteration }}. {{ $soal->pertanyaan }}</strong><span class="badge bg-label-secondary">Bobot {{ $soal->bobot }}</span></div>
                                @if($soal->tipe==='pilihan_ganda')
                                    @php
                                        $benar = $jawaban && (string) $jawaban->pilihan_jawaban === (string) $soal->kunci_jawaban;
                                    @endphp
                                    <div class="alert alert-{{ $benar?'success':'danger' }} py-2 mb-0">Jawaban: {{ isset($soal->opsi[(int)($jawaban?->pilihan_jawaban)]) ? chr(65+(int)$jawaban->pilihan_jawaban).'. '.$soal->opsi[(int)$jawaban->pilihan_jawaban] : '-' }} — {{ $benar?'Benar':'Salah' }} ({{ $jawaban?->nilai ?? 0 }})</div>
                                @else
                                    <div class="bg-light rounded p-3 mb-2">{!! $jawaban?->jawaban_text ? nl2br(e($jawaban->jawaban_text)) : '<span class="text-muted">Tidak ada jawaban teks.</span>' !!}</div>
                                    @if($jawaban?->file)<a href="{{ route('dosen.lms.quiz.jawaban.download',$jawaban) }}" class="btn btn-sm btn-outline-primary mb-3"><i class="bx bx-download me-1"></i>Unduh File Jawaban</a>@endif
                                    <div class="row g-2"><div class="col-md-3"><label class="form-label">Nilai</label><input type="number" step=".01" min="0" max="{{ $soal->bobot }}" name="nilai[{{ $jawaban?->jawaban_id }}]" value="{{ $jawaban?->nilai }}" class="form-control" {{ $attempt->status==='submitted'?'required':'disabled' }}></div><div class="col-md-9"><label class="form-label">Feedback Soal</label><textarea name="feedback_jawaban[{{ $jawaban?->jawaban_id }}]" class="form-control" {{ $attempt->status==='graded'?'disabled':'' }}>{{ $jawaban?->feedback }}</textarea></div></div>
                                @endif
                            </div>
                        @endforeach
                        <label class="form-label">Feedback Umum</label><textarea name="feedback" class="form-control" rows="3" {{ $attempt->status==='graded'?'disabled':'' }}>{{ $attempt->feedback }}</textarea>
                    </div>
                    <div class="modal-footer">
                        @if(!$attempt->izinkan_ulang)
                            <!-- <button type="submit" form="retryForm{{ $attempt->attempt_id }}" class="btn btn-outline-warning"><i class="bx bx-lock-open me-1"></i>Izinkan Ulang</button> -->
                        @endif
                        @if($attempt->status==='submitted')<button class="btn btn-primary"><i class="bx bx-check me-1"></i>Simpan Nilai & Kunci</button>@endif
                    </div>
                    </form>
                    <form id="retryForm{{ $attempt->attempt_id }}" action="{{ route('dosen.lms.quiz.attempt.izinkan-ulang',$attempt) }}" method="POST">@csrf @method('PUT')</form>
                </div></div></div>
                @endif
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada mahasiswa.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </div>
</div>
<style>.result-hero{border-radius:.9rem;background:linear-gradient(135deg,#696cff,#5a5de4 55%,#8592ff)}</style>
@endsection
