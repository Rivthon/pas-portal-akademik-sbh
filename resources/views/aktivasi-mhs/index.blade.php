@extends('layouts.master')

@section('content')
<div class="card">
    <div class="card-header bg-light">
        <h5 class="mb-0">Verfikasi Aktivasi Mahasiswa</h5>
    </div>

    <div class="card-body">
        <div class="container mt-5">
            <h5 class="mb-4">Daftar Mahasiswa</h5>
            <!-- Form Pencarian dengan AJAX -->

            <form id="search-aktivasi" class="mb-3">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control"
                        placeholder="Cari nama, NIM, atau program studi" value="{{ request()->get('search') }}">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </form>
            <div class="d-flex justify-content-start mb-3">
                <button id="reset-all-status" class="btn btn-danger">Reset Semua Status</button>
            </div>

            <!-- Wrapper List Mata Kuliah -->
            <div id="aktivasi-list">
                @include('aktivasi-mhs.partials_list', ['mahasiswa' => $mahasiswa])
            </div>

        </div>

    </div>
</div>
@endsection