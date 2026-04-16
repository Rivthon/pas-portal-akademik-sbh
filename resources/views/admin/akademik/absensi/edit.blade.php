@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Edit Jadwal</h2>

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

    <form action="{{ route('admin.jadwal.update', $jadwal->jadwal_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="matakuliah_id" class="form-label">Mata Kuliah</label>
            <select name="matakuliah_id" id="matakuliah_id" class="form-control">
                <option value="">-- Pilih Mata Kuliah --</option>
                @foreach ($matakuliah as $item)
                    <option value="{{ $item->matakuliah_id }}" {{ $jadwal->matakuliah_id == $item->matakuliah_id ? 'selected' : '' }}>
                        {{ $item->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="hari" class="form-label">Hari</label>
            <input type="text" name="hari" class="form-control" id="hari" value="{{ $jadwal->hari }}" placeholder="Contoh: Senin">
        </div>

        <div class="mb-3">
            <label for="jam_mulai" class="form-label">Jam Mulai</label>
            <input type="time" name="jam_mulai" class="form-control" id="jam_mulai" value="{{ $jadwal->jam_mulai }}">
        </div>

        <div class="mb-3">
            <label for="jam_selesai" class="form-label">Jam Selesai</label>
            <input type="time" name="jam_selesai" class="form-control" id="jam_selesai" value="{{ $jadwal->jam_selesai }}">
        </div>

        <div class="mb-3">
            <label for="ruangan" class="form-label">Ruangan</label>
            <input type="text" name="ruangan" class="form-control" id="ruangan" value="{{ $jadwal->ruangan }}" placeholder="Nama Ruangan">
        </div>

        <button type="submit" class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.jadwal.index') }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
