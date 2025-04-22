@extends('layouts.master')
@section('title', isset($tagihan) ? 'Edit Tagihan Mahasiswa' : 'Tambah Tagihan Mahasiswa')

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
        <h5 class="card-title">{{ isset($tagihan) ? 'Edit Tagihan Mahasiswa' : 'Tambah Tagihan Mahasiswa' }}</h5>
    </div>
    <div class="card-body">
        <form
            action="{{ isset($tagihan) ? route('admin.tagihan-mahasiswa.update', $tagihan->id) : route('admin.tagihan-mahasiswa.store') }}"
            method="POST" novalidate>

            @csrf
            @isset($tagihan)
            @method('PUT')
            @endisset

            <div class="row">
                <!-- Mahasiswa -->
                <div class="col-md-6 mb-3">
                    <label for="mahasiswa_id" class="form-label"><strong>Mahasiswa:</strong></label>
                    <select name="mahasiswa_id" class="form-select" required>
                        <option value="">-- Pilih Mahasiswa --</option>
                        @foreach ($mahasiswa as $mhs)
                        <option value="{{ $mhs->mahasiswa_id }}" {{ old('mahasiswa_id', $tagihan->mahasiswa_id ?? '') == $mhs->mahasiswa_id
                            ? 'selected' : '' }}>
                            {{ $mhs->nama }} - {{ $mhs->nim }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Semester -->
                <div class="col-md-6 mb-3">
                    <label for="semester" class="form-label"><strong>Semester:</strong></label>
                    <select name="semester" class="form-select" required>
                        <option value="">-- Pilih Semester --</option>
                        @for ($i = 1; $i <= 10; $i++) <option value="{{ $i }}" {{ old('semester', $tagihan->semester ??
                            '') == $i ? 'selected' : '' }}>
                            Semester {{ $i }}
                            </option>
                            @endfor
                    </select>
                </div>

                <input type="hidden" name="ta_id" value="{{ old('ta_id', $tahunAjaran->ta_id) }}">

                <!-- Tenor Pembayaran -->
                <div class="col-md-6 mb-3">
                    <label for="tenor_pembayaran_id" class="form-label"><strong>Tenor Pembayaran:</strong></label>
                    <select name="tenor_pembayaran_id" class="form-select" id="tenor_pembayaran" required>
                        <option value="">-- Pilih Tenor --</option>
                        @foreach ($tenor as $t)
                        <option value="{{ $t->id }}" data-persentase="{{ $t->persentase }}" {{
                            old('tenor_pembayaran_id', $tagihan->tenor_pembayaran_id ?? '') == $t->id ? 'selected' : ''
                            }}>
                            {{ $t->nama }} - {{ $t->persentase }}%
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Jumlah Tagihan -->
                <div class="col-md-6 mb-3">
                    <label for="jumlah_tagihan" class="form-label"><strong>Jumlah Tagihan (Rp):</strong></label>
                    <input type="number" name="jumlah_tagihan" class="form-control" id="jumlah_tagihan"
                        value="{{ old('jumlah_tagihan', $tagihan->jumlah_tagihan ?? '') }}" required>
                </div>

                <!-- Total Pembayaran (Dihitung Otomatis) -->
                <div class="col-md-6 mb-3">
                    <label for="total_pembayaran" class="form-label"><strong>Total Pembayaran (Rp):</strong></label>
                    <input type="number" name="total_pembayaran" class="form-control" id="total_pembayaran"
                        value="{{ old('total_pembayaran', $tagihan->total_pembayaran ?? 0) }}">
                </div>

                <!-- Jatuh Tempo -->
                <div class="col-md-6 mb-3">
                    <label for="jatuh_tempo" class="form-label"><strong>Jatuh Tempo:</strong></label>
                    <input type="date" name="jatuh_tempo" class="form-control"
                        value="{{ old('jatuh_tempo', $tagihan->jatuh_tempo ?? '') }}" required>
                </div>

                <!-- Status -->
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label"><strong>Status:</strong></label>
                    <select name="status" class="form-select" required>
                        <option value="belum_lunas" {{ old('status', $tagihan->status ?? '') == 'belum_lunas' ?
                            'selected' : '' }}>
                            Belum Lunas
                        </option>
                        <option value="lunas" {{ old('status', $tagihan->status ?? '') == 'lunas' ? 'selected' : '' }}>
                            Lunas
                        </option>
                    </select>
                </div>
            </div>

            <!-- Tombol Simpan -->
            <button type="submit" class="btn btn-primary">
                <i class="bx bxs-save"></i> {{ isset($tagihan) ? 'Update' : 'Simpan' }}
            </button>
        </form>
    </div>
</div>

<!-- Script untuk menghitung total pembayaran -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const jumlahTagihanInput = document.getElementById('jumlah_tagihan');
        const tenorPembayaranSelect = document.getElementById('tenor_pembayaran');
        const totalPembayaranInput = document.getElementById('total_pembayaran');

        function hitungTotalPembayaran() {
            const jumlahTagihan = parseFloat(jumlahTagihanInput.value) || 0;
            const tenorSelected = tenorPembayaranSelect.options[tenorPembayaranSelect.selectedIndex];
            const persentase = parseFloat(tenorSelected.dataset.persentase) || 0;

            const totalPembayaran = (jumlahTagihan * persentase) / 100;
            totalPembayaranInput.value = totalPembayaran.toFixed(0);
        }

        jumlahTagihanInput.addEventListener('input', hitungTotalPembayaran);
        tenorPembayaranSelect.addEventListener('change', hitungTotalPembayaran);
    });
</script>

@endsection
