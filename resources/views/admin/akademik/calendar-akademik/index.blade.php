@extends('layouts.master')
@section('title', 'Calender Akademik')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    <i class="fa fa-calendar"></i> Calender Akademik
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar kalender akademik yang terdaftar di sistem. Silakan tambahkan kalender
                    akademik baru jika diperlukan.
                </p>
                <!-- CTA Button -->
                {{-- @can('evaluasi-create')
                <div class="mb-3">
                    <a href="{{ route('admin.evaluasi.create') }}" class="btn btn-primary">
                        Tambah Pertanyaan
                    </a>
                </div>
                @endcan --}}
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/calender.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Calender</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Kalender Akademik
            </caption>
            <thead>
                <tr>
                    <th width="50px">No</th>
                    <th>Jurusan</th>
                    {{-- <th>Tahun Ajaran</th> --}}
                    <th>File</th>
                    <th>Status</th>
                    <th width="200px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($kalender as $index => $r)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $r->programStudi->nama ?? 'Tidak Ada' }}</td>
                    {{-- <td>{{ $r->tahun_ajaran }}</td> --}}
                    <td>
                        @if ($r->fileExists())
                        <a href="{{ route('admin.calender.file', $r) }}" target="_blank" class="btn btn-info btn-sm">
                            <i class="fa-solid fa-file-pdf"></i> Lihat PDF
                        </a>
                        @elseif ($r->path)
                        <span class="badge bg-label-danger">File hilang, silakan upload ulang</span>
                        @else
                        <span class="badge bg-label-warning">Tidak Ada File</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $r->status ? 'bg-label-success' : 'bg-label-danger' }}">
                            {{ $r->status ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </td>
                    <td>
                        @can('kalender-edit')
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.calender.edit',$r->id) }}">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
