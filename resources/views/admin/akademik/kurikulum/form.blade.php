@extends('layouts.master')
@section('title', isset($kurikulum) ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah')
@section('content')
<div class="container">
    @if ($errors->any())
    <div class="alert alert-danger text-center">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">
                {{ $kurikulum->exists ? 'Edit Mata Kuliah' : 'Tambah Mata Kuliah' }}
            </h4>
        </div>
        <div class="card-body">
            <form
                action="{{ $kurikulum->exists ? route('admin.kurikulum.update', $kurikulum) : route('admin.kurikulum.store') }}"
                method="POST">
                @csrf
                @if($kurikulum->exists)
                @method('PUT')
                @endif

                <!-- Tahun Ajaran -->
                <input type="hidden" name="ta_id" value="{{ $tahunAjaranAktif->ta_id }}">
                <div class="mb-3">
                    <label class="form-label">Tahun Ajaran</label>
                    <input type="text" class="form-control"
                        value="{{ $tahunAjaranAktif->nama }} - {{ $tahunAjaranAktif->semester }}" disabled>
                </div>

                <!-- Mata Kuliah -->
                <div class="mb-3">
                    <label for="matakuliah_id" class="form-label">Mata Kuliah</label>
                    <select name="matakuliah_id" id="matakuliah_id" class="form-control select2" required>
                        <option value="">-- Pilih Mata Kuliah --</option>
                        @foreach($mataKuliah as $mata)
                        <option value="{{ $mata->matakuliah_id }}" {{ old('matakuliah_id', $kurikulum->matakuliah_id) ==
                            $mata->matakuliah_id ? 'selected' : '' }}>
                            {{ $mata->matakuliah_id }} - {{ $mata->nama }} (Semester {{ $mata->smt }})
                        </option>
                        @endforeach
                    </select>
                    @error('matakuliah_id') <div class="text-danger">{{ $message }}</div> @enderror
                </div>

                <!-- Ruangan -->
                {{-- <div class="mb-3">
                    <label for="ruangan_id" class="form-label">Ruangan</label>
                    <select name="ruangan_id" id="ruangan_id" class="form-control select2" required>
                        <option value="">-- Pilih Ruangan --</option>
                        @foreach($ruangan as $r)
                        <option value="{{ $r->ruangan_id }}" {{ old('ruangan_id', $kurikulum->ruangan_id) ==
                            $r->ruangan_id ? 'selected' : '' }}>
                            {{ $r->nama }}
                        </option>
                        @endforeach
                    </select>
                    @error('ruangan_id') <div class="text-danger">{{ $message }}</div> @enderror
                </div> --}}

                <!-- Jam Mulai -->
                {{-- <div class="mb-3">
                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" id="jam_mulai" class="form-control"
                        value="{{ old('jam_mulai', $kurikulum->jam_mulai) }}" required>
                    @error('jam_mulai') <div class="text-danger">{{ $message }}</div> @enderror
                </div> --}}

                <!-- Jam Selesai -->
                {{-- <div class="mb-3">
                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" id="jam_selesai" class="form-control"
                        value="{{ old('jam_selesai', $kurikulum->jam_selesai) }}" required>
                    @error('jam_selesai') <div class="text-danger">{{ $message }}</div> @enderror
                </div> --}}

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary">
                    {{ $kurikulum->exists ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('admin.kurikulum.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>

</div>
@endsection