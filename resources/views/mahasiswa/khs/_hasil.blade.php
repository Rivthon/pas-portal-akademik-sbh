<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h5 class="mb-1">{{ $judulHasil ?? 'Kartu Hasil Studi' }}</h5>
                <p class="text-muted mb-0">
                    Semester {{ $semesterKhs ?: '-' }} &bull;
                    {{ $ta?->nama ?? '-' }}{{ $ta?->semester ? ' - '.ucfirst($ta->semester) : '' }}
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('mahasiswa.khs.cetak', ['ta_id' => $selectedTaId]) }}" class="btn btn-primary">
                    <i class="bx bx-printer me-1"></i>Cetak KHS
                </a>
                @if($tampilkanRiwayatEdom ?? false)
                    <a href="{{ route('mahasiswa.edom.index') }}" class="btn btn-outline-warning">
                        <i class="bx bx-history me-1"></i>Riwayat EDOM
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th class="ps-4">No.</th>
                    <th>Mata Kuliah</th>
                    <th class="text-center">SKS</th>
                    <th class="text-center">HM</th>
                    <th class="text-center">AM</th>
                    <th class="text-center pe-4">SKS x AM</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $calculateKhsWeight = fn ($grade) => match ($grade) {
                        'A' => 4.00, 'AB' => 3.75, 'BA' => 3.50, 'B' => 3.00,
                        'BC' => 2.75, 'C' => 2.00, 'D' => 1.00, 'E' => 0,
                        default => 0,
                    };
                @endphp
                @forelse($khs as $index => $item)
                    @php
                        $bobot = $calculateKhsWeight($item->khs);
                        $bobotTotal = ($item->kurikulum->mataKuliah->sks ?? 0) * $bobot;
                    @endphp
                    <tr>
                        <td class="ps-4">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item->kurikulum->mataKuliah->nama }}</strong>
                            <small class="d-block text-muted">{{ $item->kurikulum->mataKuliah->matakuliah_id }}</small>
                        </td>
                        <td class="text-center">{{ $item->kurikulum->mataKuliah->sks }}</td>
                        <td class="text-center"><span class="badge bg-label-primary">{{ $item->khs }}</span></td>
                        <td class="text-center">{{ number_format($bobot, 2) }}</td>
                        <td class="text-center pe-4">{{ number_format($bobotTotal, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-5">Belum ada nilai KHS pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr><th colspan="4" class="text-end">Total SKS</th><th colspan="2">{{ $khs->sum(fn ($item) => $item->kurikulum->mataKuliah->sks) }}</th></tr>
                <tr><th colspan="4" class="text-end">IPS</th><th colspan="2">{{ number_format($ips, 2) }}</th></tr>
                <tr><th colspan="4" class="text-end">IPK</th><th colspan="2">{{ number_format($ipk, 2) }}</th></tr>
            </tfoot>
        </table>
    </div>
</div>
