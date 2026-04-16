@extends('layouts.master')
@section('title', 'Edit Pengguna')
@section('content')
<div class="row">
    <div class="col-lg-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Pengguna</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="name" class="form-label"><strong>Nama:</strong></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Nama"
                                    value="{{ old('name', $user->name) }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="email" class="form-label"><strong>Email:</strong></label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email"
                                    value="{{ old('email', $user->email) }}">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="password" class="form-label"><strong>Password (Opsional):</strong></label>
                                <input type="password" name="password" id="password" class="form-control"
                                    placeholder="Password Baru">
                                <small class="text-muted">Kosongkan jika tidak ingin mengubah password.</small>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="confirm-password" class="form-label"><strong>Konfirmasi Password
                                        (Opsional):</strong></label>
                                <input type="password" name="confirm-password" id="confirm-password"
                                    class="form-control" placeholder="Konfirmasi Password Baru">
                            </div>
                        </div>
                        <div class="col-lg-12">
                            <div class="mb-3">
                                <label for="roles" class="form-label"><strong>Role:</strong></label>
                                <select name="roles[]" id="roles" class="form-control" multiple>
                                    @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" {{ in_array($value, $userRoles) ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.users.index') }}"
                            class="btn btn-secondary d-flex align-items-center gap-2">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                        <button type="submit"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                            <i class="bx bx-save"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection