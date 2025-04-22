@extends('layouts.mahasiswa')

@section('content')

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card bg-light">
            <div class="card-body">
                <h4 class="card-title">Jadwal Kuliah</h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped mt-4">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Mata Kuliah</th>
                                <th>Semester</th>
                                <th>Hari</th>
                                <th>Jam</th>
                                <th>Ruangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($jadwal as $key => $item)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $item->mataKuliah->nama }}</td>
                                <td>{{ $item->mataKuliah->semester }}</td>
                                {{-- <td>{{ $item->hari }}</td>
                                <td>{{ $item->jam }}</td> --}}
                                <td>{{ $item->ruangan }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada jadwal tersedia untuk semester ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

</div>
@endsection