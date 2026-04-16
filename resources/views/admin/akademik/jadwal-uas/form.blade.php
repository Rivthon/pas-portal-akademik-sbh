@extends('layouts.master')
@section('title', isset($jadwalUas) ? 'Edit Jadwal UAS' : 'Tambah Tambah Jadwal UAS')
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
            <h5>{{ isset($jadwalUas) ? 'Update Jadwal UTS' : 'Tambah Jadwal UTS' }}</h5>
        </div>
        <div class="card-body">
            <form
                action="{{ isset($jadwalUas) ? route('admin.jadwal-uas.update', $jadwalUas->id) : route('admin.jadwal-uas.store') }}"
                method="POST">
                @csrf
                @if(isset($jadwalUas))
                @method('PUT')
                @endif
                <input type="hidden" name="ta_id" value="{{ $tahunAjaranAktif->ta_id }}">
                @if(isset($jadwalUas))
                <input type="hidden" name="id" value="{{ $jadwalUas->id }}">
                @endif
                <div class="form-group">
                    <label for="matakuliah_id">Mata Kuliah</label>
                    <select name="matakuliah_id" id="matakuliah_id" class="form-control select2" required>
                        <option value="">Pilih Mata Kuliah</option>
                        @foreach($matakuliah as $item)
                        <option value="{{ $item->matakuliah_id }}" {{ (old('matakuliah_id', $jadwalUts->matakuliah_id ??
                            '') == $item->matakuliah_id) ? 'selected' : '' }}>
                            {{ $item->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" name="tanggal" id="tanggal" class="form-control"
                        value="{{ old('tanggal', $jadwalUts->tanggal ?? '') }}" required>
                </div>
                <div class="form-group">
                    <label for="jam">Jam</label>
                    <input type="text" name="jam" id="jam" class="form-control"
                        value="{{ old('jam', $jadwalUts->jam ?? '') }}" required>
                </div>

                <div class="form-group">
                    <label for="jenis_kelas">Jenis Kelas</label>
                    <select name="jenis_kelas" id="jenis_kelas" class="form-control" required>
                        <option value="" disabled selected>Pilih Jenis Kelas</option>
                        <option value="Reguler" {{ old('jenis_kelas', $jadwalUts->jenis_kelas ?? '') == 'Reguler' ?
                            'selected' : '' }}>
                            Reguler
                        </option>
                        <option value="Karyawan" {{ old('jenis_kelas', $jadwalUts->jenis_kelas ?? '') == 'Karyawan' ?
                            'selected' : ''
                            }}>
                            Karyawan
                        </option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="ruangan_id">Ruangan</label>
                    <select name="ruangan_id" id="ruangan_id" class="form-control" required>
                        <option value="">Pilih Ruangan</option>
                        @foreach($ruangan as $item)
                        <option value="{{ $item->ruangan_id }}" {{ (old('ruangan_id', $jadwalUts->ruangan_id ?? '') ==
                            $item->ruangan_id) ? 'selected' : '' }}>
                            {{ $item->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="d-flex justify-content-between mt-3">
                    <a href="{{ route('admin.jadwal-uas.index') }}" class="btn btn-secondary">Kembali</a>
                    <button type="submit" class="btn btn-primary"
                        onclick="return confirm('Apakah Anda yakin ingin menyimpan data ini?')">
                        {{ isset($jadwalUas) ? 'Perbaharui' : 'Simpan' }}
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