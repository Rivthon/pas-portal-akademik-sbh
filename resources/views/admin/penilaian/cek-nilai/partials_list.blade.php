<div class="table-responsive">
    <table class="table table-modern table-hover mb-0">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th>Mahasiswa</th>
                <th>Mata Kuliah</th>
                <th class="text-center">Tugas</th>
                <th class="text-center">Absen</th>
                <th class="text-center">Praktik</th>
                <th class="text-center">UTS</th>
                <th class="text-center">UAS</th>
                <th class="text-center">Akhir</th>
                <th class="text-center">Huruf</th>
            </tr>
        </thead>
        <tbody>
            @forelse($krsData as $index => $krs)
                <tr>
                    <td class="text-center">{{ $krsData->firstItem() + $index }}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm me-2">
                                <span class="avatar-initial rounded-circle bg-label-primary">
                                    {{ substr($krs->mahasiswa->nama ?? 'M', 0, 1) }}
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-0" style="font-size: .85rem;">{{ $krs->mahasiswa->nama ?? 'Tanpa Nama' }}</h6>
                                <small class="text-muted">{{ $krs->mahasiswa->nim ?? '-' }}</small>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="fw-semibold text-dark" style="font-size: .85rem;">{{ $krs->kurikulum->mataKuliah->nama ?? '-' }}</div>
                        <small class="text-muted">
                            {{ $krs->kurikulum->mataKuliah->matakuliah_id ?? '-' }} | Smt {{ $krs->kurikulum->mataKuliah->smt ?? '-' }} | {{ $krs->kurikulum->mataKuliah->sks ?? 0 }} SKS
                        </small>
                    </td>
                    <td class="text-center">{{ $krs->tugas ?? '-' }}</td>
                    <td class="text-center">{{ $krs->absen ?? '-' }}</td>
                    <td class="text-center">{{ $krs->praktik ?? '-' }}</td>
                    <td class="text-center fw-bold text-primary">{{ $krs->uts ?? '-' }}</td>
                    <td class="text-center fw-bold text-primary">{{ $krs->uas ?? '-' }}</td>
                    <td class="text-center">{{ $krs->akhir ?? '-' }}</td>
                    <td class="text-center">
                        @if($krs->khs)
                            <span class="badge bg-label-success">{{ $krs->khs }}</span>
                        @else
                            <span class="badge bg-label-secondary">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <img src="{{ asset('assets/img/illustrations/empty-state.png') }}" alt="Empty" style="height: 120px; opacity: 0.5;" onerror="this.style.display='none'">
                        <p class="text-muted mt-3 mb-0">Tidak ada data nilai yang ditemukan untuk filter ini.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($krsData->hasPages())
<div class="pagination-modern pagination-links">
    <div class="text-muted" style="font-size: .8rem;">
        Menampilkan <strong>{{ $krsData->firstItem() }}</strong> - <strong>{{ $krsData->lastItem() }}</strong> dari <strong>{{ $krsData->total() }}</strong> data
    </div>
    <div>
        {{ $krsData->links('pagination::bootstrap-4') }}
    </div>
</div>
@endif
