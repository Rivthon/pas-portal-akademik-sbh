<div class="row">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Program Studi</th>
                    <th>Semester</th>

                    <th>Tahun Masuk</th>
                    <th>Gelombang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tarif as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->programStudi->nama ?? '-' }}</td>
                    <td>{{ ucfirst($item->semester) }}</td>

                    <td>{{ $item->gelombangs->nama ?? '-' }}</td>
                    <td>{{ $item->tahun_masuk ?? '-' }}</td>
                    <td>IDR. {{ number_format($item->tarif, 0, ',', '.') }}</td>
                    <td>
                        <a href="{{ route('admin.tarif.edit', $item->id) }}" class="btn btn-sm btn-warning">
                            <i class="bx bxs-edit"></i>
                        </a>
                        <form action="{{ route('admin.tarif.destroy', $item->id) }}" method="POST"
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
                    <td colspan="6" class="text-center">Tidak ada data tarif.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($tarif->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $tarif->links('pagination::bootstrap-4') }}
</div>
@endif
{{-- <div class="table-responsive">
    <thead class="table-light">
        <tr>
            <th>#</th>
            <th>Program Studi</th>
            <th>Semester</th>
            <th>Tahun Ajaran</th>
            <th>Tahun Masuk</th>\
            <th>Gelombang</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($tarif as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->programStudi->nama ?? '-' }}</td>
            <td>{{ ucfirst($item->semester) }}</td>
            <td>{{ $item->tahun_masuk ?? '-' }}</td>
            <td>{{ $item->gelombangs->nama ?? '-' }}</td>
            <td>IDR. {{ number_format($item->tarif, 0, ',', '.') }}</td>
            <td>
                <a href="{{ route('admin.tarif.edit', $item->id) }}" class="btn btn-sm btn-warning">
                    <i class="bx bxs-edit"></i>
                </a>
                <form action="{{ route('admin.tarif.destroy', $item->id) }}" method="POST" style="display:inline;">
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
            <td colspan="6" class="text-center">Tidak ada data tarif.</td>
        </tr>
        @endforelse
    </tbody>
    </table>
</div>

@if ($tarif->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $tarif->links('pagination::bootstrap-4') }}
</div>
@endif --}}