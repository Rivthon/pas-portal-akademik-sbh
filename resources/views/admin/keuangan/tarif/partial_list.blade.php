<div class="row">
    <div class="table-responsive">
        <table class="table table-borderless table-hover align-middle">
            <thead class="bg-light border-bottom">
                <tr>
                    <th class="text-center rounded-start">#</th>
                    <th>Program Studi</th>
                    <th class="text-center">Semester</th>
                    <th class="text-center">Angkatan</th>
                    <th class="text-center">Gelombang</th>
                    <th class="text-end">Tarif / Nominal</th>
                    <th class="text-center rounded-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tarif as $item)
                <tr>
                    <td class="text-center text-muted">{{ $loop->iteration + ($tarif->currentPage() - 1) * $tarif->perPage() }}</td>
                    <td class="fw-bold text-primary">{{ $item->programStudi->nama ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge bg-label-info">Smt {{ $item->semester }}</span>
                    </td>
                    <td class="text-center fw-bold">{{ $item->tahun_masuk ?? '-' }}</td>
                    <td class="text-center">
                        @if($item->gelombangs)
                            <span class="badge bg-label-primary px-3">{{ $item->gelombangs->nama }}</span>
                        @else
                            <span class="text-muted small">-</span>
                        @endif
                    </td>
                    <td class="text-end text-success fw-bold">Rp {{ number_format($item->tarif, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-icon btn-outline-warning"
                            onclick="editTarifAjax({{ $item->id }}, '{{ $item->semester }}', '{{ $item->jurusan_id }}', '{{ $item->tahun_masuk }}', '{{ $item->gelombang_id }}', '{{ $item->tarif }}')" title="Edit Tarif">
                            <i class="bx bxs-edit"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-icon btn-outline-danger" title="Hapus Tarif"
                            onclick="deleteTarifAjax({{ $item->id }}, 'SMT {{ $item->semester }} - {{ $item->programStudi->singkat ?? $item->programStudi->nama ?? '-' }}')">
                            <i class="bx bxs-trash"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="text-center p-4">
                            <i class="bx bx-receipt text-muted" style="font-size: 3rem;"></i>
                            <p class="mt-2 text-muted">Belum ada data tarif yang sesuai dengan filter.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($tarif->hasPages())
<div class="d-flex justify-content-end mt-3 pagination-links">
    {{ $tarif->links('pagination::bootstrap-4') }}
</div>
@endif