@extends('layouts.master')
@section('title', 'Edit Calender Akademik')
@section('content')
<div class="row">
    <div class="col-lg-10 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Calender Akademik</h5>
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
                <form action="{{ route('admin.calender.update', $kalender->id) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <!-- Pilih Jurusan -->
                    <input type="hidden" name="id" value="{{ $kalender->id }}">
                    <div class="mb-3">
                        <label for="jurusan_id" class="form-label">Jurusan</label>
                        <select class="form-control" name="jurusan_id" required>
                            <option value="">Pilih Jurusan</option>
                            @foreach ($programStudi as $j)
                            <option value="{{ $j->jurusan_id }}" {{ $kalender->jurusan_id == $j->jurusan_id ? 'selected'
                                : ''
                                }}>
                                {{ $j->nama }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="mb-3">
                        <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                        <input type="text" class="form-control" id="tahun_ajaran" name="tahun_ajaran"
                            value="{{ $r->tahun_ajaran }}" required>
                    </div> --}}

                    <!-- File PDF -->
                    <div class="mb-3">
                        <label for="file" class="form-label">File Kalender Akademik (PDF)</label>
                        <input type="file" class="form-control" name="file" accept="application/pdf">
                        <small class="text-muted">Kosongkan jika tidak ingin mengganti file.</small>
                        @if ($kalender->path)
                        <p class="mt-2">
                            <a href="{{ asset('storage/' . $kalender->path) }}" target="_blank"
                                class="btn btn-sm btn-info">
                                <i class="fa-solid fa-file-pdf"></i> Lihat File
                            </a>
                        </p>
                        @endif
                    </div>
                    <!-- Status -->
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control" name="status">
                            <option value="1" {{ $kalender->status == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ $kalender->status == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>
@endsection