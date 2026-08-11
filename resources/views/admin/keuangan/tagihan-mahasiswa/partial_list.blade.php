<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="thead-dark">
            <tr>
                <th>#</th>
                <th>Mahasiswa</th>
                <th>Semester</th>
                <th>Tagihan</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Batas Waktu</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tagihan as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->mahasiswa->nama }}</td>
                <td>{{ 'Semester ' . ucfirst($item->semester) }}</td>
                <td>{{ number_format($item->jumlah_tagihan, 0, ',', '.') }}</td>
                <td>{{ number_format($item->total_pembayaran, 0, ',', '.') }}</td>
                <td>{{ number_format($item->total_tagihan - $item->total_pembayaran, 0, ',', '.') }}</td>
                <td>{{ date('l, d M Y', strtotime($item->jatuh_tempo)) }}</td>
                <td>
                    @if($item->total_pembayaran >= $item->total_tagihan)
                    <span class="badge badge-success">Lunas</span>
                    @else
                    <span class="badge badge-warning">Belum Lunas</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.tagihan.detail', $item->id) }}" class="btn btn-sm btn-warning">
                        <i class="bx bx-detail"></i> Detail
                    </a>
                    <form action="{{ route('admin.tagihan-mahasiswa.destroy', $item->id) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"
                            onclick="return confirm('Yakin ingin menghapus data ini?')">
                            <i class="bx bxs-trash"></i> Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center">Tidak ada data tagihan mahasiswa.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
