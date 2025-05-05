@extends('layouts.master')
@section('title','Pengajuan Transkrip')


@section('content')
<div class="card bg-light">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="search-pengajuan" class="form-control" placeholder="Cari Mahasiswa...">
            </div>
        </div>
        <div id="loading-spinner" class="text-center my-3" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
        <div id="table-container">
            @include('pengajuan-transkrip.table')
        </div>

    </div>
</div>
@endsection