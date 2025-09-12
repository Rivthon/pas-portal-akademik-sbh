@extends('layouts.app')

@section('content')
<div class="row mb-4">
    <div class="col-lg-12">
        <h2>Tambah Mahasiswa Baru</h2>
    </div>
</div>

{{-- Alert error global --}}
@if ($errors->any())
<div class="alert alert-danger">
    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda:<br><br>
    <ul>
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form action="{{ route('admin.mahasiswa.store') }}" method="POST">
    @csrf
    <div class="row">
        {{-- Nama --}}
        <div class="col-md-6 mb-3">
            <label for="name" class="form-label"><strong>Nama</strong></label>
            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                placeholder="Masukkan nama lengkap" value="{{ old('name') }}">
            @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Email --}}
        <div class="col-md-6 mb-3">
            <label for="email" class="form-label"><strong>Email</strong></label>
            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                placeholder="Masukkan email" value="{{ old('email') }}">
            @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        {{-- Password --}}
        <div class="col-md-6 mb-3">
            <label for="password" class="form-label"><strong>Password</strong></label>
            <input type="password" name="password" id="password"
                class="form-control @error('password') is-invalid @enderror" placeholder="Masukkan password">
            @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- NIM --}}
        <div class="col-md-6 mb-3">
            <label for="nim" class="form-label"><strong>NIM</strong></label>
            <input type="text" name="nim" id="nim" class="form-control @error('nim') is-invalid @enderror"
                placeholder="Masukkan NIM" value="{{ old('nim') }}">
            @error('nim')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <div class="row">
        {{-- Program Studi --}}
        <div class="col-md-6 mb-3">
            <label for="program_studi_id" class="form-label"><strong>Program Studi</strong></label>
            <select name="program_studi_id" id="program_studi_id"
                class="form-select @error('program_studi_id') is-invalid @enderror">
                <option value="">-- Pilih Program Studi --</option>
                @foreach ($programStudi as $program)
                <option value="{{ $program->program_studi_id }}" {{ old('program_studi_id')==$program->program_studi_id
                    ? 'selected' : '' }}>
                    {{ $program->name }}
                </option>
                @endforeach
            </select>
            @error('program_studi_id')
            <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    {{-- Tombol submit --}}
    <div class="text-center mt-4">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa-solid fa-floppy-disk"></i> Simpan
        </button>
    </div>
</form>

<p class="text-center text-primary mt-4"><small>Absensi Online</small></p>
@endsection