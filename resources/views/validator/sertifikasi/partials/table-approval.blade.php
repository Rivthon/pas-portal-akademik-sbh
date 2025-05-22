@if($query->count())
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle text-center">
        <thead class="table-primary">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Kegiatan</th>
                <th>Tingkat</th>
                <th>Tanggal</th>
                <th>Status</th>
                <th>Detail</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($query as $item)
            <tr>
                <td>{{ $loop->iteration + ($query->currentPage() - 1) * $query->perPage() }}</td>
                <td>{{ $item->mahasiswa->nama ?? '-' }}</td>
                <td>{{ $item->nama_kegiatan }}</td>
                <td>{{ $item->tingkat_kegiatan }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                <td><span class="badge bg-success">Disetujui</span></td>
                <td>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                        data-bs-target="#detailModal{{ $item->id }}">
                        Lihat
                    </button>
                </td>
            </tr>

            {{-- Modal Detail --}}
            <!-- Modal Detail -->
            <div class="modal fade" id="detailModal{{ $item->id }}" tabindex="-1"
                aria-labelledby="detailModalLabel{{ $item->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-light">
                            <h5 class="modal-title text-white" id="detailModalLabel{{ $item->id }}">Detail Sertifikasi
                                Disetujui
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body text-start">
                            @if(session('success') && session('item_id') == $item->id)
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Tutup"></button>
                            </div>
                            @endif

                            <dl class="row">
                                <dt class="col-sm-4">Nama</dt>
                                <dd class="col-sm-8">{{ $item->mahasiswa->nama ?? '-' }}</dd>

                                <dt class="col-sm-4">Nama Kegiatan</dt>
                                <dd class="col-sm-8">{{ $item->nama_kegiatan }}</dd>

                                <dt class="col-sm-4">Penyelenggara</dt>
                                <dd class="col-sm-8">{{ $item->penyelenggara }}</dd>

                                <dt class="col-sm-4">Tingkat</dt>
                                <dd class="col-sm-8">{{ $item->tingkat_kegiatan }}</dd>

                                <dt class="col-sm-4">Prestasi</dt>
                                <dd class="col-sm-8">{{ $item->prestasi }}</dd>

                                <dt class="col-sm-4">Tanggal</dt>
                                <dd class="col-sm-8">{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</dd>

                                <dt class="col-sm-4">Jenis Sertifikat</dt>
                                <dd class="col-sm-8">{{ ucfirst($item->jenis_sertifikat) }}</dd>

                                <dt class="col-sm-4">Bobot</dt>
                                <dd class="col-sm-8">{{ $item->bobot }}</dd>


                                @if($item->file_sertifikat)
                                <dt class="col-sm-4">File Sertifikat</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $item->file_sertifikat }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                        Lihat Sertifikat
                                    </a>
                                </dd>
                                @endif

                                @if($item->dokumen_pendukung)
                                <dt class="col-sm-4">Dokumen Pendukung</dt>
                                <dd class="col-sm-8">
                                    <a href="{{ $item->dokumen_pendukung }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">
                                        Lihat Dokumen
                                    </a>
                                </dd>
                                @endif
                            </dl>

                            <form action="{{ route('admin.sertifikasi.catatan', $item->id) }}" method="POST">
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
    {!! $query->withQueryString()->links() !!}
</div>
@else
<div class="alert alert-warning text-center">
    Tidak ada data sertifikasi ditemukan.
</div>
@endif