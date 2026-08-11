@extends('layouts.mahasiswa')
@section('title', 'Rincian Nilai LMS - ' . ($jadwal->kurikulum?->mataKuliah?->nama ?? 'Mata Kuliah'))

@section('content')
@php
    $mataKuliah = $jadwal->kurikulum?->mataKuliah;
    $programStudi = $jadwal->kurikulum?->programStudi;
@endphp

<div class="row">
    <div class="col-xxl-12 mt-auto mb-auto order-0">

        {{-- Navigasi Atas --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <a href="{{ route('mahasiswa.lms.gradebook.index') }}" class="btn btn-outline-primary rounded-pill shadow-sm btn-sm px-3">
                <i class="bx bx-arrow-back me-1"></i> Semua Nilai
            </a>
            <a href="{{ route('mahasiswa.lms.show', $jadwal) }}" class="btn btn-primary rounded-pill shadow-sm btn-sm px-3">
                <i class="bx bx-book-reader me-1"></i> Buka Kelas
            </a>
        </div>

        {{-- Hero Header Rekap Nilai --}}
        <div class="card border-0 shadow-sm mb-4 overflow-hidden gradebook-hero" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
            <div class="card-body p-4 text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <span class="badge bg-white text-primary rounded-pill px-3 py-1 mb-2 fw-semibold">
                            <i class="bx bx-award me-1"></i>REKAP NILAI MATAKULIAH
                        </span>
                        <h3 class="text-white fw-bold mt-1 mb-1">{{ $mataKuliah?->nama ?? '-' }}</h3>
                        <p class="text-white-50 mb-4">
                            <i class="bx bx-code-alt me-1"></i>{{ $mataKuliah?->matakuliah_id ?? '-' }} &bull; <i class="bx bx-buildings me-1"></i>{{ $programStudi?->nama ?? '-' }}
                        </p>

                        <div class="row g-3">
                            <div class="col-6 col-sm-4">
                                <div class="grade-stat border border-light border-opacity-25 rounded-3 p-3 text-center">
                                    <small class="d-block text-white-50 mb-1">Nilai Sementara</small>
                                    <strong class="fs-4 text-white">{{ $persentase }}%</strong>
                                </div>
                            </div>
                            <div class="col-6 col-sm-4">
                                <div class="grade-stat border border-light border-opacity-25 rounded-3 p-3 text-center">
                                    <small class="d-block text-white-50 mb-1">Total Poin</small>
                                    <strong class="fs-4 text-white">{{ number_format($nilaiDiperoleh, 0) }} / {{ number_format($totalMaksimal, 0) }}</strong>
                                </div>
                            </div>
                            <div class="col-12 col-sm-4">
                                <div class="grade-stat border border-light border-opacity-25 rounded-3 p-3 text-center">
                                    <small class="d-block text-white-50 mb-1">Komponen Dinilai</small>
                                    <strong class="fs-4 text-white">{{ $jumlahDinilai }} / {{ $tugasList->count() + $quizList->count() }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 text-center d-none d-md-block">
                        <i class="bx bx-bar-chart-alt-2 text-white" style="font-size: 8rem; opacity: 0.25;"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- Alert Info --}}
        <div class="alert alert-primary border-0 shadow-sm rounded-3 d-flex align-items-center mb-4" role="alert">
            <i class="bx bx-info-circle fs-4 me-2"></i>
            <div>
                Nilai ini bersifat <strong>baca-saja</strong>. Tugas atau quiz yang belum dikumpulkan / belum dinilai dihitung 0 pada persentase sementara.
            </div>
        </div>

        {{-- Tabel Rincian Nilai --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold text-dark mb-0"><i class="bx bx-list-check text-primary me-2"></i>Rincian Nilai Tugas & Quiz</h5>
            </div>
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light border-bottom">
                        <tr>
                            <th>Komponen</th>
                            <th class="text-center">Pertemuan</th>
                            <th class="text-center">Deadline</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Nilai</th>
                            <th>Feedback Dosen</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- LOOPING TUGAS --}}
                        @forelse($tugasList as $tugas)
                            @php($pengumpulan = $tugas->pengumpulan->first())
                            <tr class="border-bottom">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-initial rounded-circle bg-label-warning p-2 me-2">
                                            <i class="bx bx-task fs-5"></i>
                                        </span>
                                        <strong class="text-dark">{{ $tugas->judul }}</strong>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge bg-label-secondary rounded-pill">Ke-{{ $nomorPertemuan->get($tugas->pertemuan_id, '-') }}</span></td>
                                <td class="text-center"><small class="text-muted"><i class="bx bx-time-five me-1"></i>{{ $tugas->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small></td>
                                <td class="text-center">
                                    @if(!$pengumpulan)
                                        <span class="badge bg-label-danger rounded-pill px-3"><i class="bx bx-x me-1"></i>Belum Dikumpulkan</span>
                                    @elseif($pengumpulan->nilai === null)
                                        <span class="badge bg-label-warning rounded-pill px-3"><i class="bx bx-time me-1"></i>Menunggu Penilaian</span>
                                    @else
                                        <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-check me-1"></i>Sudah Dinilai</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($pengumpulan && $pengumpulan->nilai !== null)
                                        <span class="badge bg-label-primary rounded-pill px-3 fs-6">{{ $pengumpulan->nilai }} / {{ $tugas->nilai_maksimal }}</span>
                                    @else
                                        <span class="text-muted">- / {{ $tugas->nilai_maksimal }}</span>
                                    @endif
                                </td>
                                <td class="text-wrap style-feedback">{{ $pengumpulan?->feedback ?: '-' }}</td>
                            </tr>
                        @empty
                            @if($quizList->isEmpty())
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="bx bx-folder-open fs-1 d-block mb-2"></i>
                                        Belum ada tugas atau quiz pada mata kuliah ini.
                                    </td>
                                </tr>
                            @endif
                        @endforelse

                        {{-- LOOPING QUIZ --}}
                        @foreach($quizList as $quiz)
                            @php($attemptQuiz = $quiz->attempts->first())
                            <tr class="border-bottom">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="avatar-initial rounded-circle bg-label-primary p-2 me-2">
                                            <i class="bx bx-help-circle fs-5"></i>
                                        </span>
                                        <div>
                                            <span class="badge bg-label-primary rounded-pill me-1" style="font-size: 0.7rem;">QUIZ</span>
                                            <strong class="text-dark">{{ $quiz->judul }}</strong>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center"><span class="badge bg-label-secondary rounded-pill">Ke-{{ $nomorPertemuan->get($quiz->pertemuan_id, '-') }}</span></td>
                                <td class="text-center"><small class="text-muted"><i class="bx bx-time-five me-1"></i>{{ $quiz->deadline?->translatedFormat('d M Y H:i') ?? '-' }}</small></td>
                                <td class="text-center">
                                    @if($attemptQuiz?->status === 'graded')
                                        <span class="badge bg-label-success rounded-pill px-3"><i class="bx bx-check me-1"></i>Sudah Dinilai</span>
                                    @elseif($attemptQuiz?->status === 'submitted')
                                        <span class="badge bg-label-warning rounded-pill px-3"><i class="bx bx-time me-1"></i>Menunggu Penilaian</span>
                                    @else
                                        <span class="badge bg-label-secondary rounded-pill px-3"><i class="bx bx-minus me-1"></i>Belum Dikerjakan</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($attemptQuiz?->status === 'graded')
                                        <span class="badge bg-label-primary rounded-pill px-3 fs-6">{{ $attemptQuiz->nilai_total }} / {{ number_format($quiz->nilai_maksimal_gradebook, 0) }}</span>
                                    @else
                                        <span class="text-muted">- / {{ number_format($quiz->nilai_maksimal_gradebook, 0) }}</span>
                                    @endif
                                </td>
                                <td class="text-wrap style-feedback">{{ $attemptQuiz?->feedback ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<style>
    .gradebook-hero {
        border-radius: .9rem;
    }
    .grade-stat {
        background: rgba(255, 255, 255, 0.12);
        backdrop-filter: blur(5px);
    }
    .style-feedback {
        max-width: 250px;
        font-size: 0.85rem;
    }
</style>
@endsection