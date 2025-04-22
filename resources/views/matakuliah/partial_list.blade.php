<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Kode</th>
                <th>Kategori</th>
                <th>SKS</th>
                <th>Semester</th>
                <th>Jurusan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($matakuliah as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->nama ?? '-' }}</td>
                <td>{{ $item->matakuliah_id ?? '-' }}</td>
                <td>
                    <span
                        class="badge
                        {{ $item->kategori_mk == 1 ? 'bg-success' : ($item->kategori_mk == 0 ? 'bg-primary' : 'bg-secondary') }}">
                        {{ $item->kategori_mk == 1 ? 'Pilihan' : ($item->kategori_mk == 0 ? 'Wajib' : 'Tidak Diketahui')
                        }}
                    </span>
                </td>
                <td>{{ $item->sks ?? '-' }}</td>
                <td>{{ ucfirst($item->semester) }} ({{ $item->smt }})</td>
                <td>{{ $item->programStudi->nama ?? '-' }}</td>
                <td>
                    <a href="{{ route('admin.matakuliah.edit', $item->matakuliah_id) }}" class="btn btn-sm btn-warning">
                        <i class="bx bxs-edit"></i>
                    </a>
                    <form action="{{ route('admin.matakuliah.destroy', $item->matakuliah_id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="bx bxs-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Tidak ada data mata kuliah.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($matakuliah->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $matakuliah->links('pagination::bootstrap-4') }}
</div>
@endif