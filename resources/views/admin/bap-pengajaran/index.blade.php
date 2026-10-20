@extends('layouts.master')
@section('title', 'BAP Pengajaran Dosen')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background: linear-gradient(135deg,#0f766e,#0d9488)">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-white text-success mb-2">BAUK</span>
                <h3 class="fw-bold text-white mb-1">BAP Pengajaran Dosen</h3>
                <p class="text-white-50 mb-0">
                    Rekap honor per periode cutoff tanggal 20. Satu sesi hanya dapat dibayar satu kali.
                </p>
            </div>
            <i class="bx bx-clipboard d-none d-md-block" style="font-size: 5rem; opacity: .3"></i>
        </div>
    </div>
</div>

<div class="alert alert-info border-0">
    <i class="bx bx-info-circle me-2"></i>
    Periode terpilih <strong>{{ $period['label'] }}</strong>. Jumlah yang diakui dibatasi maksimal
    <strong>{{ $maksimalPertemuan }} pertemuan per kelas</strong> dalam satu semester.
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tahun Akademik / Semester</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                        <option value="{{ $ta->ta_id }}" @selected((string) $selectedTaId === (string) $ta->ta_id)>
                            {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Periode Honor</label>
                <select name="period" class="form-select">
                    @foreach($periodOptions as $option)
                        <option value="{{ $option['key'] }}" @selected($period['key'] === $option['key'])>
                            {{ $option['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cari Dosen</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Nama, NIDN, atau kode dosen">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<form method="POST" action="{{ route('admin.bap-pengajaran.bulk-download-pdf') }}" id="bulk-paid-form"
    >
    @csrf
    <input type="hidden" name="ta_id" value="{{ $selectedTaId }}">
    <input type="hidden" name="period" value="{{ $period['key'] }}">
    <input type="hidden" name="search" value="{{ request('search') }}">
    <input type="hidden" name="page" value="{{ request('page') }}">
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold mb-0"><i class="bx bx-user-voice text-primary me-2"></i>Daftar Pengajaran Dosen</h5>
            <small class="text-muted">Centang dosen untuk konfirmasi pembayaran atau download BAP massal.</small>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="submit" class="btn btn-danger" id="bulk-download-button" disabled>
                <i class="bx bxs-file-archive me-1"></i>Download BAP Bulk
                <span class="badge bg-white text-danger ms-1 bulk-selected-count">0</span>
            </button>
            @can('bap-pengajaran-pay')
                <button type="submit" class="btn btn-success" id="bulk-paid-button" disabled
                    formaction="{{ route('admin.bap-pengajaran.bulk-mark-paid') }}"
                    onclick="return confirm('Tandai seluruh sesi yang belum dibayar dari dosen terpilih sebagai PAID?')">
                    <i class="bx bx-check-double me-1"></i>Konfirmasi Paid Terpilih
                    <span class="badge bg-white text-success ms-1 bulk-selected-count">0</span>
                </button>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 45px">
                        <input class="form-check-input" type="checkbox" id="select-all-dosen" title="Pilih semua dosen pada halaman ini">
                    </th>
                    <th class="text-center">No</th>
                    <th>Dosen</th>
                    <th class="text-center">Kelas Sudah Berjalan</th>
                    <th class="text-center">Teori</th>
                    <th class="text-center">Praktik</th>
                    <th class="text-center">Jumlah Mengajar</th>
                    <th class="text-center">Pembayaran</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosenList as $dosen)
                    <tr>
                        <td class="text-center">
                            <input class="form-check-input dosen-bulk-checkbox" type="checkbox"
                                name="dosen_ids[]" value="{{ $dosen->dosen_id }}"
                                @disabled($dosen->jumlah_mengajar <= 0)
                                aria-label="Pilih {{ $dosen->nama }}">
                        </td>
                        <td class="text-center">{{ $dosenList->firstItem() + $loop->index }}</td>
                        <td>
                            <strong>{{ $dosen->nama }}</strong>
                            <small class="d-block text-muted">
                                NIDN: {{ $dosen->nidn ?: '-' }} &bull; {{ $dosen->kd_dosen ?: '-' }}
                            </small>
                        </td>
                        <td class="text-center"><span class="badge bg-label-secondary">{{ $dosen->jumlah_kelas }}</span></td>
                        <td class="text-center"><span class="badge bg-label-primary">{{ $dosen->jumlah_teori }} kali</span></td>
                        <td class="text-center"><span class="badge bg-label-warning">{{ $dosen->jumlah_praktik }} kali</span></td>
                        <td class="text-center">
                            <span class="badge bg-label-success fs-6">{{ $dosen->jumlah_mengajar }} kali</span>
                        </td>
                        <td class="text-center">
                            @if($dosen->jumlah_mengajar === 0)
                                <span class="badge bg-label-secondary">Tidak ada sesi</span>
                            @elseif($dosen->belum_dibayar === 0)
                                <span class="badge bg-label-success">PAID ({{ $dosen->dibayar }})</span>
                            @elseif($dosen->dibayar > 0)
                                <span class="badge bg-label-warning">Sebagian</span>
                                <small class="d-block text-muted">{{ $dosen->belum_dibayar }} belum dibayar</small>
                            @else
                                <span class="badge bg-label-danger">UNPAID</span>
                                <small class="d-block text-muted">{{ $dosen->belum_dibayar }} sesi</small>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                <a href="{{ route('admin.bap-pengajaran.show', ['dosen' => $dosen, 'ta_id' => $selectedTaId, 'period' => $period['key']]) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="bx bx-show me-1"></i>Detail
                                </a>
                                <a href="{{ route('admin.bap-pengajaran.pdf', ['dosen' => $dosen, 'ta_id' => $selectedTaId, 'period' => $period['key']]) }}"
                                    class="btn btn-sm btn-danger" title="Download BAP PDF">
                                    <i class="bx bxs-file-pdf me-1"></i>PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bx bx-folder-open fs-1"></i>
                            <h6 class="mt-2">Tidak ada dosen pada semester ini</h6>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dosenList->hasPages())
        <div class="card-footer bg-white">{{ $dosenList->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
</form>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all-dosen');
        const checkboxes = Array.from(document.querySelectorAll('.dosen-bulk-checkbox:not(:disabled)'));
        const buttons = Array.from(document.querySelectorAll('#bulk-paid-button, #bulk-download-button'));
        const countBadges = Array.from(document.querySelectorAll('.bulk-selected-count'));

        if (!selectAll || buttons.length === 0 || countBadges.length === 0) return;

        const refresh = () => {
            const checked = checkboxes.filter((checkbox) => checkbox.checked).length;
            countBadges.forEach((badge) => badge.textContent = checked);
            buttons.forEach((button) => button.disabled = checked === 0);
            selectAll.checked = checkboxes.length > 0 && checked === checkboxes.length;
            selectAll.indeterminate = checked > 0 && checked < checkboxes.length;
        };

        selectAll.addEventListener('change', function () {
            checkboxes.forEach((checkbox) => checkbox.checked = selectAll.checked);
            refresh();
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', refresh));
        refresh();
    });
</script>
@endpush
