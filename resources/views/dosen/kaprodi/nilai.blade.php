@extends('layouts.dosen')
@section('title', 'Verifikasi Nilai Kaprodi')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3 0%,#5a72d8 100%)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-25" style="width:56px;height:56px">
                    <i class="bx bx-check-double fs-2"></i>
                </span>
                <div>
                    <h4 class="text-white mb-1">Verifikasi Nilai</h4>
                    <p class="mb-0 text-white-50">Periksa nilai dosen sebelum diterbitkan oleh BAAK.</p>
                </div>
            </div>
            <div class="small">
                <span class="text-white-50">Program studi:</span>
                <strong>{{ $programStudiDipimpin->pluck('nama')->join(', ') ?: 'Belum ditetapkan' }}</strong>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show"><i class="bx bx-check-circle me-1"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('warning'))
    <div class="alert alert-warning"><i class="bx bx-info-circle me-1"></i>{{ session('warning') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger"><i class="bx bx-error-circle me-1"></i>{{ $errors->first() }}</div>
@endif

<form id="bulk-nilai-form" method="POST" action="{{ route('dosen.kaprodi.nilai.bulk-approve') }}">
    @csrf
</form>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
            <form method="GET" action="{{ route('dosen.kaprodi.nilai.index') }}" class="d-flex align-items-end gap-2">
                <div>
                    <label class="form-label small mb-1">Status Pengajuan</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua status</option>
                        <option value="submitted" @selected($status === 'submitted')>Menunggu ACC</option>
                        <option value="approved" @selected($status === 'approved')>Disetujui</option>
                        <option value="revision" @selected($status === 'revision')>Perlu revisi</option>
                    </select>
                </div>
            </form>
            <button type="submit" form="bulk-nilai-form" id="bulk-nilai-button" class="btn btn-success" disabled
                onclick="return confirm('Setujui semua pengajuan nilai yang dipilih? Nilai akan diteruskan ke BAAK.')">
                <i class="bx bx-check-double me-1"></i>ACC Nilai Terpilih
                <span id="bulk-nilai-count" class="badge bg-white text-success ms-1">0</span>
            </button>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4" style="width:44px"><input type="checkbox" class="form-check-input" id="select-all-nilai" title="Pilih semua yang menunggu pada halaman ini"></th>
                    <th>Mata Kuliah</th>
                    <th>Kelas / TA</th>
                    <th>Penginput</th>
                    <th>Diajukan</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $item)
                <tr>
                    <td class="ps-4">
                        @if($item->status === 'submitted')
                            <input type="checkbox" class="form-check-input bulk-nilai-item" name="submission_ids[]"
                                value="{{ $item->id }}" form="bulk-nilai-form">
                        @else
                            <input type="checkbox" class="form-check-input" disabled>
                        @endif
                    </td>
                    <td>
                        <strong>{{ $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-' }}</strong>
                        <small class="d-block text-muted">{{ $item->jadwal?->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}</small>
                    </td>
                    <td>
                        {{ jenis_kelas_label($item->jadwal?->jenis_kelas ?? 'Reguler') }}
                        <small class="d-block text-muted">{{ $item->jadwal?->tahunAjaran?->nama }}</small>
                    </td>
                    <td>{{ $item->submitter?->nama ?? '-' }}</td>
                    <td>{{ optional($item->submitted_at)->format('d/m/Y H:i') ?: '-' }}</td>
                    <td>
                        <span class="badge bg-label-{{ ['submitted'=>'warning','approved'=>'success','revision'=>'danger'][$item->status] ?? 'secondary' }}">
                            {{ ['submitted'=>'Menunggu ACC','approved'=>'Disetujui','revision'=>'Perlu revisi'][$item->status] ?? $item->status }}
                        </span>
                        @if($item->review_note)<small class="d-block text-danger mt-1">{{ $item->review_note }}</small>@endif
                    </td>
                    <td class="text-end pe-4">
                        <div class="d-inline-flex flex-wrap justify-content-end gap-1">
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('dosen.kaprodi.nilai.show', $item) }}"><i class="bx bx-show me-1"></i>Periksa</a>
                            @if($item->status === 'submitted')
                                <form method="POST" action="{{ route('dosen.kaprodi.nilai.approve', $item) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-success" onclick="return confirm('Setujui nilai ini?')"><i class="bx bx-check me-1"></i>ACC</button>
                                </form>
                                <button class="btn btn-sm btn-outline-danger" type="button" data-bs-toggle="collapse" data-bs-target="#revision-{{ $item->id }}">Revisi</button>
                            @endif
                        </div>
                        @if($item->status === 'submitted')
                            <div class="collapse mt-2" id="revision-{{ $item->id }}">
                                <form class="d-flex gap-1" method="POST" action="{{ route('dosen.kaprodi.nilai.revision', $item) }}">
                                    @csrf
                                    <input class="form-control form-control-sm" name="review_note" required maxlength="1000" placeholder="Catatan revisi">
                                    <button class="btn btn-sm btn-danger">Kirim</button>
                                </form>
                            </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-5 text-muted"><i class="bx bx-file-find fs-2 d-block mb-2"></i>Belum ada pengajuan nilai.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($submissions->hasPages())
        <div class="card-footer bg-white d-flex justify-content-center">{{ $submissions->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('select-all-nilai');
    const items = Array.from(document.querySelectorAll('.bulk-nilai-item'));
    const button = document.getElementById('bulk-nilai-button');
    const count = document.getElementById('bulk-nilai-count');

    function updateBulkNilai() {
        const selected = items.filter(item => item.checked).length;
        count.textContent = selected;
        button.disabled = selected === 0;
        selectAll.checked = items.length > 0 && selected === items.length;
        selectAll.indeterminate = selected > 0 && selected < items.length;
    }

    selectAll?.addEventListener('change', function () {
        items.forEach(item => item.checked = selectAll.checked);
        updateBulkNilai();
    });
    items.forEach(item => item.addEventListener('change', updateBulkNilai));
    updateBulkNilai();
});
</script>
@endpush
