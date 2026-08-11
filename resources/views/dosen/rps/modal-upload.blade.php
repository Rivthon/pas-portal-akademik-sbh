<div class="modal fade" id="uploadRps{{ $item->kurikulum_id }}-{{ strtolower($item->jenis_kelas) }}" tabindex="-1" aria-hidden="true">

    <div class="modal-dialog">

        <form action="{{ route('dosen.rps.store') }}" method="POST" enctype="multipart/form-data">

            @csrf

            <input type="hidden" name="kurikulum_id" value="{{ $item->kurikulum_id }}">

            <div class="modal-content">

                <div class="modal-header">

                    <h5 class="modal-title">

                        {{ $item->rps ? 'Upload Ulang RPS' : 'Upload RPS' }}

                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    @if ($item->rps)
                        <div class="alert alert-info">
                            <div class="fw-semibold mb-1">
                                <i class="bx bx-file me-1"></i>File saat ini
                            </div>
                            <div class="text-break">{{ $item->rps->nama_file ?: basename($item->rps->file) }}</div>
                            <small class="d-block mt-1">
                                Terakhir diperbarui: {{ $item->rps->updated_at?->format('d M Y H:i') ?? '-' }}
                            </small>
                            <a href="{{ route('dosen.rps.show', $item->rps) }}?v={{ $item->rps->updated_at?->timestamp }}"
                                target="_blank" class="btn btn-sm btn-outline-info mt-2">
                                <i class="bx bx-show me-1"></i>Lihat File Saat Ini
                            </a>
                        </div>
                    @endif

                    <div class="mb-3">

                        <label class="form-label">

                            Mata Kuliah

                        </label>

                        <input type="text" class="form-control" value="{{ $item->kurikulum->mataKuliah->nama }}"
                            readonly>

                    </div>
                    <input type="hidden" name="jenis_kelas" value="{{ $item->jenis_kelas }}">
                    <div class="mb-3">

                        <label class="form-label">

                            {{ $item->rps ? 'Pilih File PDF Pengganti' : 'Upload File PDF' }}

                        </label>

                        <input type="file" class="form-control" name="file" accept=".pdf" required>

                        <small class="text-muted">

                            Maksimal 10 MB

                        </small>

                    </div>

                </div>

                <div class="modal-footer">

                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">

                        Batal

                    </button>

                    <button type="submit" class="btn btn-primary">

                        {{ $item->rps ? 'Upload Ulang' : 'Upload' }}

                    </button>

                </div>

            </div>

        </form>

    </div>

</div>
