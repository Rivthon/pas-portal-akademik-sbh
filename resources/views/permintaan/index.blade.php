@extends('layouts.master')
@section('title', 'Lista Permintaan Mahasiswa')

@section('content')
<div class="card bg-light">
    <div class="card-body">
        <!-- Search Input -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="search-permintaan" class="form-control" placeholder="Cari Mahasiswa...">
            </div>
        </div>



        <!-- AJAX Table Container -->
        <div id="table-container">
            @include('permintaan.table')
        </div>
        <!-- Loading Spinner -->
        <div id="loading-spinner" class="text-center my-3" style="display: none;">
            <div class="spinner-grow text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>

</script>
@endpush