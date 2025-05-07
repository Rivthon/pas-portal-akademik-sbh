@extends('layouts.master')
@section('title', 'List Permintaan')
@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">List Permintaan</h5>
    </div>
    <div class="card-body">
        <div id="permintaan-section">
            <div class="mb-3">
                <input type="text" id="search-permintaan" class="form-control" placeholder="Cari Nama...">
            </div>
            <div id="loading-spinner" class="text-center my-3" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <div id="table-container">
                @include('permintaan.table') {{-- bagian ini akan diganti via AJAX --}}
            </div>
        </div>
    </div>
</div>
@endsection