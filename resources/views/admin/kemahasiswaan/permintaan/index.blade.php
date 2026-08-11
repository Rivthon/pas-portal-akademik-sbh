@extends('layouts.master')
@section('title', 'Helpdesk')
@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Helpdesk Mahasiswa & Dosen</h5>
    </div>
    <div class="card-body">
        <div id="permintaan-section">
            <form action="{{ route('admin.helpdesk.index') }}" method="GET" class="row g-2 mb-3">
                <div class="col">
                    <input type="search" name="search" id="search-permintaan" class="form-control"
                        value="{{ request('search') }}" placeholder="Cari nama pemohon atau judul...">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit"><i class="bx bx-search me-1"></i>Cari</button>
                    @if (request()->filled('search'))
                        <a href="{{ route('admin.helpdesk.index') }}" class="btn btn-outline-secondary">Reset</a>
                    @endif
                </div>
            </form>
            <div id="loading-spinner" class="text-center my-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="table-container">
                @include('admin.kemahasiswaan.permintaan.table') {{-- bagian ini akan diganti via AJAX --}}
            </div>
        </div>
    </div>
</div>
@endsection
