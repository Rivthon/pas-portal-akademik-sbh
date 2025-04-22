@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Tambah Jadwal</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Oops!</strong> Ada masalah dengan input Anda.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.jadwal.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="matakuliah_id" class="form-label">Mata Kuliah</label>
            <select name="matakuliah_id" id="matakuliah_id" class="form-control">
                <option value="">-- Pilih Mata Kuliah --</option>
                @foreach ($matakuliah as $item)
                    <option value="{{ $item->matakuliah_id }}" {{ old('matakuliah_id') == $item->matakuliah_id ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>
           <div class="mb-3">
               <label for="tanggal" class="form-label">Tanggal</label>
               <input type="date" name="tanggal" id="tanggal" class="form-control" required>
           </div>


        <div class="mb-3">
            <label for="hari" class="form-label">Hari</label>
            <input type="text" name="hari" class="form-control" id="hari" value="{{ old('hari') }}" placeholder="Contoh: Senin">
        </div>

        <div class="mb-3">
            <label for="jam_mulai" class="form-label">Jam Mulai</label>
            <input type="time" name="jam_mulai" class="form-control" id="jam_mulai" value="{{ old('jam_mulai') }}">
        </div>

        <div class="mb-3">
            <label for="jam_selesai" class="form-label">Jam Selesai</label>
            <input type="time" name="jam_selesai" class="form-control" id="jam_selesai" value="{{ old('jam_selesai') }}">
        </div>

        <div class="mb-3">
            <label for="ruangan" class="form-label">Ruangan</label>
            <input type="text" name="ruangan" class="form-control" id="ruangan" value="{{ old('ruangan') }}" placeholder="Nama Ruangan">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
