<div class="table-responsive text-nowrap">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th class="text-center" style="width: 5%">No</th>
                <th style="width: 25%">Mata Kuliah</th>
                <th style="width: 15%">Jadwal</th>
                <th style="width: 25%">Dosen</th>
                <th class="text-center" style="width: 20%">Progress</th>
                <th class="text-center" style="width: 10%">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($jadwal as $index => $item)

                @php
                    $pertemuanCount = $item->pertemuan_count ?? $item->pertemuan->count();
                    $progressPercent = $pertemuanCount > 0 ? min(100, round(($pertemuanCount / 14) * 100)) : 0;

                    $dosenList = [];
                    if ($item->kurikulum && $item->kurikulum->dosenToMatakuliah) {
                        foreach ($item->kurikulum->dosenToMatakuliah as $dtm) {
                            if ($dtm->dosen) {
                                $dosenList[] = [
                                    'nama' => $dtm->dosen->nama,
                                    'jenis' => strtolower((string) $dtm->jenis_dosen),
                                ];
                            }
                        }
                    }
                @endphp

                <tr>
                    {{-- NO --}}
                    <td class="text-center text-muted">
                        {{ $jadwal->firstItem() + $index }}
                    </td>

                    {{-- MATAKULIAH --}}
                    <td>
                        <div class="d-flex flex-column">
                            <span class="fw-bold text-dark">
                                {{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}
                            </span>

                            <span class="text-muted small">
                                Kode:
                                <span class="badge bg-label-secondary">
                                    {{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}
                                </span>
                                |
                                SMT:
                                <span class="badge bg-label-info">
                                    {{ $item->kurikulum?->mataKuliah?->smt ?? '-' }}
                                </span>
                            </span>
                        </div>
                    </td>

                    {{-- JADWAL --}}
                    <td>
                        <div class="fw-medium">
                            <i class="bx bx-calendar text-primary me-1"></i>
                            {{ $item->hari }}
                        </div>

                        <div class="text-muted small">
                            <i class="bx bx-time-five me-1"></i>
                            {{ substr($item->jam_mulai, 0, 5) }} -
                            {{ substr($item->jam_selesai, 0, 5) }}
                        </div>

                        <span
                            class="badge bg-label-{{ strtolower($item->jenis_kelas) == 'reguler' ? 'success' : 'warning' }} mt-1">
                            {{ ucfirst($item->jenis_kelas) }}
                        </span>
                    </td>

                    {{-- DOSEN --}}
                    <td>
                        @if(count($dosenList) > 0)
                            <ul class="list-unstyled mb-0 small">
                                @foreach($dosenList as $dosen)
                                    <li class="d-flex align-items-center gap-1 mb-1">
                                        <i class="bx bx-user me-1 text-muted"></i>
                                        <span>{{ $dosen['nama'] }}</span>
                                        <span class="badge rounded-pill bg-label-{{ $dosen['jenis'] === 'praktik' ? 'success' : 'primary' }}"
                                            style="font-size: .65rem;">
                                            {{ $dosen['jenis'] === 'praktik' ? 'Praktik' : 'Teori' }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <span class="text-muted fst-italic small">
                                Belum ada dosen
                            </span>
                        @endif
                    </td>

                    {{-- PROGRESS --}}
                    <td class="text-center">
                        <div class="d-flex flex-column align-items-center">
                            <span class="fw-bold mb-1">
                                {{ $pertemuanCount }}/14
                            </span>

                            <div class="progress w-100" style="height: 6px;">
                                <div class="progress-bar {{ $pertemuanCount >= 14 ? 'bg-success' : 'bg-primary' }}"
                                    style="width: {{ $progressPercent }}%">
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- AKSI --}}
                    <td class="text-center">
                        @can('absensi-export')
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                @disabled($pertemuanCount === 0)>
                                <i class="bx bx-dots-horizontal-rounded me-1"></i>
                                Aksi
                            </button>

                            @if($pertemuanCount > 0)
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li>
                                        <form action="{{ route('admin.absensi.cetakRekap') }}" method="POST" target="_blank">
                                            @csrf
                                            <input type="hidden" name="jadwal_id" value="{{ $item->id }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bx-printer text-info me-2"></i>
                                                Cetak Rekap Absensi
                                            </button>
                                        </form>
                                    </li>
                                    <li>
                                        <form action="{{ route('admin.absensi.cetakBAP') }}" method="POST" target="_blank">
                                            @csrf
                                            <input type="hidden" name="jadwal_id" value="{{ $item->id }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bxs-file-pdf text-success me-2"></i>
                                                Cetak BAP
                                            </button>
                                        </form>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form action="{{ route('admin.absensi.jurnalMengajar') }}" method="POST" target="_blank">
                                            @csrf
                                            <input type="hidden" name="jadwal_id" value="{{ $item->id }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="bx bx-book-content text-primary me-2"></i>
                                                Download Jurnal Mengajar
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            @endif
                        </div>
                        @else
                            <span class="text-muted">-</span>
                        @endcan
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="6" class="text-center py-5">
                        <i class="bx bx-folder-open text-muted" style="font-size: 3rem;"></i>
                        <h6 class="mt-3 fw-bold">Data tidak ditemukan</h6>
                        <small class="text-muted">Coba ubah filter</small>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- PAGINATION --}}
@if($jadwal->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-3 px-2">
        <small class="text-muted">
            {{ $jadwal->firstItem() }} - {{ $jadwal->lastItem() }} dari {{ $jadwal->total() }}
        </small>
        {{ $jadwal->links('pagination::bootstrap-5') }}
    </div>
@endif
