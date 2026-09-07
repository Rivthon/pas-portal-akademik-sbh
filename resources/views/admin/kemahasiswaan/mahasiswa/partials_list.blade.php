<table class="table table-bordered table-striped align-middle">
    <thead class="table-primary">
        <tr>
            @can('mahasiswa-edit')
            <th class="text-center" style="width: 40px;">
                <input type="checkbox" id="select-all-mahasiswa" class="form-check-input" aria-label="Pilih semua mahasiswa">
            </th>
            @endcan
            <th>Avatar</th>
            <th>Nama</th>
            <th>Email</th>
            <th>NIM</th>
            <th>Program Studi</th>
            <th>Kelas</th>
            <th>Dosen Pembimbing</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($mahasiswa as $m)
        <tr data-id="{{ $m->mahasiswa_id }}">
            @can('mahasiswa-edit')
            <td class="text-center">
                <input type="checkbox" class="form-check-input mahasiswa-checkbox" value="{{ $m->mahasiswa_id }}"
                    aria-label="Pilih {{ $m->nama }}">
            </td>
            @endcan
            <!-- Avatar -->
            <td class="text-center">
                <img src="{{ $m->avatar_url }}" alt="Avatar" class="rounded-circle border"
                    style="width: 40px; height: 40px; object-fit: cover;"
                    onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'" />
            </td>

            <!-- Nama, Email, NIM, Program Studi -->
            <td>{{ $m->nama }}</td>
            <td>{{ $m->email }}</td>
            <td>{{ $m->nim }}</td>
            <td>{{ $m->programStudi->nama ?? '-' }}</td>
            <td class="text-center" style="min-width: 145px;">
                @can('mahasiswa-edit')
                <select class="form-select form-select-sm kelas-dropdown"
                    data-previous="{{ \App\Models\Mahasiswa::normalisasiKelasUntukPenyimpanan($m->kelas) ?? '' }}">
                    <option value="pagi" @selected(\App\Models\Mahasiswa::normalisasiKelasUntukPenyimpanan($m->kelas) === 'pagi')>
                        Reguler A
                    </option>
                    <option value="karyawan" @selected(\App\Models\Mahasiswa::normalisasiKelasUntukPenyimpanan($m->kelas) === 'karyawan')>
                        Reguler B
                    </option>
                </select>
                @else
                <span class="badge bg-label-info">{{ $m->label_kelas }}</span>
                @endcan
            </td>
            <td class="text-center">
                <select name="dosen_id" class="form-select form-select-sm dosen-dropdown">
                    <option value="">Pilih Dosen</option>
                    @foreach($dosen as $d)
                    <option value="{{ $d->dosen_id }}" {{ $m->dosen_id == $d->dosen_id ? 'selected' : '' }}>
                        {{ $d->nama }}
                    </option>
                    @endforeach
                </select>
            </td>
            <!-- Status dengan Badge dan Dropdown -->
            <td class="text-center">
                @php
                $statusColors = [
                'aktif' => 'success',
                'nonaktif' => 'danger',
                'lulus' => 'primary',
                'dropout' => 'warning',
                'cuti' => 'info',
                ];
                @endphp

                <span class="badge status-badge bg-{{ $statusColors[$m->status_mhs] ?? 'secondary' }}">
                    {{ ucfirst($m->status_mhs) }}
                </span>

                <select name="status_mhs" class="form-select form-select-sm status-dropdown mt-1">
                    @foreach(['aktif', 'nonaktif', 'lulus', 'dropout', 'cuti'] as $status)
                    <option value="{{ $status }}" {{ $m->status_mhs === $status ? 'selected' : '' }}>
                        {{ ucfirst($status) }}
                    </option>
                    @endforeach
                </select>
            </td>

            <!-- Aksi -->
            <td class="text-center">
                @can('mahasiswa-edit')
                <a class="btn btn-primary btn-sm" href="{{ route('admin.mahasiswa.edit', $m->mahasiswa_id) }}"
                    data-bs-toggle="tooltip" data-bs-original-title="Edit Mahasiswa">
                    <i class="bx bx-edit"></i>
                </a>
                @endcan

                @can('mahasiswa-delete')
                <form action="{{ route('admin.mahasiswa.destroy', $m->mahasiswa_id) }}" method="POST"
                    style="display:inline;" onsubmit="return confirm('Apakah Anda yakin?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm" data-bs-toggle="tooltip"
                        data-bs-original-title="Hapus Mahasiswa">
                        <i class="bx bx-trash"></i>
                    </button>
                </form>
                @endcan

                <form action="{{ route('admin.resetPassword', $m->mahasiswa_id) }}" method="POST"
                    style="display:inline;" onsubmit="return confirm('Reset password mahasiswa ini?')">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm" data-bs-toggle="tooltip"
                        data-bs-original-title="Reset Password">
                        <i class="bx bx-reset"></i>
                    </button>
                </form>

                @can('mahasiswa-impersonate')
                <form action="{{ route('admin.mahasiswa.impersonate', $m->mahasiswa_id) }}" method="POST"
                    style="display:inline;" onsubmit="return confirm('Login sebagai mahasiswa {{ $m->nama }}?')">
                    @csrf
                    <button type="submit" class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                        data-bs-original-title="Login sebagai Mahasiswa">
                        <i class="bx bx-log-in-circle"></i>
                    </button>
                </form>
                @endcan
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="@can('mahasiswa-edit') 10 @else 9 @endcan" class="text-center text-muted">Data tidak ditemukan</td>
        </tr>
        @endforelse
    </tbody>
</table>

<!-- Notifikasi -->
<div id="status-alert" class="alert d-none position-fixed top-0 end-0 m-3"></div>
