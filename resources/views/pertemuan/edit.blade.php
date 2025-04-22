@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">
        <h3>Edit Pertemuan</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.pertemuan.update', [$jadwal_id, $pertemuan->pertemuan_id]) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="topik">Topik</label>
                <input type="text" id="topik" name="topik" class="form-control" value="{{ old('topik', $pertemuan->topik) }}" required>
            </div>
            <div class="form-group">
                <label for="tanggal_pertemuan">Tanggal Pertemuan</label>
                <input type="date" id="tanggal_pertemuan" name="tanggal_pertemuan" class="form-control" value="{{ old('tanggal_pertemuan', $pertemuan->tanggal_pertemuan) }}" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="1" {{ $pertemuan->status == 1 ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $pertemuan->status == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Simpan</button>
            <a href="{{ route('admin.pertemuan.index', $jadwal_id) }}" class="btn btn-secondary mt-3">Batal</a>
        </form>
    </div>
</div>
@endsection
