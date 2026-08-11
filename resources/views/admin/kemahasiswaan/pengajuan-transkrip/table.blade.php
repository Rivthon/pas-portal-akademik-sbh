<div class="table-responsive">
    <table class="table table-modern table-hover align-middle text-center mb-0">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th class="text-start">Mahasiswa</th>
                <th>Jenis</th>
                <th>Status</th>
                <th class="text-start">Keperluan</th>
                <th style="width: 15%;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($pengajuan as $index => $item)
            <tr>
                <td class="text-center">{{ $loop->iteration + ($pengajuan->currentPage() - 1) * $pengajuan->perPage() }}</td>
                <td class="text-start fw-semibold" style="font-size: .85rem;">{{ $item->mahasiswa->nama }}</td>
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
                <td class="text-start" style="font-size: .85rem;">{{ $item->keperluan }}</td>
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