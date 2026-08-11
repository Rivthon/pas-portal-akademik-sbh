@extends('layouts.master')
@section('title', 'Detail Penilaian Dosen')
@section('content')

    <div class="card shadow-sm mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h5 class="card-title text-primary mb-1 fw-bold">Detail Evaluasi Dosen Mengajar</h5>
                <p class="text-muted mb-0">Laporan performa dan analisa presisi hasil evaluasi EDOM mahasiswa.</p>
            </div>
            <div>
                <a href="{{ route('admin.penilaian.index', ['ta_id' => request('ta_id'), 'jurusan_id' => request('jurusan_id')]) }}"
                    class="btn btn-secondary me-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <form action="{{ route('admin.penilaian.cetak-pdf') }}" method="POST" target="_blank" class="d-inline">
                    @csrf
                    <input type="hidden" name="ta_id" value="{{ $ta_id }}">
                    <input type="hidden" name="jurusan_id" value="{{ $jurusan_id }}">
                    <input type="hidden" name="kurikulum_id" value="{{ $kurikulum_id }}">
                    <input type="hidden" name="dosen_id" value="{{ $dosen_id }}">
                    <input type="hidden" name="jenis_dosen" value="{{ $jenis_dosen ?? '' }}">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Cetak PDF
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm h-100 overflow-hidden">
                {{-- Gradient Banner --}}
                <div style="background: linear-gradient(135deg, #696cff 0%, #8592ff 100%); height: 80px;"></div>
                <div class="card-body text-center" style="margin-top: -50px;">
                    {{-- Avatar --}}
                    <div class="d-flex justify-content-center mb-3">
                        @if($dosen->avatar)
                            <img src="{{ Storage::url($dosen->avatar) }}" class="rounded-circle border border-4 border-white shadow-sm"
                                style="width: 100px; height: 100px; object-fit: cover; background: #fff;" alt="Dosen"
                                onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'">
                        @else
                            <div class="rounded-circle bg-white border border-4 border-white shadow-sm d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; font-size: 2rem; color: #696cff; background: #f0f1ff !important;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-1">{{ $dosen->nama }}</h5>
                    <p class="text-muted small mb-3">NIDN: {{ $dosen->nidn ?? '-' }}</p>
                    <div class="border-top pt-3 text-start px-2">
                        <div class="d-flex align-items-start mb-2">
                            <i class="bx bx-book-open text-primary me-2 mt-1"></i>
                            <div><small class="text-muted d-block">Mata Kuliah</small><strong>{{ $kurikulum->mataKuliah?->nama ?? '-' }}</strong></div>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <i class="bx bx-buildings text-primary me-2 mt-1"></i>
                            <div><small class="text-muted d-block">Program Studi</small><strong>{{ $kurikulum->programStudi?->nama ?? '-' }}</strong></div>
                        </div>
                        <div class="d-flex align-items-start mb-2">
                            <i class="bx bx-calendar text-primary me-2 mt-1"></i>
                            <div><small class="text-muted d-block">Semester</small><strong>{{ $kurikulum->mataKuliah?->smt ?? '-' }}</strong></div>
                        </div>
                        <div class="d-flex align-items-start mb-0">
                            <i class="bx bx-chalkboard text-primary me-2 mt-1"></i>
                            <div><small class="text-muted d-block">Metode</small><span class="badge bg-label-primary rounded-pill px-3">{{ ucfirst($jenis_dosen) }}</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Ringkasan Nilai Per Aspek</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">No</th>
                                    <th>Aspek Penilaian</th>
                                    <th class="text-center">Rata-rata Nilai</th>
                                    <th>Kriteria</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($rataRataNilai) && $rataRataNilai->count() > 0)
                                    @php $no = 1; @endphp
                                    @foreach ($rataRataNilai as $evaluasiId => $nilai)
                                        <tr>
                                            <td class="ps-4">{{ $no++ }}</td>
                                            <td>{{ $penilaian->firstWhere('evaluasi_id', $evaluasiId)->evaluasi->nama ?? '-' }}</td>
                                            <td class="text-center fw-bold">{{ number_format($nilai, 2) }}</td>
                                            <td>
                                                <span
                                                    class="badge {{ $nilai >= 3.5 ? 'bg-success' : ($nilai >= 2.5 ? 'bg-warning text-dark' : 'bg-danger') }}">
                                                    {{ $kriteria($nilai) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada penilaian</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(isset($sarans) && $sarans->isNotEmpty())
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">List Saran Mahasiswa</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                            @foreach ($sarans as $saran)
                                <li class="list-group-item p-3">
                                    <div class="d-flex align-items-start">
                                        <div class="ms-2">
                                            <p class="mb-1">"{{ $saran->saran }}"</p>
                                            <small class="text-muted">&mdash; {{ $saran->mahasiswa->nama ?? 'Anonim' }}</small>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="mb-0 fw-bold">Daftar Mahasiswa (Mengisi EDOM)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-hover mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="ps-4">No</th>
                                        <th>NIM</th>
                                        <th>Nama Mahasiswa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $nomhs = 1; @endphp
                                    @foreach ($sarans->unique('mahasiswa_id') as $saran)
                                        <tr>
                                            <td class="ps-4">{{ $nomhs++ }}</td>
                                            <td>{{ $saran->mahasiswa->nim ?? '-' }}</td>
                                            <td>{{ $saran->mahasiswa->nama ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection