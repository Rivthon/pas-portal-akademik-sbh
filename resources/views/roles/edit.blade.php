@extends('layouts.master')
@section('title', 'Edit Roles')
@section('content')
<div class="row">
    <div class="col-lg-12 col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Roles</h5>
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

                <form method="POST" action="{{ route('admin.roles.update', $role->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Name:</strong>
                                <input type="text" name="name" placeholder="Name" class="form-control"
                                    value="{{ $role->name }}">
                            </div>
                        </div>
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="form-group">
                                <strong>Permission:</strong>
                                <div class="row">
                                    @foreach($permission as $value)
                                    <div class="col-md-3 col-sm-6">
                                        <div class="form-check">
                                            <input type="checkbox" name="permission[{{$value->id}}]"
                                                value="{{$value->id}}" class="form-check-input" id="perm_{{$value->id}}"
                                                {{ in_array($value->id, $rolePermissions) ?
                                            'checked' : '' }}>
                                            <label class="form-check-label" for="perm_{{$value->id}}">
                                                {{ $value->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.roles.index') }}"
                                class="btn btn-secondary d-flex align-items-center gap-2">
                                <i class="bx bx-arrow-back"></i> Kembali
                            </a>
                            <button type="submit"
                                class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                                <i class="bx bx-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </form>
                @endsection