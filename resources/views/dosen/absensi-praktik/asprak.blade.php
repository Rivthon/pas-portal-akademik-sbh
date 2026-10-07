@extends('layouts.dosen')
@section('title', 'Kelola Asprak')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="bx bx-group text-primary me-2"></i>Kelola Asprak</h4>
            <p class="text-muted mb-0">{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }} · {{ jenis_kelas_label($jadwal->jenis_kelas) }}</p>
        </div>
        <a href="{{ route('dosen.absensi-praktik.index') }}" class="btn btn-outline-secondary"><i class="bx bx-arrow-back me-1"></i>Kembali</a>
    </div>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    <div class="alert alert-info border-0 shadow-sm">
        <i class="bx bx-info-circle me-2"></i>Pilih mahasiswa yang menjadi Asisten Praktikum untuk mata kuliah ini. Penugasan tidak mengubah KRS, SKS, nilai, atau absensi kuliah mahasiswa.
    </div>

    <form method="POST" action="{{ route('dosen.absensi-praktik.asprak.update', $jadwal) }}">
        @csrf @method('PUT')
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <div class="row g-3 align-items-center">
                    <div class="col-md-7"><h6 class="fw-bold mb-0">Mahasiswa Aktif {{ $jadwal->programStudi?->nama ?? '' }}</h6></div>
                    <div class="col-md-5"><input id="searchAsprak" type="search" class="form-control" placeholder="Cari nama atau NIM..."></div>
                </div>
            </div>
            <div class="table-responsive" style="max-height:560px">
                <table class="table table-hover align-middle mb-0" id="asprakTable">
                    <thead class="table-light sticky-top"><tr><th style="width:60px" class="text-center">Pilih</th><th>Mahasiswa</th><th class="text-center">Semester</th><th>Kelas Mahasiswa</th></tr></thead>
                    <tbody>
                        @forelse($candidates as $candidate)
                            <tr data-search="{{ strtolower($candidate->nim.' '.$candidate->nama) }}">
                                <td class="text-center"><input class="form-check-input" type="checkbox" name="mahasiswa_ids[]" value="{{ $candidate->mahasiswa_id }}" @checked(in_array((int) $candidate->mahasiswa_id, old('mahasiswa_ids', $activeIds), true))></td>
                                <td><strong>{{ $candidate->nama }}</strong><small class="d-block text-muted">{{ $candidate->nim }}</small></td>
                                <td class="text-center"><span class="badge bg-label-primary">{{ $candidate->semester }}</span></td>
                                <td>{{ jenis_kelas_label($candidate->kelas) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-5">Tidak ada mahasiswa aktif pada program studi ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                <small class="text-muted">Asprak yang dilepas tetap memiliki riwayat kehadiran sebelumnya.</small>
                <button class="btn btn-primary px-4"><i class="bx bx-save me-1"></i>Simpan Asprak</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('script')
<script>
document.getElementById('searchAsprak')?.addEventListener('input', function () {
    const keyword = this.value.toLowerCase().trim();
    document.querySelectorAll('#asprakTable tbody tr[data-search]').forEach(function (row) {
        row.classList.toggle('d-none', !row.dataset.search.includes(keyword));
    });
});
</script>
@endpush
