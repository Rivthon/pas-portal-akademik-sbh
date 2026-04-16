@extends('layouts.master')
@section('title', 'List Tahun Ajaran')
@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">List Tahun Ajaran</h5>
        <a href="{{ route('admin.tahun-ajaran.create') }}" class="btn btn-primary btn-sm">
            Tambah Tahun Ajaran
        </a>
    </div>
    <div class="card-body">
        <div id="tahun-ajaran-section">
            <div class="mb-3">
                <input type="text" id="search-tahun-ajaran" class="form-control" placeholder="Cari tahun ajaran...">
            </div>
            <div id="loading-spinner" class="text-center my-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="table-container">
                @include('admin.akademik.tahun-ajaran.table') {{-- bagian ini akan diganti via AJAX --}}
            </div>
        </div>
    </div>
</div>
@endsection