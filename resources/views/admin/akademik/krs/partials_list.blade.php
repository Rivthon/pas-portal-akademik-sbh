{{-- Partial view: KRS list table (rendered via AJAX) --}}

@if($mahasiswa->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bx bx-search-alt" style="font-size: 2.5rem;"></i>
        <p class="mt-2 mb-0 fw-semibold">Tidak ada data KRS ditemukan.</p>
        <small>Coba ubah filter pencarian atau tambahkan KRS baru.</small>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-modern mb-0">
            <thead>
                <tr>
                    <th style="width: 40px;">#</th>
                    <th>Mahasiswa</th>
                    <th>Program Studi</th>
                    <th>Semester</th>
                    <th>Mata Kuliah KRS</th>
                    <th style="width: 80px;">Detail</th>
                </tr>
            </thead>
            <tbody>
                @foreach($mahasiswa as $index => $mhs)
                    @php
                        $krsItems = $mhs->krs ?? collect();
                        $mkCount = $krsItems->count();
                        $totalSks = $krsItems->sum(function($k) {
                            return $k->kurikulum->mataKuliah->sks ?? 0;
                        });

                        // Badge prodi color
                        $prodiName = strtolower($mhs->programStudi->singkat ?? $mhs->programStudi->nama ?? '');
                        $badgeClass = 'default';
                        if (str_contains($prodiName, 'farmasi')) $badgeClass = 'farmasi';
                        elseif (str_contains($prodiName, 'gizi')) $badgeClass = 'gizi';
                        elseif (str_contains($prodiName, 'kebidanan') || str_contains($prodiName, 'bidan')) $badgeClass = 'kebidanan';
                    @endphp
                    <tr>
                        <td class="text-muted fw-semibold">{{ $mahasiswa->firstItem() + $index }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="student-info">
                                    <div class="student-name">{{ $mhs->nama }}</div>
                                    <div class="student-nim">{{ $mhs->nim }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge-prodi {{ $badgeClass }}">
                                {{ $mhs->programStudi->singkat ?? $mhs->programStudi->nama ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-info">Semester {{ $mhs->semester }}</span>
                        </td>
                        <td>
                            @if($mkCount > 0)
                                <span class="badge bg-label-success">{{ $mkCount }} MK</span>
                                <span class="text-muted" style="font-size: .75rem;">({{ $totalSks }} SKS)</span>
                            @else
                                <span class="badge bg-label-danger">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($mkCount > 0)
                                <button class="btn btn-sm btn-outline-primary btn-toggle-detail" data-target="detail-{{ $mhs->mahasiswa_id }}" title="Lihat detail mata kuliah">
                                    <i class="bx bx-chevron-down"></i>
                                </button>
                            @endif
                        </td>
                    </tr>

                    {{-- Detail Row --}}
                    @if($mkCount > 0)
                        <tr id="detail-{{ $mhs->mahasiswa_id }}" class="d-none">
                            <td colspan="6" style="background: #fafbfd; padding: 1rem 1.5rem;">
                                <div class="mb-2">
                                    <strong class="text-primary" style="font-size: .8rem;">
                                        <i class="bx bx-list-ul me-1"></i> Daftar Mata Kuliah KRS — {{ $mhs->nama }}
                                    </strong>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: .8rem;">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 30px;">#</th>
                                                <th>Kode MK</th>
                                                <th>Nama Mata Kuliah</th>
                                                <th>SKS</th>
                                                <th>Semester</th>
                                                <th>Nilai KHS</th>
                                                <th style="width: 60px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($krsItems as $kIdx => $krs)
                                                @php
                                                    $mk = $krs->kurikulum->mataKuliah ?? null;
                                                @endphp
                                                <tr>
                                                    <td>{{ $kIdx + 1 }}</td>
                                                    <td><code>{{ $mk->matakuliah_id ?? '-' }}</code></td>
                                                    <td>{{ $mk->nama ?? 'N/A' }}</td>
                                                    <td class="text-center">{{ $mk->sks ?? 0 }}</td>
                                                    <td class="text-center">{{ $mk->smt ?? '-' }}</td>
                                                    <td class="text-center">
                                                        @if($krs->khs)
                                                            <span class="badge bg-success">{{ $krs->khs }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @can('krs-delete')
                                                        <button class="btn btn-sm btn-outline-danger btn-delete-krs"
                                                            data-id="{{ $krs->krs_id }}"
                                                            data-mk="{{ $mk->nama ?? 'MK' }}"
                                                            title="Hapus KRS ini">
                                                            <i class="bx bx-trash"></i>
                                                        </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                                <td class="text-center fw-bold">{{ $totalSks }} SKS</td>
                                                <td colspan="3"></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($mahasiswa->hasPages())
        <div class="pagination-modern">
            <div class="text-muted" style="font-size: .8rem;">
                Menampilkan {{ $mahasiswa->firstItem() }}–{{ $mahasiswa->lastItem() }} dari {{ $mahasiswa->total() }} mahasiswa
            </div>
            <div class="pagination-links">
                {{ $mahasiswa->links() }}
            </div>
        </div>
    @else
        <div class="pagination-modern">
            <div class="text-muted" style="font-size: .8rem;">
                Menampilkan {{ $mahasiswa->total() }} mahasiswa
            </div>
        </div>
    @endif
@endif
