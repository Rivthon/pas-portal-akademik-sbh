@extends('layouts.dosen')

@section('content')
@php
    $quizLocked = (int) $quiz->attempts_count > 0;
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <a href="{{ route('dosen.lms.quiz.index', $quiz->jadwal) }}" class="btn btn-sm btn-label-secondary"><i class="bx bx-arrow-back me-1"></i>Daftar Quiz</a>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary"
                @if($quiz->soal->isNotEmpty() && $destinationQuizzes->isNotEmpty()) data-bs-toggle="modal" data-bs-target="#copyQuestions" @else disabled @endif
                title="{{ $quiz->soal->isEmpty() ? 'Quiz ini belum memiliki soal' : ($destinationQuizzes->isEmpty() ? 'Belum ada quiz tujuan yang belum dikerjakan' : 'Salin soal ke quiz lain') }}">
                <i class="bx bx-copy-alt me-1"></i>Salin ke Quiz Lain
            </button>
            <a href="{{ route('dosen.lms.quiz.hasil', $quiz) }}" class="btn btn-sm btn-primary"><i class="bx bx-bar-chart me-1"></i>Hasil & Penilaian</a>
        </div>
    </div>
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{ session('error') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between gap-3">
            <div><span class="badge bg-label-primary mb-2">KELOLA QUIZ</span><h3 class="fw-bold mb-1">{{ $quiz->judul }}</h3><p class="text-muted mb-0">{{ $quiz->jadwal?->kurikulum?->mataKuliah?->nama }} &bull; {{ $quiz->soal->count() }} soal</p></div>
            <button class="btn btn-outline-primary align-self-center" data-bs-toggle="modal" data-bs-target="#editQuiz"><i class="bx bx-cog me-1"></i>Pengaturan</button>
        </div>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bx bx-shuffle me-2"></i>
        <strong>Urutan soal otomatis diacak per mahasiswa.</strong>
        Urutan akan tetap sama saat mahasiswa memuat ulang halaman dan soal terkunci setelah ujian mulai dikerjakan.
    </div>

    @if($quizLocked)
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-start gap-2">
            <i class="bx bx-lock-alt fs-4"></i>
            <div>
                <strong>Soal quiz sudah dikunci.</strong>
                <div>Quiz telah mulai dikerjakan oleh {{ $quiz->attempts_count }} mahasiswa. Soal tidak dapat ditambah, diubah, atau dihapus agar isi ujian dan hasil mahasiswa tetap konsisten.</div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm sticky-xl-top" style="top:1rem">
                <div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-plus-circle text-primary me-2"></i>Tambah Soal</h5></div>
                <div class="card-body">
                    <form action="{{ route('dosen.lms.quiz.soal.store', $quiz) }}" method="POST" class="question-form">@csrf
                        <fieldset @disabled($quizLocked)>
                        <div class="mb-3"><label class="form-label">Tipe</label><select name="tipe" class="form-select question-type"><option value="pilihan_ganda">Pilihan Ganda</option><option value="essay">Essay</option></select></div>
                        <div class="mb-3"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="4" required></textarea></div>
                        <div class="pg-fields">
                            @foreach(['A','B','C','D','E'] as $i => $label)
                                <div class="input-group mb-2"><span class="input-group-text">{{ $label }}</span><input name="opsi[]" class="form-control" {{ $i < 2 ? 'required' : '' }}></div>
                            @endforeach
                            <div class="mb-3"><label class="form-label">Kunci Jawaban</label><select name="kunci_jawaban" class="form-select"><option value="0">A</option><option value="1">B</option><option value="2">C</option><option value="3">D</option><option value="4">E</option></select></div>
                        </div>
                        <div class="row g-2 mb-3"><div class="col-6"><label class="form-label">Bobot</label><input type="number" step=".01" min=".01" name="bobot" value="10" class="form-control" required></div><div class="col-6"><label class="form-label">Urutan</label><input type="number" min="1" name="urutan" value="{{ $quiz->soal->count()+1 }}" class="form-control"></div></div>
                        <button class="btn btn-primary w-100"><i class="bx bx-plus me-1"></i>Tambahkan Soal</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            @forelse($quiz->soal as $soal)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between gap-3">
                            <div class="d-flex gap-3"><span class="avatar-initial rounded-circle bg-label-primary question-number">{{ $loop->iteration }}</span><div><div class="d-flex gap-2 mb-2"><span class="badge bg-label-{{ $soal->tipe === 'essay' ? 'warning' : 'info' }}">{{ $soal->tipe === 'essay' ? 'Essay' : 'Pilihan Ganda' }}</span><span class="badge bg-label-secondary">Bobot {{ $soal->bobot }}</span></div><h6 class="fw-bold mb-2">{!! nl2br(e($soal->pertanyaan)) !!}</h6></div></div>
                            <div class="d-flex gap-1"><button class="btn btn-sm btn-icon btn-label-warning" @if(!$quizLocked) data-bs-toggle="modal" data-bs-target="#editSoal{{ $soal->soal_id }}" @else disabled title="Soal terkunci karena quiz sudah dikerjakan" @endif><i class="bx {{ $quizLocked ? 'bx-lock-alt' : 'bx-edit' }}"></i></button><form method="POST" action="{{ route('dosen.lms.quiz.soal.destroy', $soal) }}" onsubmit="return confirm('Hapus soal ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-label-danger" @disabled($quizLocked) title="{{ $quizLocked ? 'Soal terkunci karena quiz sudah dikerjakan' : 'Hapus soal' }}"><i class="bx bx-trash"></i></button></form></div>
                        </div>
                        @if($soal->tipe === 'pilihan_ganda')
                            <div class="row g-2 mt-2">@foreach($soal->opsi ?? [] as $i => $opsi)<div class="col-md-6"><div class="border rounded p-2 {{ (string)$i === (string)$soal->kunci_jawaban ? 'border-success bg-label-success' : '' }}"><strong>{{ chr(65+$i) }}.</strong> {{ $opsi }} @if((string)$i === (string)$soal->kunci_jawaban)<i class="bx bx-check float-end"></i>@endif</div></div>@endforeach</div>
                        @endif
                    </div>
                </div>
                <div class="modal fade" id="editSoal{{ $soal->soal_id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form action="{{ route('dosen.lms.quiz.soal.update', $soal) }}" method="POST" class="question-form">@csrf @method('PUT')
                    <div class="modal-header"><h5>Edit Soal</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
                        <div class="row g-3"><div class="col-md-4"><label class="form-label">Tipe</label><select name="tipe" class="form-select question-type"><option value="pilihan_ganda" {{ $soal->tipe==='pilihan_ganda'?'selected':'' }}>Pilihan Ganda</option><option value="essay" {{ $soal->tipe==='essay'?'selected':'' }}>Essay</option></select></div><div class="col-md-4"><label class="form-label">Bobot</label><input type="number" step=".01" name="bobot" value="{{ $soal->bobot }}" class="form-control" required></div><div class="col-md-4"><label class="form-label">Urutan</label><input type="number" name="urutan" value="{{ $soal->urutan }}" class="form-control"></div><div class="col-12"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="3" required>{{ $soal->pertanyaan }}</textarea></div></div>
                        <div class="pg-fields mt-3">@foreach(['A','B','C','D','E'] as $i=>$label)<div class="input-group mb-2"><span class="input-group-text">{{ $label }}</span><input name="opsi[]" value="{{ $soal->opsi[$i] ?? '' }}" class="form-control"></div>@endforeach<div><label class="form-label">Kunci</label><select name="kunci_jawaban" class="form-select">@foreach(['A','B','C','D','E'] as $i=>$label)<option value="{{ $i }}" {{ (string)$soal->kunci_jawaban===(string)$i?'selected':'' }}>{{ $label }}</option>@endforeach</select></div></div>
                    </div><div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div>
                </form></div></div></div>
            @empty
                <div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bx bx-list-plus" style="font-size:4rem"></i><h5>Belum ada soal</h5></div></div>
            @endforelse
        </div>
    </div>
</div>

<div class="modal fade" id="editQuiz" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form action="{{ route('dosen.lms.quiz.update', $quiz) }}" method="POST">@csrf @method('PUT')
    <div class="modal-header"><h5>Pengaturan Quiz</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3">
        <div class="col-12"><label class="form-label">Judul</label><input name="judul" value="{{ $quiz->judul }}" class="form-control" required></div><div class="col-12"><label class="form-label">Deskripsi</label><textarea name="deskripsi" class="form-control">{{ $quiz->deskripsi }}</textarea></div>
        <div class="col-md-4"><label class="form-label">Mulai</label><input type="datetime-local" name="mulai_at" value="{{ $quiz->mulai_at?->format('Y-m-d\TH:i') }}" class="form-control"></div><div class="col-md-4"><label class="form-label">Deadline</label><input type="datetime-local" name="deadline" value="{{ $quiz->deadline?->format('Y-m-d\TH:i') }}" class="form-control"></div><div class="col-md-4"><label class="form-label">Durasi</label><input type="number" name="durasi_menit" value="{{ $quiz->durasi_menit }}" class="form-control"></div>
        <div class="col-12"><div class="form-check"><input type="checkbox" name="aktif" value="1" class="form-check-input" id="activeEdit" {{ $quiz->aktif?'checked':'' }}><label for="activeEdit" class="form-check-label">Aktif dan tampil kepada mahasiswa</label></div></div>
    </div></div><div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div></div>

<div class="modal fade" id="copyQuestions" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('dosen.lms.quiz.soal.copy', $quiz) }}" method="POST" onsubmit="return confirm('Salin seluruh soal dari quiz yang dipilih?')">
                @csrf
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1">Salin Soal ke Quiz Lain</h5>
                        <small class="text-muted">Seluruh soal dari “{{ $quiz->judul }}” akan disalin. Quiz ini tidak akan berubah.</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label fw-semibold">Pilih Quiz Tujuan</label>
                    <select name="destination_quiz_id" class="form-select" required>
                        <option value="">-- Pilih quiz tujuan yang belum dikerjakan --</option>
                        @foreach($destinationQuizzes as $destinationQuiz)
                            @php
                                $destinationCourse = $destinationQuiz->jadwal?->kurikulum?->mataKuliah;
                                $destinationClass = strtolower((string) $destinationQuiz->jadwal?->jenis_kelas) === 'karyawan' ? 'Reguler B' : 'Reguler A';
                            @endphp
                            <option value="{{ $destinationQuiz->quiz_id }}" @selected(old('destination_quiz_id') == $destinationQuiz->quiz_id)>
                                {{ $destinationQuiz->judul }} — {{ $destinationCourse?->nama ?? 'Mata kuliah tidak tersedia' }} — {{ $destinationClass }} — {{ $destinationQuiz->aktif ? 'Aktif' : 'Draft' }} — {{ $destinationQuiz->soal_count }} soal saat ini
                            </option>
                        @endforeach
                    </select>
                    <div class="alert alert-info border-0 mt-3 mb-0">
                        <i class="bx bx-info-circle me-1"></i>
                        Quiz lama boleh sudah berjalan. Tipe soal, opsi A–E, kunci jawaban, bobot, dan urutan akan ikut disalin ke quiz tujuan yang belum pernah dikerjakan mahasiswa.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"><i class="bx bx-copy-alt me-1"></i>Salin ke Quiz Tujuan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>.question-number{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;flex:none}</style>
<script>document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.question-form').forEach(f=>{const s=f.querySelector('.question-type'),p=f.querySelector('.pg-fields');const t=()=>{p.style.display=s.value==='pilihan_ganda'?'block':'none';p.querySelectorAll('input,select').forEach(e=>e.disabled=s.value!=='pilihan_ganda')};s.addEventListener('change',t);t()})})</script>
@endsection
