@extends('layouts.master')
@section('title', 'Tambah Program Studi')
@section('content')
<div class="row">
    <div class="col-lg-10 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Program Studi</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('admin.program-studi.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <!-- Input untuk Jurusan ID -->
                        <div class="col-md-6 mb-3">
                            <label for="jurusan_id" class="form-label"><strong>Kode Prog Studi:</strong></label>
                            <input type="text" name="jurusan_id" id="jurusan_id"
                                class="form-control @error('jurusan_id') is-invalid @enderror"
                                placeholder="Kode Prog Studi" value="{{ old('jurusan_id') }}">
                            @error('jurusan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Nama Program Studi -->
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label"><strong>Nama:</strong></label>
                            <input type="text" name="nama" id="nama"
                                class="form-control @error('nama') is-invalid @enderror" placeholder="Nama Prog Studi"
                                value="{{ old('nama') }}">
                            @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Kaprodi -->
                        <div class="col-md-6 mb-3">
                            <label for="kaprod" class="form-label"><strong>Kaprodi:</strong></label>
                            <input type="text" name="kaprod" id="kaprod"
                                class="form-control @error('kaprod') is-invalid @enderror"
                                placeholder="Nama Ketua Program Studi" value="{{ old('kaprod') }}">
                            @error('kaprod')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Jenjang -->
                        <div class="col-md-6 mb-3">
                            <label for="jenjang" class="form-label"><strong>Jenjang:</strong></label>
                            <select name="jenjang" id="jenjang"
                                class="form-control @error('jenjang') is-invalid @enderror">
                                <option value="">-- Pilih Jenjang --</option>
                                <option value="D3" {{ old('jenjang')=='D3' ? 'selected' : '' }}>D3</option>
                                <option value="S1" {{ old('jenjang')=='S1' ? 'selected' : '' }}>S1</option>
                                <option value="S2" {{ old('jenjang')=='S2' ? 'selected' : '' }}>S2</option>
                                <option value="S3" {{ old('jenjang')=='S3' ? 'selected' : '' }}>S3</option>
                            </select>
                            @error('jenjang')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk TTD -->
                        <div class="col-md-6 mb-3">
                            <label for="ttd" class="form-label"><strong>TTD:</strong></label>
                            <input type="file" name="ttd" id="ttd"
                                class="form-control @error('ttd') is-invalid @enderror">
                            @error('ttd')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Header BAAK -->
                        <div class="col-md-6 mb-3">
                            <label for="header_baak" class="form-label"><strong>Header BAAK:</strong></label>
                            <input type="file" name="header_baak" id="header_baak"
                                class="form-control @error('header_baak') is-invalid @enderror">
                            @error('header_baak')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Kaprodi BAAK -->
                        <div class="col-md-6 mb-3">
                            <label for="kapro_baak" class="form-label"><strong>Kaprodi BAAK:</strong></label>
                            <input type="file" name="kapro_baak" id="kapro_baak"
                                class="form-control @error('kapro_baak') is-invalid @enderror">
                            @error('kapro_baak')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Input untuk Dospem BAAK -->
                        <div class="col-md-6 mb-3">
                            <label for="dospem_baak" class="form-label"><strong>Dospem BAAK:</strong></label>
                            <input type="file" name="dospem_baak" id="dospem_baak"
                                class="form-control @error('dospem_baak') is-invalid @enderror">
                            @error('dospem_baak')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa-solid fa-floppy-disk"></i> Simpan
                            </button>
                            <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection