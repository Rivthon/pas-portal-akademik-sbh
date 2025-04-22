@extends('layouts.master')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>{{ isset($matakuliah) ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}</h2>
        </div>
        <div class="pull-right">
            <a class="btn btn-primary btn-sm" href="{{ route('admin.matakuliah.index') }}">
                <i class="fa fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong> Terdapat beberapa kesalahan dalam input Anda.<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Tentukan action dan method -->
<form
    action="{{ isset($matakuliah) ? route('admin.matakuliah.update', $matakuliah->id) : route('admin.matakuliah.store') }}"
    method="POST">
    @csrf
    @if(isset($matakuliah))
    @method('PUT')
    @endif

    <div class="row">
        <!-- Nama Mata Kuliah -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="name"><strong>Nama Mata Kuliah:</strong></label>
                <input type="text" name="name" class="form-control" placeholder="Nama Mata Kuliah"
                    value="{{ old('name', isset($matakuliah) ? $matakuliah->name : '') }}" required>
            </div>
        </div>

        <!-- Kode Mata Kuliah -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="code"><strong>Kode Mata Kuliah:</strong></label>
                <input type="text" name="code" class="form-control" placeholder="Kode Mata Kuliah"
                    value="{{ old('code', isset($matakuliah) ? $matakuliah->code : '') }}" required>
            </div>
        </div>

        <!-- Total Pertemuan -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="total_pertemuan"><strong>Total Pertemuan:</strong></label>
                <input type="number" name="total_pertemuan" class="form-control" placeholder="Total Pertemuan"
                    value="{{ old('total_pertemuan', isset($matakuliah) ? $matakuliah->total_pertemuan : '') }}"
                    required>
            </div>
        </div>

        <!-- SKS -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="sks"><strong>SKS (Satuan Kredit Semester):</strong></label>
                <input type="number" name="sks" class="form-control" placeholder="SKS"
                    value="{{ old('sks', isset($matakuliah) ? $matakuliah->sks : '') }}" required>
            </div>
        </div>

        <!-- Semester -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="semester"><strong>Semester:</strong></label>
                <input type="number" name="semester" class="form-control" placeholder="Semester"
                    value="{{ old('semester', isset($matakuliah) ? $matakuliah->semester : '') }}" min="1" required>
            </div>
        </div>

        <!-- Kategori -->
        <div class="col-md-6">
            <div class="form-group">
                <label for="kategori_mk"><strong>Kategori Mata Kuliah:</strong></label>
                <select name="kategori_mk" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <option value="1" {{ old('kategori_mk', isset($matakuliah) ? $matakuliah->kategori_mk : '') == 1 ?
                        'selected' : '' }}>Pilihan</option>
                    <option value="0" {{ old('kategori_mk', isset($matakuliah) ? $matakuliah->kategori_mk : '') == 0 ?
                        'selected' : '' }}>Wajib</option>
                </select>
            </div>
        </div>

        <!-- Program Studi -->
        <div class="col-md-12">
            <div class="form-group">
                <label for="program_studi_id"><strong>Program Studi:</strong></label>
                <select name="program_studi_id" class="form-control" required>
                    <option value="">-- Pilih Program Studi --</option>
                    @foreach ($programStudi as $prodi)
                    <option value="{{ $prodi->program_studi_id }}" {{ old('program_studi_id', isset($matakuliah) ?
                        $matakuliah->program_studi_id : '') == $prodi->program_studi_id ? 'selected' : '' }}>
                        {{ $prodi->name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary mt-3">
                <i class="fa-solid fa-floppy-disk"></i> {{ isset($matakuliah) ? 'Update' : 'Simpan' }}
            </button>
        </div>
    </div>
</form>
@endsection