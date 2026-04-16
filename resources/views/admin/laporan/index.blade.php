@extends('layouts.app')

@section('content')
<div class="container">

    <h3>Laporan Rekap Absensi</h3>
    {{-- Tampilkan error jika ada --}}
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Tampilkan pesan sukses jika ada --}}
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
    {{-- Tampilkan form jika data mata kuliah ada --}}
    @if ($mataKuliah->isEmpty())
    <div class="alert alert-warning">
        Tidak ada mata kuliah yang tersedia untuk dipilih.
    </div>
    @else
    <form action="{{ route('admin.laporan.generate-pdf') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="jadwal_id">Pilih Mata Kuliah / Jadwal</label>
            <select name="jadwal_id" id="jadwal_id" class="form-control @error('jadwal_id') is-invalid @enderror"
                required>
                <option value="">-- Pilih Mata Kuliah --</option>
                @foreach($mataKuliah as $jadwal)
                <option value="{{ $jadwal->jadwal_id }}">
                    {{ $jadwal->mataKuliah->name }} - {{ $jadwal->hari }} ({{ $jadwal->jam_mulai }} - {{
                    $jadwal->jam_selesai }})
                </option>
                @endforeach
            </select>
            @error('jadwal_id')
            <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <!-- Tombol Submit -->
        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>

    </form>
    @endif
</div>

@endsection