<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center">
        <thead class="table-primary">
            <tr>
                <th>#</th>
                <th>Nama</th>
                <th>Semester</th>
                <th>Status TA</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tahunAjarans as $ta)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $ta->nama }}</td>
                <td>{{ $ta->semester }}</td>
                <td>
                    <form action="{{ route('admin.tahun-ajaran.updateStatus', $ta->ta_id) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-sm {{ $ta->status_ta == 1 ? 'btn-success' : 'btn-secondary' }}">
                            {{ $ta->status_ta == 1 ? 'Aktif' : 'Tidak Aktif' }}
                        </button>
                    </form>
                </td>
                <td>
                    <a href="{{ route('admin.tahun-ajaran.edit', $ta->ta_id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.tahun-ajaran.destroy', $ta->ta_id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')">Hapus</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center">Data tidak tersedia</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Custom Pagination -->
    @if ($tahunAjarans->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <p class="text-sm text-muted">
                Menampilkan {{ $tahunAjarans->firstItem() }} sampai {{ $tahunAjarans->lastItem() }} dari {{
                $tahunAjarans->total() }} data
            </p>
        </div>
        <div>
            {!! $tahunAjarans->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
    </div>
    @endif
</div>