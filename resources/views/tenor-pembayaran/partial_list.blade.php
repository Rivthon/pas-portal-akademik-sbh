<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Semester</th>
                <th>tenor</th>
                <th>Percent</th>
                <th>Batas Waktu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenor as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ ucfirst($item->semester) }}</td>
                <td>{{ $item->tenor }}</td>
                <td>{{ $item->persentase_formatted }}</td>
                <td>{{ $item->batas_waktu }}</td>
                <td>
                    <a href="{{ route('admin.tenor-pembayaran.edit', $item->id) }}" class="btn btn-sm btn-warning">
                        <i class="bx bxs-edit"></i>
                    </a>
                    <form action="{{ route('admin.tenor-pembayaran.destroy', $item->id) }}" method="POST"
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
                <td colspan="6" class="text-center">Tidak ada data tenor.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($tenor->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $tenor->links('pagination::bootstrap-4') }}
</div>
@endif