@extends('layouts.master')
@section('title', 'Data Users')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Data User
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Berikut adalah daftar user yang tersedia. Silakan tambahkan user baru jika diperlukan.
                </p>
                <!-- CTA Button -->
                @can('users-create')
                <div class="mb-3">
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary" data-bs-toggle="tooltip"
                        data-bs-original-title="Tambah">
                        Tambah User
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
<div class="card">
    <div class="d-flex align-items-center justify-content-between pe-4">
        <h5 class="card-header mb-0">Users</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <caption class="ms-4">
                List Users
            </caption>
            <thead>
                <tr>
                    <th class="text-center" width="100px">#</th>
                    <th>Avatar</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Roles</th>
                    <th width="280px">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $key => $user)
                <tr>
                    <td class="text-center">{{ ++$i }}</td>
                    <td><img src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('dashboard_assets/assets/img/avatars/1.png') }}"
                            alt="Avatar" class="img-thumbnail" width="50"></td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>

                    <td>
                        @if (!empty($user->getRoleNames()))
                        @foreach ($user->getRoleNames() as $v)
                        <label class="badge bg-success">{{ $v }}</label>
                        @endforeach
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.users.edit', $user->id) }}">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </a>
                        <form method="POST" action="{{ route('admin.users.destroy', $user->id) }}"
                            style="display:inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-sm">
                                <i class="fa-solid fa-trash"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-center mt-3">
        {!! $data->links('pagination::bootstrap-5') !!}
    </div>
</div>
<!-- End Tabel Agama -->
@endsection