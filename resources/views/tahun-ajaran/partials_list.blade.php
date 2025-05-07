<div class="row">
    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-light">
                <caption class="text-muted">List Tahun Ajaran</caption>
                <thead class="table-light text-center">
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Semester</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
            <tbody>
                @forelse ($tahunAjarans as $index => $ta)
                <tr>
                    <td class="text-center">{{ $tahunAjarans->firstItem() + $index }}</td>
                    <td>{{ $ta->nama }}</td>
                    <td class="text-center">{{ $ta->semester }}</td>
                    <td class="text-center">
                        <form action="{{ route('admin.tahun-ajaran.updateStatus', $ta->ta_id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="btn btn-sm {{ $ta->status_ta == 1 ? 'btn-success' : 'btn-secondary' }}">
                                {{ $ta->status_ta == 1 ? 'Aktif' : 'Tidak Aktif' }}
                            </button>
                        </form>
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.tahun-ajaran.edit', $ta->ta_id) }}" class="btn btn-sm btn-warning">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                            data-bs-target="#deleteModal{{ $ta->ta_id }}">
                            <i class="fa fa-trash"></i>
                        </button>

                        <!-- Modal Konfirmasi Hapus -->
                        <div class="modal fade" id="deleteModal{{ $ta->ta_id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body text-center">
                                        Apakah Anda yakin ingin menghapus tahun ajaran
                                        <strong class="text-danger">{{ $ta->nama }}</strong>?
                                    </div>
                                    <div class="modal-footer">
                                        <form action="{{ route('admin.tahun-ajaran.destroy', $ta->ta_id) }}"
                                            method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger">Hapus</button>
                                        </form>
                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Batal</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- End Modal -->
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Data tidak tersedia</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($tahunAjarans->hasPages())
    <div class="d-flex justify-content-center mt-3 pagination-links">
        {{ $tahunAjarans->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
</div>
</div>