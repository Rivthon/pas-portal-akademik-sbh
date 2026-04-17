<div class="table-responsive">
    <table class="table table-borderless table-hover align-middle">
        <thead class="bg-light border-bottom">
            <tr>
                <th class="text-center rounded-start">#</th>
                <th class="text-center">Smt</th>
                <th class="text-center">Angkatan</th>
                <th class="text-center">Gelombang</th>
                <th class="text-center">Tenor</th>
                <th class="text-center">Persentase</th>
                <th class="text-center">Batas Waktu</th>
                <th class="text-center rounded-end">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tenor as $item)
            <tr>
                <td class="text-center text-muted">{{ $loop->iteration + (($tenor instanceof \Illuminate\Pagination\LengthAwarePaginator) ? ($tenor->currentPage() - 1) * $tenor->perPage() : 0) }}</td>
                <td class="text-center"><span class="badge bg-label-info">Smt {{ $item->semester }}</span></td>
                <td class="text-center fw-bold text-primary">{{ $item->tahun_masuk ?? '-' }}</td>
                <td class="text-center">
                    @if($item->gelombang)
                        <span class="badge bg-label-primary">{{ $item->gelombang->nama }}</span>
                    @else
                        <span class="text-muted small">-</span>
                    @endif
                </td>
                <td class="text-center"><strong>Ke-{{ $item->tenor }}</strong></td>
                <td class="text-center text-success fw-bold">{{ $item->persentase }}%</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->batas_waktu)->translatedFormat('d M Y') }}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-icon btn-outline-warning" 
                        onclick="editTenor({{ $item->id }}, '{{ $item->semester }}', '{{ $item->tahun_masuk }}', '{{ $item->gelombang_id }}', '{{ $item->tenor }}', '{{ $item->persentase }}', '{{ $item->batas_waktu }}')" title="Edit Tenor">
                        <i class="bx bxs-edit"></i>
                    </button>
                    <form action="{{ route('admin.tenor-pembayaran.destroy', $item->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Tenor"
                            onclick="return confirm('Yakin ingin menghapus cicilan Tenor Ke-{{ $item->tenor }} ini?')">
                            <i class="bx bxs-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8">
                    <div class="text-center p-4">
                        <i class="bx bx-calendar text-muted" style="font-size: 3rem;"></i>
                        <p class="mt-2 text-muted">Belum ada skema tenor yang sesuai dengan filter.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($tenor instanceof \Illuminate\Pagination\LengthAwarePaginator && $tenor->hasPages())
<div class="d-flex justify-content-end mt-3 pagination-links">
    {{ $tenor->links('pagination::bootstrap-4') }}
</div>
@endif