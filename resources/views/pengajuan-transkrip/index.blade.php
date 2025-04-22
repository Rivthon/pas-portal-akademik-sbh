@extends('layouts.master')
@section('title', 'Pengajuan Transkrip Mahasiswa')
@section('content')
<div class="card shadow-sm mb-4">
    <div class="d-flex align-items-center row g-0">
        <!-- Content Section -->
        <div class="col-md-7">
            <div class="card-body">
                <!-- Title -->
                <h5 class="card-title text-primary mb-3 fw-bold">
                    Pengajuan Transkrip Mahasiswa
                </h5>
                <!-- Description -->
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Anda dapat Melihat Pengjuan transkrip mahasiswa melalui menu ini.
                </p>
                <!-- CTA Button -->

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
<div class="card bg-light">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Mahasiswa</th>
                        <th>Jenis</th>
                        <th>Keperluan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($pengajuan as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->mahasiswa->nama }}</td>
                        <td>{{ ucfirst($item->jenis) }}</td>
                        <td>{{ $item->keperluan }}</td>
                        <td>
                            <form action="{{ route('admin.pengajuan.updateStatus', $item->id) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="pending" {{ $item->status == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="disetujui" {{ $item->status == 'disetujui' ? 'selected' : ''
                                        }}>Disetujui
                                    </option>
                                    <option value="diproses" {{ $item->status == 'diproses' ? 'selected' : ''
                                        }}>Diproses
                                    </option>
                                    <option value="selesai" {{ $item->status == 'selesai' ? 'selected' : '' }}>Selesai
                                    </option>
                                    <option value="ditolak" {{ $item->status == 'ditolak' ? 'selected' : '' }}>Ditolak
                                    </option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.pengajuan.edit', $item->id) }}" class="btn btn-sm btn-primary">
                                <i class="bx bxs-edit"></i>
                            </a>
                            <form action="{{ route('admin.pengajuan.destroy', $item->id) }}" method="POST"
                                style="display:inline;">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Hapus pengajuan?')"
                                    class="btn btn-sm btn-danger">
                                    <i class="bx bxs-trash"></i>
                                </button>
                            </form>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection