@extends('layouts.master')
@section('title', isset($matakuliah) ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah')

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
        <h5 class="card-title">{{ isset($matakuliah) ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}</h5>
    </div>
    <div class="card-body">
        <!-- FORM DIMULAI -->
        <form action="{{ isset($matakuliah)
                            ? route('admin.matakuliah.update', $matakuliah->matakuliah_id)
                            : route('admin.matakuliah.store') }}" method="POST">
            @csrf
            @if(isset($matakuliah))
            @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama" class="form-label"><strong>Nama Mata Kuliah:</strong></label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Mata Kuliah"
                        value="{{ old('nama', $matakuliah->nama ?? '') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="matakuliah_id" class="form-label"><strong>Kode Mata Kuliah:</strong></label>
                    <input type="text" name="matakuliah_id" class="form-control" placeholder="Kode Mata Kuliah"
                        value="{{ old('matakuliah_id', $matakuliah->matakuliah_id ?? '') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="sks" class="form-label"><strong>SKS (Satuan Kredit Semester):</strong></label>
                    <input type="number" name="sks" class="form-control" placeholder="SKS"
                        value="{{ old('sks', $matakuliah->sks ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="smt" class="form-label"><strong>Semester:</strong></label>
                    <select name="smt" id="smt" class="form-control" required>
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 8; $i++) <option value="{{ $i }}" {{ old('smt', $matakuliah->smt ?? '') ==
                            $i ? 'selected' :
                            '' }}>
                            Semester {{ $i }} ({{ $i % 2 == 1 ? 'Ganjil' : 'Genap' }})
                            </option>
                            @endfor
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="kategori_mk" class="form-label"><strong>Kategori Mata Kuliah:</strong></label>
                    <select name="kategori_mk" class="form-control" required>
                        <option value="">-- Pilih Kategori --</option>
                        <option value="0" {{ old('kategori_mk', $matakuliah->kategori_mk ?? '') == 0 ? 'selected' : ''
                            }}>Wajib</option>
                        <option value="1" {{ old('kategori_mk', $matakuliah->kategori_mk ?? '') == 1 ? 'selected' : ''
                            }}>Pilihan</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="jurusan_id" class="form-label"><strong>Program Studi:</strong></label>
                    <select name="jurusan_id" class="form-control" required>
                        <option value="">-- Pilih Program Studi --</option>
                        @foreach ($programStudi as $prodi)
                        <option value="{{ $prodi->jurusan_id }}" {{ old('jurusan_id', $matakuliah->jurusan_id ?? '') ==
                            $prodi->jurusan_id ? 'selected' : '' }}>
                            {{ $prodi->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="{{ route('admin.matakuliah.index') }}" class="btn btn-secondary">
                        <i class="bx bx-arrow-back"></i> Kembali
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bxs-save"></i> {{ isset($matakuliah) ? 'Update' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </form>
        <!-- FORM SELESAI -->
    </div>
</div>
@endsection
