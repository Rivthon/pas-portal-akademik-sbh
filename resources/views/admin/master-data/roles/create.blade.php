@extends('layouts.master')
@section('title', 'Tambah Roles')
@section('content')
<div class="row">
    <div class="col-lg-12 col-md-10 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Tambah Roles</h5>
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
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label"><strong>Nama:</strong></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Nama">
                    </div>
                    <div class="mb-3">
                        <strong>Permission:</strong>
                        <div class="row">
                            @foreach($permission as $value)
                            <div class="col-md-3">
                                <div class="form-check">
                                    <input type="checkbox" name="permission[{{$value->id}}]" value="{{$value->id}}"
                                        class="form-check-input" id="perm_{{$value->id}}">
                                    <label class="form-check-label" for="perm_{{$value->id}}">{{ $value->name }}</label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.roles.index') }}"
                            class="btn btn-secondary d-flex align-items-center gap-2">
                            <i class="bx bx-arrow-back"></i> Kembali
                        </a>
                        <button type="submit"
                            class="btn btn-primary d-flex align-items-center justify-content-center gap-2">
                            <i class="bx bx-save"></i> Simpan Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection