@extends('layouts.master')

@section('content')
<div class="card">
    <div class="card-header bg-white text-secondary">
        <h5 class="mb-0">Input Nilai UAP Kebidanan</h5>
    </div>
    <div class="card-body">
        <form id="filter-form">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Tahun Ajaran</label>
                    <select class="form-select" name="tahun_ajaran_id" required>
                        <option value="">Pilih Tahun</option>
                        @foreach($tahunAjaran as $ta)
                        <option value="{{ $ta->ta_id }}">{{ $ta->nama }} - {{ $ta->semester }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Semester</label>
                    <select class="form-select" name="semester" required>
                        @for($i = 1; $i <= 8; $i++) <option value="{{ $i }}">Semester {{ $i }}</option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-4 d-grid">
                    <button type="submit" class="btn btn-primary">Tampilkan Mahasiswa</button>
                </div>
            </div>
        </form>

        <hr class="my-4">

        <div id="mahasiswa-uap-list">
            <!-- AJAX akan menampilkan data di sini -->
        </div>
    </div>
</div>

@endsection
