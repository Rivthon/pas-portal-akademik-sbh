@extends('layouts.master')
@section('title', isset($tenor) ? 'Edit Tenor Pembayaran' : 'Tambah Tenor Pembayaran')

@section('content')

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong> Terdapat beberapa kesalahan dalam input Anda.<br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="card mb-4">
    <div class="card-header">
        <h5 class="card-title">{{ isset($tenor) ? 'Edit Tenor Pembayaran' : 'Tambah Tenor Pembayaran' }}</h5>
    </div>
    <div class="card-body">
        <form
            action="{{ isset($tenor) ? route('admin.tenor-pembayaran.update', $tenor->id) : route('admin.tenor-pembayaran.store') }}"
            method="POST">
            @csrf
            @isset($tenor)
            @method('PUT')
            @endisset

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label"><strong>Semester:</strong></label>
                    <select name="semester" class="form-control" required>
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 10; $i++) <option value="{{ $i }}" {{ old('semester', $tenor->semester ??
                            '') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                            </option>
                            @endfor
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tenor" class="form-label"><strong>Tenor:</strong></label>
                    <input type="text" name="tenor" class="form-control" placeholder="Tenor Pembayaran"
                        value="{{ old('tenor', $tenor->tenor ?? '') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="persentase" class="form-label"><strong>Persentase:</strong></label>
                    <div class="input-group">
                        <input type="number" name="persentase" class="form-control" placeholder="Persentase Pembayaran"
                            value="{{ old('persentase', $tenor->persentase ?? '') }}" required>
                        <span class="input-group-text">%</span>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="batas_waktu" class="form-label"><strong>Batas Waktu:</strong></label>
                    <input type="date" name="batas_waktu" class="form-control"
                        value="{{ old('batas_waktu', $tenor->batas_waktu ?? '') }}" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bx bxs-save"></i> {{ isset($tenor) ? 'Update' : 'Simpan' }}
            </button>
        </form>
    </div>
</div>

@endsection