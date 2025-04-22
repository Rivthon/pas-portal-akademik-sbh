@extends('layouts.master')
@section('title', isset($tarif) ? 'Edit Tarif Per Semester' : 'Tambah Tarif Per Semester')
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
        <h5 class="card-title">{{ isset($tarif) ? 'Edit Tarif Per Semester' : 'Tambah Tarif per Semester' }}</h5>
    </div>
    <div class="card-body">
        <form action="{{ isset($tarif) ? route('admin.tarif.update', $tarif->id) : route('admin.tarif.store') }}"
            method="POST">
            @csrf
            @isset($tarif)
            @method('PUT')
            @endisset

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="jurusan_id" class="form-label"><strong>Program Studi:</strong></label>
                    <select name="jurusan_id" class="form-control" required>
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $prodi)
                        <option value="{{ $prodi->jurusan_id }}" {{ old('jurusan_id', $tarif->jurusan_id ?? '') ==
                            $prodi->jurusan_id ? 'selected' : '' }}>
                            {{ $prodi->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gelombang_id" class="form-label"><strong>Gelombang:</strong></label>
                    <select name="gelombang_id" class="form-control" required>
                        <option value="">-- Pilih Gelombang --</option>
                        @foreach ($gelombangs as $g)
                        <option value="{{ $g->id }}" {{ old('gelombang_id', $tarif->gelombang_id ?? '') == $g->id ?
                            'selected' : '' }}>
                            {{ $g->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label"><strong>Semester:</strong></label>
                    <select name="semester" class="form-control" required>
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 10; $i++) <option value="{{ $i }}" {{ old('semester', $tarif->semester ??
                            '') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                            </option>
                            @endfor
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tarif" class="form-label"><strong>Tarif:</strong></label>
                    <div class="input-group">
                        <span class="input-group-text">IDR</span>
                        <input type="text" name="tarif" class="form-control" placeholder="Tarif Mata Kuliah"
                            value="{{ old('tarif', $tarif->tarif ?? 0) }}" required>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="tahun_masuk" class="form-label"><strong>Tahun Masuk:</strong></label>
                    <select name="tahun_masuk" class="form-control" required>
                        <option value="">-- Pilih Tahun Masuk --</option>
                        @for ($year = 2019; $year <= now()->year; $year++)
                            <option value="{{ $year }}" {{ old('tahun_masuk', $tarif->tahun_masuk ?? '') == $year ?
                                'selected' : '' }}>
                                {{ $year }}
                            </option>
                            @endfor
                    </select>
                </div>
            </div>
    </div>
    <button type="submit" class="btn btn-primary">
        <i class="bx bxs-save"></i> {{ isset($tarif) ? 'Update' : 'Simpan' }}
    </button>
    </form>
</div>
</div>


@endsection