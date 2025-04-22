@extends('layouts.master')
@section('title', 'Evluasi Dosen')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Gelombang
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar gelombang yang terdaftar di sistem. Silakan tambahkan gelombang baru jika
                    diperlukan.
                </p>
                <!-- CTA Button -->
                @can('gelombang-create')
                <div class="mb-3">
                    <a href="{{ route('admin.gelombang.create') }}" class="btn btn-primary">
                        Tambah Gelombang
                    </a>
                </div>
                @endcan
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/chat.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Gelombang</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Gelombang Dosen
            </caption>
            <thead>
                <tr>
                    <th width="200px">No</th>
                    <th>Nama</th>
                    <th width="200px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gelombangs as $key => $r)
                <tr>
                    <td>{{ ++$i }}</td>
                    <td>{{ $r->nama }}</td>

                    <td>
                        <form action="{{ route('admin.gelombang.destroy',$r->id) }}" method="POST"
                            style="display:inline;">
                            @csrf
                            @method('DELETE')

                            @can('gelombang-edit')
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.gelombang.edit',$r->id) }}">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </a>
                            @endcan

                            @can('gelombang-delete')
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
        {!! $gelombangs->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
