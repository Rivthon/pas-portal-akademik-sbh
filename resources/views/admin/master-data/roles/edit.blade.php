@extends('layouts.master')

@section('title', 'Edit Role')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="mb-4">
        <h4 class="fw-bold mb-1"><span class="text-muted fw-light">Pengguna / Role /</span> Edit</h4>
        <p class="text-muted mb-0">Centang hanya fitur yang boleh dibuka oleh role {{ $role->name }}.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger"><i class="bx bx-error-circle me-2"></i>{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <label for="name" class="form-label fw-semibold">Nama Role</label>
                <input type="text" name="name" id="name" class="form-control"
                    value="{{ old('name', $role->name) }}" required>
            </div>
        </div>

        <h5 class="fw-bold mb-3"><i class="bx bx-shield-quarter text-primary me-2"></i>Hak Akses Fitur</h5>
        @include('admin.master-data.roles._permission-selector')

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary">
                <i class="bx bx-arrow-back me-1"></i>Kembali
            </a>
            <button class="btn btn-primary"><i class="bx bx-save me-1"></i>Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
