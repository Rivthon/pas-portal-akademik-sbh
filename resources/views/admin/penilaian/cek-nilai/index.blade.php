@extends('layouts.master')
@section('title', 'Cek Nilai Mahasiswa')
@section('content')

@push('head')
<style>
    .cek-nilai-page .stat-card {
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
        background: #fff;
        transition: box-shadow .2s ease, transform .15s ease;
    }
    .cek-nilai-page .filter-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .cek-nilai-page .filter-card label {
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .1em;
        color: #8592a3;
        margin-bottom: 6px;
        display: block;
    }
    .cek-nilai-page .filter-card .form-select,
    .cek-nilai-page .filter-card .form-control {
        border: none;
        background: #f3f3f7;
        font-size: .85rem;
        border-radius: 8px;
        padding: .55rem .85rem;
    }
    .cek-nilai-page .filter-card .form-select:focus,
    .cek-nilai-page .filter-card .form-control:focus {
        box-shadow: 0 0 0 3px rgba(105,108,255,.12);
    }

    .cek-nilai-page .table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }
    .cek-nilai-page .table-modern thead th {
        background: #f3f3f7;
        font-size: .68rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
        color: #8592a3;
        border: none;
        padding: .85rem 1.25rem;
        white-space: nowrap;
    }
    .cek-nilai-page .table-modern tbody td {
        padding: .85rem 1.25rem;
        vertical-align: middle;
        border-color: rgba(0,0,0,.04);
        font-size: .875rem;
    }
    .cek-nilai-page .table-modern tbody tr:hover {
        background: rgba(105,108,255,.04);
    }
    .cek-nilai-page .pagination-modern {
        background: #f3f3f7;
        padding: 1rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
</style>
@endpush

<div class="cek-nilai-page">
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-2">
                <li class="breadcrumb-item"><a href="{{ route('admin.home') }}"><i class="bx bx-home-alt"></i> Dashboard</a></li>
                <li class="breadcrumb-item"><a href="#">Penilaian</a></li>
                <li class="breadcrumb-item active">Cek Nilai Mahasiswa</li>
            </ol>
        </nav>
        <h4 class="fw-bold mb-1">Cek Nilai UTS, UAS & Tugas</h4>
        <p class="text-muted mb-0" style="font-size: .875rem;">
            Pantau dan cek secara detail komponen nilai mahasiswa per mata kuliah.
            <span class="badge bg-label-primary ms-2 fs-6">{{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester }})</span>
        </p>
    </div>

    {{-- Filter Card --}}
    <div class="filter-card mb-4">
        <form id="search-nilai" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="filter-prodi">Program Studi</label>
                <select class="form-select" id="filter-prodi" name="jurusan_id">
                    <option value="">Semua Program Studi</option>
                    @foreach($programStudiList as $ps)
                    <option value="{{ $ps->jurusan_id }}">{{ $ps->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="filter-semester">Semester</label>
                <select class="form-select" id="filter-semester" name="semester">
                    <option value="">Semua</option>
                    @for($i = 1; $i <= 8; $i++)
                    <option value="{{ $i }}">Semester {{ $i }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-5">
                <label for="search-input">Cari Mahasiswa / Mata Kuliah</label>
                <div class="input-group">
                    <span class="input-group-text" style="background: #f3f3f7; border: none;"><i class="bx bx-search"></i></span>
                    <input type="text" name="search" id="search-input" class="form-control"
                        placeholder="Ketik Nama, NIM, atau Nama Mata Kuliah..." style="border: none; background: #f3f3f7;">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px;">
                    <i class="bx bx-filter-alt me-1"></i> Tampilkan
                </button>
            </div>
        </form>
    </div>

    {{-- Data Table --}}
    <div class="table-card">
        <div id="nilai-list">
            <div class="text-center py-5 text-muted">
                <i class="bx bx-info-circle" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0">Pilih filter di atas lalu klik <strong>Tampilkan</strong> untuk melihat detail nilai.</p>
            </div>
        </div>
    </div>
</div>

@push('script')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterRoute = "{{ route('admin.cek-nilai.filter') }}";

    document.getElementById('search-nilai').addEventListener('submit', function(e) {
        e.preventDefault();
        fetchNilaiData();
    });

    function fetchNilaiData() {
        const params = new URLSearchParams({
            search: document.getElementById('search-input').value,
            jurusan_id: document.getElementById('filter-prodi').value,
            semester: document.getElementById('filter-semester').value,
        });

        const listEl = document.getElementById('nilai-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Memuat data nilai...</p></div>';

        fetch(filterRoute + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            listEl.innerHTML = data.html;
        })
        .catch(() => {
            listEl.innerHTML = '<div class="text-center py-5 text-danger"><i class="bx bx-error" style="font-size:2rem;"></i><p class="mt-2">Gagal memuat data.</p></div>';
        });
    }

    document.addEventListener('click', function(e) {
        const link = e.target.closest('.pagination-links a');
        if (!link) return;
        e.preventDefault();
        const url = link.getAttribute('href');
        const listEl = document.getElementById('nilai-list');
        listEl.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';

        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { listEl.innerHTML = data.html; })
            .catch(() => { listEl.innerHTML = '<div class="text-center py-5 text-danger">Gagal memuat data.</div>'; });
    });
});
</script>
@endpush
@endsection
