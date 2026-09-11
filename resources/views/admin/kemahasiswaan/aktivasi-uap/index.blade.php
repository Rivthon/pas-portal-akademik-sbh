@extends('layouts.master')

@push('head')
<style>
    .uap-page .hero-card { border: 0; border-radius: 16px; background: linear-gradient(135deg, #696cff, #4f8fe8); color: #fff; overflow: hidden; }
    .uap-page .hero-icon { width: 58px; height: 58px; border-radius: 16px; display: grid; place-items: center; background: rgba(255,255,255,.18); font-size: 1.8rem; }
    .uap-page .summary-card, .uap-page .filter-card, .uap-page .table-card { border: 1px solid rgba(67,89,113,.08); border-radius: 14px; box-shadow: 0 2px 12px rgba(67,89,113,.06); }
    .uap-page .summary-icon { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; font-size: 1.25rem; }
    .uap-page .filter-label { color: #8592a3; font-size: .72rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .uap-page .table thead th { padding: .9rem 1rem; background: #f5f5f9; border: 0; color: #697a8d; font-size: .72rem; letter-spacing: .05em; text-transform: uppercase; white-space: nowrap; }
    .uap-page .table tbody td { padding: .9rem 1rem; vertical-align: middle; }
    .uap-page .student-avatar { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
    .uap-page .status-text { min-width: 58px; font-size: .67rem; font-weight: 700; }
    .uap-page .form-check-input { cursor: pointer; width: 2.35rem; height: 1.2rem; }
    .uap-page .pagination .page-link { border-radius: 8px; margin: 0 2px; }
</style>
@endpush

@section('content')
<div class="container-xxl flex-grow-1 container-p-y uap-page">
    <div class="card hero-card mb-4">
        <div class="card-body p-4 d-flex align-items-center justify-content-between gap-3">
            <div>
                <div class="small text-white-50 mb-2">Administrasi Akademik</div>
                <h4 class="text-white mb-2">Aktivasi Ujian Akhir Program (UAP)</h4>
                <p class="mb-0 text-white-50">Aktivasi ini khusus mahasiswa aktif Program Studi Kebidanan semester 6.</p>
            </div>
            <div class="hero-icon flex-shrink-0"><i class="bx bx-medal"></i></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card summary-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="summary-icon bg-label-primary"><i class="bx bx-group"></i></div>
                <div><div class="text-muted small">Kebidanan Semester 6</div><h4 class="mb-0">{{ number_format($total) }}</h4></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="summary-icon bg-label-success"><i class="bx bx-check-circle"></i></div>
                <div><div class="text-muted small">UAP Aktif</div><h4 class="mb-0 text-success">{{ number_format($totalAktif) }}</h4></div>
            </div></div>
        </div>
        <div class="col-md-4">
            <div class="card summary-card h-100"><div class="card-body d-flex align-items-center gap-3">
                <div class="summary-icon bg-label-secondary"><i class="bx bx-lock-alt"></i></div>
                <div><div class="text-muted small">Belum Diaktifkan</div><h4 class="mb-0">{{ number_format($totalNonaktif) }}</h4></div>
            </div></div>
        </div>
    </div>

    <div class="card filter-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.aktivasi-uap.index') }}" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="filter-label mb-2" for="search">Cari mahasiswa</label>
                    <div class="input-group"><span class="input-group-text"><i class="bx bx-search"></i></span><input id="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama atau NIM"></div>
                </div>
                <div class="col-md-4">
                    <label class="filter-label mb-2" for="kelas">Kelas</label>
                    <select id="kelas" name="kelas" class="form-select">
                        <option value="">Semua kelas</option>
                        <option value="pagi" @selected(request('kelas') === 'pagi')>Reguler A</option>
                        <option value="karyawan" @selected(request('kelas') === 'karyawan')>Reguler B</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" type="submit"><i class="bx bx-filter-alt me-1"></i>Filter</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.aktivasi-uap.index') }}" title="Reset filter"><i class="bx bx-reset"></i></a>
                </div>
            </form>

            @can('aktivasi-uap-bulk-update')
                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                    <button type="button" class="btn btn-sm btn-success bulk-uap" data-status="1"><i class="bx bx-check-double me-1"></i>Aktifkan Hasil Filter</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary bulk-uap" data-status="0"><i class="bx bx-lock-alt me-1"></i>Nonaktifkan Hasil Filter</button>
                    <span class="small text-muted align-self-center">Aksi massal hanya berlaku untuk Kebidanan semester 6 sesuai kelas dan pencarian di atas.</span>
                </div>
            @endcan
        </div>
    </div>

    <div class="card table-card overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>NIM</th><th>Mahasiswa</th><th>Semester</th><th>Tahun Masuk</th><th>Status UAP</th></tr></thead>
                <tbody>
                    @forelse ($mahasiswa as $item)
                        <tr>
                            <td class="fw-semibold">{{ $item->nim }}</td>
                            <td><div class="d-flex align-items-center gap-3"><img class="student-avatar" src="{{ $item->avatar_url ?? asset('dashboard_assets/assets/img/avatars/1.png') }}" alt="" onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'"><div><div class="fw-semibold">{{ $item->nama }}</div><small class="text-muted">Kebidanan</small></div></div></td>
                            <td><span class="badge bg-label-info">Semester {{ $item->semester }}</span></td>
                            <!-- <td>{{ jenis_kelas_label($item->kelas) }}</td> -->
                            <td>{{ $item->tahun_masuk ?: '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input toggle-uap" type="checkbox" data-id="{{ $item->mahasiswa_id }}" @checked($item->status_uap) @disabled(!auth()->user()->can('aktivasi-uap-update'))>
                                    </div>
                                    <span class="status-text {{ $item->status_uap ? 'text-success' : 'text-muted' }}">{{ $item->status_uap ? 'AKTIF' : 'NONAKTIF' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5"><i class="bx bx-search-alt fs-1 text-muted"></i><div class="text-muted mt-2">Mahasiswa Kebidanan tidak ditemukan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($mahasiswa->hasPages())
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-top bg-light">
                <small class="text-muted">Menampilkan {{ $mahasiswa->firstItem() }}–{{ $mahasiswa->lastItem() }} dari {{ $mahasiswa->total() }} mahasiswa</small>
                {{ $mahasiswa->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('head')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = @json(csrf_token());
    const updateRoute = @json(route('admin.aktivasi-uap.update-status'));
    const bulkRoute = @json(route('admin.aktivasi-uap.bulk-update'));

    function notify(type, message) {
        if (typeof toastr !== 'undefined') {
            toastr[type](message);
        } else {
            window.alert(message);
        }
    }

    document.querySelectorAll('.toggle-uap').forEach(function (toggle) {
        toggle.addEventListener('change', async function () {
            const status = this.checked ? 1 : 0;
            const label = this.closest('td').querySelector('.status-text');
            this.disabled = true;

            try {
                const response = await fetch(updateRoute, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({mahasiswa_id: this.dataset.id, status: status})
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Status gagal diperbarui.');
                label.textContent = status ? 'AKTIF' : 'NONAKTIF';
                label.className = 'status-text ' + (status ? 'text-success' : 'text-muted');
                notify('success', data.message);
            } catch (error) {
                this.checked = !this.checked;
                notify('error', error.message || 'Terjadi kesalahan jaringan.');
            } finally {
                this.disabled = false;
            }
        });
    });

    document.querySelectorAll('.bulk-uap').forEach(function (button) {
        button.addEventListener('click', async function () {
            const status = Number(this.dataset.status);
            const confirmed = typeof Swal !== 'undefined'
                ? (await Swal.fire({title: 'Konfirmasi Aktivasi UAP', text: (status ? 'Aktifkan' : 'Nonaktifkan') + ' UAP seluruh mahasiswa Kebidanan pada hasil filter?', icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, lanjutkan', cancelButtonText: 'Batal'})).isConfirmed
                : window.confirm('Lanjutkan perubahan status UAP secara massal?');
            if (!confirmed) return;

            this.disabled = true;
            try {
                const response = await fetch(bulkRoute, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json'},
                    body: JSON.stringify({
                        status: status,
                        search: document.getElementById('search').value,
                        kelas: document.getElementById('kelas').value
                    })
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Aktivasi massal gagal.');
                if (typeof Swal !== 'undefined') await Swal.fire({title: 'Berhasil', text: data.message, icon: 'success'});
                window.location.reload();
            } catch (error) {
                notify('error', error.message || 'Terjadi kesalahan jaringan.');
            } finally {
                this.disabled = false;
            }
        });
    });
});
</script>
@endpush
