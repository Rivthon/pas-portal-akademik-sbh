@extends('layouts.mahasiswa')
@section('title', 'Riwayat Nilai UTS dan UAS')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#566a7f 0%,#696cff 100%)">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25" style="width:54px;height:54px">
                    <i class="bx bx-history fs-2"></i>
                </span>
                <div>
                    <h4 class="text-white mb-1">Riwayat Nilai UTS &amp; UAS</h4>
                    <p class="text-white-50 mb-0">Arsip nilai ujian semester terdahulu milik {{ $mahasiswa->nama }}.</p>
                </div>
            </div>
            <span class="badge bg-white text-primary px-3 py-2">Tanpa Nilai Absolut</span>
        </div>
    </div>
</div>

@if($tahunAjaranOptions->isEmpty())
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bx bx-file-blank text-muted" style="font-size:4rem"></i>
            <h5 class="mt-3">Belum Ada Riwayat Nilai</h5>
            <p class="text-muted mb-0">Nilai UTS atau UAS dari tahun akademik sebelumnya belum tersedia.</p>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('mahasiswa.nilai-ujian.riwayat') }}" class="row g-3 align-items-end">
                <div class="col-md-9">
                    <label for="ta_id" class="form-label fw-semibold">Tahun Akademik Sebelumnya</label>
                    <select name="ta_id" id="ta_id" class="form-select" onchange="this.form.submit()">
                        @foreach($tahunAjaranOptions as $tahunAjaran)
                            <option value="{{ $tahunAjaran->ta_id }}" @selected((int) $selectedTaId === (int) $tahunAjaran->ta_id)>
                                {{ $tahunAjaran->nama }}{{ $tahunAjaran->semester ? ' - '.ucfirst($tahunAjaran->semester) : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button class="btn btn-primary"><i class="bx bx-search-alt me-1"></i>Tampilkan</button>
                </div>
            </form>
            <div class="alert alert-info py-2 mt-3 mb-0">
                <i class="bx bx-info-circle me-1"></i>Halaman ini hanya menampilkan nilai UTS dan UAS. Nilai absolut, huruf mutu, dan IP tidak ditampilkan.
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-1">{{ $selectedTahunAjaran?->nama ?? '-' }} - {{ ucfirst((string) $selectedTahunAjaran?->semester) }}</h5>
                <small class="text-muted">{{ $mahasiswa->programStudi?->nama }} · {{ $nilai->count() }} mata kuliah</small>
            </div>
            <span class="badge bg-label-primary px-3 py-2">Riwayat Ujian</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:70px">No.</th>
                        <th>Kode</th>
                        <th>Mata Kuliah</th>
                        <th class="text-center">SKS</th>
                        <th class="text-center">UTS</th>
                        <th class="text-center">UAS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($nilai as $index => $item)
                        <tr>
                            <td class="text-center">{{ $index + 1 }}</td>
                            <td><span class="badge bg-label-secondary">{{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</span></td>
                            <td class="fw-semibold">{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</td>
                            <td class="text-center">{{ $item->kurikulum?->mataKuliah?->sks ?? '-' }}</td>
                            <td class="text-center"><span class="badge bg-label-primary px-3">{{ filled($item->uts) ? $item->uts : '-' }}</span></td>
                            <td class="text-center"><span class="badge bg-label-info px-3">{{ filled($item->uas) ? $item->uas : '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">Tidak ada nilai UTS atau UAS pada tahun akademik ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif
@endsection
