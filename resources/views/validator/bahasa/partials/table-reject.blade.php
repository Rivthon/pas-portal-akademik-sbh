@if($query->count())
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Nama Bahasa</th>
                <th>Level</th>
                <th>Penyelenggara</th>
                <th>Tanggal Tes</th>
                <th>Skor</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($query as $item)
            <tr>
                <td>{{ $loop->iteration + ($query->currentPage() - 1) * $query->perPage() }}</td>
                <td>{{ $item->mahasiswa->nama ?? '-' }}</td>
                <td>{{ $item->nama_bahasa }}</td>
                <td>{{ $item->level }}</td>
                <td>{{ $item->penyelenggara }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal_tes)->format('d M Y') }}</td>
                <td>{{ $item->skor }}</td>
                <td>
                    <span class="badge bg-danger">{{ ucfirst($item->status_validasi) }}</span>
                </td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#detailModal{{ $item->id }}">
                        Lihat
                    </button>
                </td>
            </tr>

            <!-- Modal Detail -->
            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1"
                aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-light">
                            <h5 class="modal-title text-white" id="detailModalLabel{{ $item->id }}">Detail Penguasaan
                                Bahasa Asing</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body text-start">
                            <dl class="row">
                                <dt class="col-sm-4">Nama Bahasa</dt>
                                <dd class="col-sm-8">{{ $item->nama_bahasa }}</dd>

                                <dt class="col-sm-4">Level</dt>
                                <dd class="col-sm-8">{{ $item->level }}</dd>

                                <dt class="col-sm-4">Penyelenggara</dt>
                                <dd class="col-sm-8">{{ $item->penyelenggara }}</dd>

                                <dt class="col-sm-4">Tanggal Tes</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($item->tanggal_tes)->format('d M Y') }}
                                </dd>

                                <dt class="col-sm-4">Skor</dt>
                                <dd class="col-sm-8">{{ $item->skor }}</dd>

                                @if($item->file_sertifikat)
                                <dt class="col-sm-4">File Sertifikat</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $item->file_sertifikat }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">
                                        Lihat Sertifikat
                                    </a>
                                </dd>
                                @endif

                                <dt class="col-sm-4">Status Validasi</dt>
                                <dd class="col-sm-8">{{ ucfirst($item->status_validasi) }}</dd>

                                <dt class="col-sm-4">Catatan Validator</dt>
                                <dd class="col-sm-8">{{ $item->catatan_validator }}</dd>

                                <dt class="col-sm-4">Bobot</dt>
                                <dd class="col-sm-8">{{ $item->bobot }}</dd>
                            </dl>
                            <form action="{{ route('admin.bahasa.catatan', $item->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="status_validasi_{{ $item->id }}" class="form-label"><strong>Status
                                            Validasi:</strong></label>
                                    <select name="status_validasi" id="status_validasi_{{ $item->id }}"
                                        class="form-select">
                                        <option value="menunggu" {{ old('status_validasi', $item->status_validasi) ===
                                            'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                        <option value="disetujui" {{ old('status_validasi', $item->status_validasi) ===
                                            'disetujui' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="ditolak" {{ old('status_validasi', $item->status_validasi) ===
                                            'ditolak' ? 'selected' : '' }}>Ditolak</option>
                                        <option value="ditinjau" {{ old('status_validasi', $item->status_validasi) ===
                                            'ditinjau' ? 'selected' : '' }}>Ditinjau</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="bobot_{{ $item->id }}"
                                        class="form-label"><strong>Bobot:</strong></label>
                                    <input type="number" step="any" name="bobot" id="bobot_{{ $item->id }}"
                                        class="form-control" value="{{ old('bobot', $item->bobot) }}">
                                </div>

                                <div class="mb-3">
                                    <label for="catatan_validator_{{ $item->id }}" class="form-label"><strong>Catatan
                                            Validator:</strong></label>
                                    <textarea name="catatan_validator" id="catatan_validator_{{ $item->id }}" rows="4"
                                        class="form-control">{{ old('catatan_validator', $item->catatan_validator) }}</textarea>
                                </div>

                                <div class="modal-footer px-0">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-center mt-3">
    {!! $query->withQueryString()->links('pagination::bootstrap-5') !!}
</div>
@else
<div class="alert alert-warning text-center">
    Tidak ada data bahasa ditemukan.
</div>
@endif