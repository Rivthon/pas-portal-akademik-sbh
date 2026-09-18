<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th style="min-width: 105px;">Waktu</th>
                <th style="min-width: 240px;">Mata Kuliah</th>
                <th>Jenis</th>
                <th style="min-width: 170px;">Prodi / Semester</th>
                <th>Kelas</th>
                <th>Ruangan</th>
                <th style="min-width: 210px;">Dosen Pengampu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($jadwalItems as $item)
                @php($mataKuliah = $item->kurikulum?->mataKuliah)
                <tr>
                    <td>
                        @if($item->jam_mulai || $item->jam_selesai)
                            <span class="fw-semibold text-dark">
                                {{ $item->jam_mulai ? substr((string) $item->jam_mulai, 0, 5) : '--:--' }}
                                &ndash;
                                {{ $item->jam_selesai ? substr((string) $item->jam_selesai, 0, 5) : '--:--' }}
                            </span>
                        @else
                            <span class="badge bg-label-warning">Belum diatur</span>
                        @endif
                    </td>
                    <td>
                        <div class="fw-bold text-dark">{{ $item->nama_matakuliah }}</div>
                        <small class="text-muted">{{ $mataKuliah?->matakuliah_id ?? '-' }} &bull; {{ (int) ($mataKuliah?->sks ?? 0) }} SKS</small>
                    </td>
                    <td>
                        <span class="badge {{ $item->jenis_jadwal === 'Praktik' ? 'bg-label-success' : 'bg-label-primary' }}">
                            <i class="bx {{ $item->jenis_jadwal === 'Praktik' ? 'bx-test-tube' : 'bx-book-open' }} me-1"></i>{{ $item->jenis_jadwal }}
                        </span>
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $item->programStudi?->singkat ?: ($item->programStudi?->nama ?? '-') }}</div>
                        <small class="text-muted">Semester {{ $mataKuliah?->smt ?? '-' }}</small>
                    </td>
                    <td><span class="badge bg-label-info">{{ $item->kelas_label }}</span></td>
                    <td>
                        @if($item->ruangan)
                            <span class="fw-semibold"><i class="bx bx-door-open me-1 text-warning"></i>{{ $item->ruangan->nama }}</span>
                        @else
                            <span class="text-muted">Belum diatur</span>
                        @endif
                    </td>
                    <td>
                        @forelse($item->dosen_pengampu as $namaDosen)
                            <div class="small mb-1"><i class="bx bx-user me-1 text-primary"></i>{{ $namaDosen }}</div>
                        @empty
                            <span class="text-muted small">Belum ditugaskan</span>
                        @endforelse
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
