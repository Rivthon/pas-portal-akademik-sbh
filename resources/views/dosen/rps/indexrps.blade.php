@extends('layouts.dosen')

@section('content')

    <div class="container-fluid">

        <div class="card">

            <div class="card-header">

                <h4>
                    Rencana Pembelajaran Semester (RPS)
                </h4>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-striped">

                    <thead>

                        <tr>

                            <th width="5%">No</th>

                            <th>Mata Kuliah</th>

                            <th>Program Studi</th>

                            <th>Semester</th>
                            <th>Kelas</th>

                            <th>Status</th>

                            <th width="20%">Aksi</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($mataKuliah as $item)

                            <tr>

                                <td>

                                    {{ $loop->iteration }}

                                </td>

                                <td>

                                    {{ $item->kurikulum->mataKuliah->nama }}

                                </td>

                                <td>

                                    {{ $item->kurikulum->programStudi->nama }}

                                </td>

                                <td>

                                    {{ $item->kurikulum->mataKuliah->semester }}

                                </td>
                                <td>
                                    @if($item->jenis_kelas == 'reguler')
                                        <span class="badge bg-primary">
                                            REGULER A
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark">
                                            REGULER B
                                        </span>
                                    @endif
                                </td>
                                <td>

                                    @if($item->rps)

                                    <span class="badge bg-success">
                                        SUDAH UPLOAD
                                    </span>

                                    @else

                                    <span class="badge bg-danger">
                                        BELUM UPLOAD
                                    </span>

                                    @endif

                                </td>

                                <td>
@if($item->rps)

<a href="{{ asset('storage/'.$item->rps->file) }}"
    target="_blank"
    class="btn btn-success btn-sm">
    Lihat
</a>

@endif

                                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#uploadRps{{ $item->kurikulum_id }}">

                                        Upload

                                    </button>

                                </td>

                            </tr>

                            @include('dosen.rps.modal-upload')

                        @empty

                            <tr>

                                <td colspan="6">

                                    Belum ada mata kuliah.

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

@endsection