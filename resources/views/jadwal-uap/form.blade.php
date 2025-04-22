@extends('layouts.master')
@section('content')
<div class="container mt-5">
    @if ($errors->any())
    <div class="alert alert-danger mt-3">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <div class="card">
        <div class="card-header">
            <h5>{{ isset($jadwalUap) ? 'Update Jadwal UAP' : 'Tambah Jadwal UAP' }}</h5>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($jadwalUap) ? route('admin.jadwal-uap.update', $jadwalUap->id) : route('admin.jadwal-uap.store') }}"
                method="POST">
                @csrf
                @if(isset($jadwalUap))
                @method('PUT')
                @endif
                <input type="hidden" name="ta_id" value="{{ $tahunAjaranAktif->ta_id }}">
                @if(isset($jadwalUap))
                <input type="hidden" name="id" value="{{ $jadwalUap->id }}">
                @endif
                <div class="form-group">
                    <label for="nama">nama</label>
                    <input type="text" name="nama" id="nama" class="form-control"
                        value="{{ old('nama', $jadwalUap->nama ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                        value="{{ old('tanggal', $jadwalUap->tanggal ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label for="jam">Jam</label>
                    <input type="text" name="jam" id="jam" class="form-control"
                        value="{{ old('jam', $jadwalUap->jam ?? '') }}" required>
                </div>


                <div class="form-group">
                    <label for="ruangan_id">Ruangan</label>
                    <select name="ruangan_id" id="ruangan_id" class="form-control" required>
                        <option value="">Pilih Ruangan</option>
                        @foreach($ruangan as $item)
                        <option value="{{ $item->ruangan_id }}" {{ (old('ruangan_id', $jadwalUap->ruangan_id ?? '') ==
                            $item->ruangan_id) ? 'selected' : '' }}>
                            {{ $item->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('admin.jadwal-uap.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Apakah Anda yakin ingin menyimpan data ini?')">
                        {{ isset($jadwalUap) ? 'Perbaharui' : 'Simpan' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endsection