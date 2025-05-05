<div class="table-responsive">
    <table class="table table-hover table-bordered align-middle text-center">
        <thead class="table-primary">
            <tr>
                <th style="width: 5%;">No</th>
                <th>Mahasiswa</th>
                <th>Jenis</th>
                <th>Status</th>
                <th>Keperluan</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengajuan as $index => $item)
            <tr>
                <td>{{ $loop->iteration + ($pengajuan->currentPage() - 1) * $pengajuan->perPage() }}</td>
                <td>{{ $item->mahasiswa->nama }}</td>
                <td><span class="badge bg-primary">{{ ucfirst($item->jenis) }}</span></td>
                <td>
                    @php
                    $badgeColor = [
                    'pending' => 'secondary',
                    'disetujui' => 'success',
                    'diproses' => 'info',
                    'selesai' => 'primary',
                    'ditolak' => 'danger',
                    ][$item->status] ?? 'dark';
                    @endphp
                    <span class="badge bg-{{ $badgeColor }} text-uppercase">{{ $item->status }}</span>
                </td>
                <td>{{ $item->keperluan }}</td>
                <td>
                    <a href="{{ route('admin.pengajuan.edit', $item->id) }}" class="btn btn-sm btn-info text-white">
                        <i class="bx bx-show"></i> Detail
                    </a>
                    <form action="{{ route('admin.pengajuan.destroy', $item->id) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Hapus pengajuan ini?')"
                            class="btn btn-sm btn-danger">
                            <i class="bx bx-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">Tidak ada data pengajuan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($pengajuan->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
            <p class="text-sm text-muted">
                Menampilkan {{ $pengajuan->firstItem() }} sampai {{ $pengajuan->lastItem() }} dari {{
                $pengajuan->total() }} data
            </p>
        </div>
        <div>
            {!! $pengajuan->withQueryString()->links('pagination::bootstrap-5') !!}
        </div>
    </div>
    @endif
</div>