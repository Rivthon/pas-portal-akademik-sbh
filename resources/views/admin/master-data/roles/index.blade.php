@extends('layouts.master')
@section('title', 'Data Role')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data Role
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah data role yang ada di sistem. Anda dapat menambah, mengedit, dan menghapus role
                    sesuai dengan kebutuhan.
                </p>
                <!-- CTA Button -->
                @can('role-create')
                <div class="mb-3">
                    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary" data-bs-toggle="tooltip"
                        data-bs-original-title="Tambah">
                        Tambah Role
                    </a>
                </div>
                @endcan
            </div>
        </div>
        <!-- Image Section -->
        <div class="col-md-5 text-center">
            <div class="p-3">
                <img src="{{ asset('assets/img/illustrations/mahasiswa.png') }}" class="img-fluid"
                    alt="Illustration of a schedule" style="max-height: 200px;">
            </div>
        </div>
    </div>
</div>
<!-- Tabel Role -->
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">List Role</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Role
            </caption>
            <thead>
                <tr>
                    <th class="text-center" width="150ox">#</th>
                    <th class="text-left">Name</th>
                    <th class="text-center">Permission</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($roles as $key => $role)
                <tr>
                    <td class="text-center">{{ ++$i }}</td>
                    <td class="text-left">{{ $role->name }}</td>
                    <td class="text-center"><span class="badge bg-label-primary">{{ $role->permissions_count }} akses</span></td>
                    <td>
                        @can('role-edit')
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.roles.edit', $role->id) }}">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>
                        @endcan
                        @can('role-delete')
                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}"
                            style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus role ini?')">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {!! $roles->links('pagination::bootstrap-5') !!}
    </div>
</div>
@endsection
