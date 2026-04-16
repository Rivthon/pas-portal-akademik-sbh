@extends('layouts.master')
@section('title', 'Program Studi')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Program Studi
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar program studi yang tersedia. Silakan tambahkan program studi baru jika
                    diperlukan.
                </p>
                <!-- CTA Button -->
                @can('program-studi-create')
                <div class="mb-3">
                    <a href="{{ route('admin.program-studi.create') }}" class="btn btn-primary">
                        Tambah Progam Studi
                    </a>
                </div>
                @endcan
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/prodi.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Program Studi</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Program Studi
            </caption>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Prog. Studi</th>
                    <th>Prog. Studi</th>
                    <th>Kaprod</th>
                    <th width="200px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($programStudis as $key => $r)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $r->jurusan_id }}</td>
                    <td>{{ $r->jenjang }} - {{ $r->nama }}</td>
                    <td>{{ $r->kaprod }}</td>
                    <td>
                        <form action="{{ route('admin.program-studi.destroy',$r->jurusan_id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')

                            @can('program-studi-edit')
                            <a class="btn btn-primary btn-sm"
                                href="{{ route('admin.program-studi.edit',$r->jurusan_id) }}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            @endcan

                            @can('program-studi-delete')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure?')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                            @endcan
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {!! $programStudis->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection