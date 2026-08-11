@extends('layouts.dosen')

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
    $programStudi = $jadwal->kurikulum?->programStudi;
@endphp
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <a href="{{ route('dosen.lms.index') }}" class="btn btn-sm btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i>Kembali ke LMS
        </a>
        <div class="d-flex flex-wrap gap-2">
            <form action="{{ route('dosen.lms.gradebook.sync-khs', $jadwal) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-sm btn-primary"
                        onclick="return confirm('Sinkronkan persentase Gradebook ke komponen Tugas pada nilai KHS?')">
                    <i class="bx bx-sync me-1"></i>Sinkronkan ke KHS
                </button>
            </form>
            <a href="{{ route('dosen.lms.gradebook.excel', $jadwal) }}" class="btn btn-sm btn-success"><i class="bx bx-spreadsheet me-1"></i>Excel</a>
            <a href="{{ route('dosen.lms.gradebook.pdf', $jadwal) }}" class="btn btn-sm btn-danger"><i class="bx bxs-file-pdf me-1"></i>PDF</a>
            <a href="{{ route('dosen.lms.kelola', $jadwal) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-cog me-1"></i>Kelola Kelas</a>
        </div>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error')) <div class="alert alert-danger">{{ session('error') }}</div> @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Nilai gagal disimpan.</strong>
            <ul class="mb-0 mt-1">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm overflow-hidden mb-4 gradebook-hero">
        <div class="card-body p-4 text-white">
            <span class="badge bg-white text-primary mb-2">GRADEBOOK DOSEN</span>
            <h3 class="text-white fw-bold mb-1">{{ $mataKuliah?->nama ?? '-' }}</h3>
            <p class="text-white-50 mb-3">
                {{ $mataKuliah?->matakuliah_id ?? '-' }} &bull; {{ $programStudi?->nama ?? '-' }}
                &bull; {{ strtoupper($jadwal->jenis_kelas ?? '-') }}
            </p>
            <p class="mb-0 text-white-50">Nilai kosong atau tugas yang belum dikumpulkan dihitung 0 pada persentase sementara.</p>
            <p class="mb-0 mt-1 text-white-50"><i class="bx bx-link-alt me-1"></i>Persentase Gradebook otomatis menjadi nilai komponen Tugas KHS; bobot tugas diterapkan saat menghitung nilai akhir.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        @foreach([
            ['Mahasiswa', $rekap->count(), 'primary', 'group'],
            ['Komponen', $tugasList->count() + $quizList->count(), 'warning', 'task'],
            ['Sudah Dinilai', $totalSudahDinilai, 'success', 'check-circle'],
            ['Rata-rata Kelas', $rataRata.'%', 'info', 'bar-chart-alt-2'],
        ] as [$label, $value, $color, $icon])
            <div class="col-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center">
                        <span class="avatar-initial rounded bg-label-{{ $color }} p-3 me-3"><i class="bx bx-{{ $icon }} fs-3"></i></span>
                        <div><h4 class="fw-bold mb-0">{{ $value }}</h4><small class="text-muted">{{ $label }}</small></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold mb-1"><i class="bx bx-table text-primary me-2"></i>Rekap Nilai Mahasiswa</h5>
                <small class="text-muted">Nilai dapat disimpan langsung pada setiap kolom tugas yang sudah dikumpulkan.</small>
            </div>
            <span class="badge bg-label-primary">Total Maksimal {{ number_format($totalMaksimal, 0) }}</span>
        </div>

        @if($tugasList->isEmpty() && $quizList->isEmpty())
            <div class="card-body text-center py-5">
                <i class="bx bx-task-x text-muted mb-2" style="font-size:4rem"></i>
                <h5>Belum ada komponen nilai</h5>
                <p class="text-muted mb-3">Buat tugas atau quiz terlebih dahulu agar Gradebook dapat digunakan.</p>
                <a href="{{ route('dosen.lms.kelola', $jadwal) }}" class="btn btn-primary">Kelola Kelas</a>
            </div>
        @else
            <div class="table-responsive gradebook-table-wrap">
                <table class="table table-hover align-middle mb-0 gradebook-table">
                    <thead class="table-light">
                        <tr>
                            <th class="sticky-col sticky-no text-center">No</th>
                            <th class="sticky-col sticky-student">Mahasiswa</th>
                            @foreach($tugasList as $tugas)
                                <th class="text-center task-column">
                                    <span class="d-block text-truncate" title="{{ $tugas->judul }}">{{ $tugas->judul }}</span>
                                    <small class="text-muted">Maks. {{ $tugas->nilai_maksimal }}</small>
                                </th>
                            @endforeach
                            @foreach($quizList as $quiz)
                                <th class="text-center task-column">
                                    <span class="badge bg-label-primary mb-1">Quiz</span>
                                    <span class="d-block text-truncate" title="{{ $quiz->judul }}">{{ $quiz->judul }}</span>
                                    <small class="text-muted">Maks. {{ number_format($quiz->nilai_maksimal_gradebook, 0) }}</small>
                                </th>
                            @endforeach
                            <th class="text-center summary-column">Total</th>
                            <th class="text-center summary-column">Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rekap as $data)
                            <tr>
                                <td class="sticky-col sticky-no text-center">{{ $loop->iteration }}</td>
                                <td class="sticky-col sticky-student">
                                    <strong class="d-block">{{ $data->mahasiswa->nama ?? '-' }}</strong>
                                    <small class="text-muted">{{ $data->mahasiswa->nim ?? '-' }}</small>
                                </td>
                                @foreach($tugasList as $tugas)
                                    @php($pengumpulan = $data->nilai_per_tugas[$tugas->tugas_id] ?? null)
                                    <td class="text-center">
                                        @if($pengumpulan)
                                            <form action="{{ route('dosen.lms.pengumpulan.nilai', $pengumpulan) }}" method="POST"
                                                  class="d-flex justify-content-center align-items-center gap-1">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="nilai" value="{{ $pengumpulan->nilai }}"
                                                       min="0" max="{{ $tugas->nilai_maksimal }}" step="1"
                                                       class="form-control form-control-sm grade-input"
                                                       aria-label="Nilai {{ $tugas->judul }} untuk {{ $data->mahasiswa->nama }}">
                                                <button class="btn btn-sm btn-icon btn-label-primary" title="Simpan nilai">
                                                    <i class="bx bx-save"></i>
                                                </button>
                                            </form>
                                            @if($pengumpulan->feedback)
                                                <small class="text-success d-block mt-1"><i class="bx bx-message-detail"></i> Ada feedback</small>
                                            @endif
                                        @else
                                            <span class="badge bg-label-danger">Belum kumpul</span>
                                        @endif
                                    </td>
                                @endforeach
                                @foreach($quizList as $quiz)
                                    @php($attemptQuiz = $data->nilai_per_quiz[$quiz->quiz_id] ?? null)
                                    <td class="text-center">
                                        @if($attemptQuiz?->status === 'graded')
                                            <span class="badge bg-label-primary fs-6">{{ number_format($attemptQuiz->nilai_total, 0) }}</span>
                                        @elseif($attemptQuiz?->status === 'submitted')
                                            <span class="badge bg-label-warning">Belum dinilai</span>
                                        @else
                                            <span class="badge bg-label-secondary">Belum</span>
                                        @endif
                                    </td>
                                @endforeach
                                <td class="text-center fw-bold">{{ number_format($data->nilai_diperoleh, 0) }} / {{ number_format($totalMaksimal, 0) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-label-{{ $data->persentase >= 75 ? 'success' : ($data->persentase >= 60 ? 'warning' : 'danger') }} fs-6">
                                        {{ $data->persentase }}%
                                    </span>
                                    <small class="text-muted d-block mt-1">{{ $data->jumlah_dinilai }}/{{ $tugasList->count() + $quizList->count() }} dinilai</small>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="{{ $tugasList->count() + $quizList->count() + 4 }}" class="text-center text-muted py-5">Belum ada mahasiswa pada kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
<style>
    .gradebook-hero { border-radius:.9rem; background:linear-gradient(135deg,#696cff 0%,#5a5de4 55%,#8592ff 100%); }
    .gradebook-table-wrap { max-height:70vh; }
    .gradebook-table th { white-space:nowrap; vertical-align:middle; }
    .task-column { min-width:175px; max-width:210px; }
    .summary-column { min-width:130px; }
    .grade-input { width:78px; text-align:center; }
    .sticky-col { position:sticky; z-index:2; background:#fff; }
    thead .sticky-col { background:#f5f5f9; z-index:4; }
    .sticky-no { left:0; min-width:55px; }
    .sticky-student { left:55px; min-width:220px; box-shadow:5px 0 8px -8px rgba(67,89,113,.55); }
</style>
@endsection
