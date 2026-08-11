@extends('layouts.master')
@section('title', 'List Pengajuan Transkrip')
@section('content')

@push('head')
<style>
    .pengajuan-page .filter-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .pengajuan-page .table-card {
        background: #fff;
        border: 1px solid rgba(0,0,0,.06);
        border-radius: 12px;
        overflow: hidden;
    }
</style>
@endpush

<div class="pengajuan-page">
    <div class="mb-4">
        <h4 class="fw-bold mb-1">Pengajuan Transkrip Mahasiswa</h4>
        <p class="text-muted mb-0" style="font-size: .875rem;">
            Kelola permintaan cetak transkrip sementara maupun akhir dari mahasiswa.
        </p>
    </div>

    <div class="filter-card mb-4">
        <div class="row align-items-end">
            <div class="col-md-5">
                <label for="search-pengajuan" class="form-label text-muted fw-bold" style="font-size:.7rem; letter-spacing:.05em; text-transform:uppercase;">Cari Mahasiswa</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bx bx-search"></i></span>
                    <input type="text" id="search-pengajuan" class="form-control bg-light border-0" placeholder="Ketik nama mahasiswa...">
                </div>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div id="transkrip-section">
            <div id="loading-spinner" class="text-center py-5" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Memuat data pengajuan...</p>
            </div>
            <div id="table-container">
                @include('admin.kemahasiswaan.pengajuan-transkrip.table')
            </div>
        </div>
    </div>
</div>
@endsection