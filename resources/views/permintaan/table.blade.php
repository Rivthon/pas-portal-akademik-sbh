<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama Mahasiswa</th>
                <th>Jenis Permintaan</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($permintaan as $index => $item)
            <tr>
                <td>{{ $permintaan->firstItem() + $index }}</td>
                <td>{{ $item->mahasiswa->nama ?? '-' }}</td>
                <td>{{ ucfirst($item->jenis_permintaan) }}</td>
                <td>{{ $item->judul }}</td>
                <td><span class="badge bg-info">{{ ucfirst($item->status) }}</span></td>
                <td>
                    <a href="{{ route('admin.helpdesk.show', $item->id) }}" class="btn btn-sm btn-primary">Detail</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Tidak ada data.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Custom Pagination -->
    @if ($permintaan->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <p class="text-sm text-muted">
                Menampilkan {{ $permintaan->firstItem() }} sampai {{ $permintaan->lastItem() }} dari {{
                $permintaan->total() }} data
            </p>
        </div>
        <div>
            {!! $permintaan->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
    </div>
    @endif
</div>