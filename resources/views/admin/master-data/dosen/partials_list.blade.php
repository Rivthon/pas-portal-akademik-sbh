<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Avatar</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Kode Dosen</th>
                <th>Program Studi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($dosen as $key => $m)
            <tr>
                <td>{{ $dosen->firstItem() + $key }}</td>
                <td>
                    <img src="{{ $m->avatar_url }}" alt="Avatar" class="rounded-circle"
                        style="width: 40px; height: 40px; object-fit: cover;"
                        onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'" />
                </td>
                <td>{{ $m->nama }}</td>
                <td>{{ $m->email }}</td>
                <td>{{ $m->kd_dosen }}</td>
                <td>{{ $m->programStudi->nama ?? '-' }}</td>
                <td>
                    <form action="{{ route('admin.dosen.destroy', $m->dosen_id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')

                        @can('dosen-edit')
                        <a class="btn btn-primary btn-sm" href="{{ route('admin.dosen.edit', $m->dosen_id) }}">
                            <i class="bx bx-edit"></i>
                        </a>
                        @endcan

                        @can('dosen-delete')
                        <button type="submit" class="btn btn-danger btn-sm"
                            onclick="return confirm('Apakah Anda yakin?')">
                            <i class="bx bx-trash"></i>
                        </button>
                        @endcan
                    </form>

                    @can('dosen-impersonate')
                    <form action="{{ route('admin.dosen.impersonate', $m->dosen_id) }}" method="POST"
                        class="d-inline" onsubmit="return confirm('Login sebagai dosen ini?')">
                        @csrf
                        <button type="submit" class="btn btn-info btn-sm" data-bs-toggle="tooltip"
                            data-bs-original-title="Login sebagai Dosen">
                            <i class="bx bx-log-in-circle"></i>
                        </button>
                    </form>
                    @endcan
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Tidak ada data ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if ($dosen->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $dosen->links('pagination::bootstrap-4') }}
</div>
@endif
