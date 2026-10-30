@extends('layouts.dosen')
@section('title', 'RPS Program Studi')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#6979f8)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">SEKPRODI</span>
                <h3 class="text-white fw-bold mb-1">RPS Program Studi</h3>
                <p class="text-white-50 mb-0">Lihat dan unduh RPS mata kuliah pada program studi yang Anda kelola.</p>
            </div>
            <i class="bx bx-file d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted">Total Mata Kuliah/Kelas</small>
            <h3 class="mb-0">{{ $mataKuliah->count() }}</h3>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted">RPS Tersedia</small>
            <h3 class="text-success mb-0">{{ $mataKuliah->filter(fn ($item) => $item->rpsSekprodi)->count() }}</h3>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <small class="text-muted">Belum Tersedia</small>
            <h3 class="text-danger mb-0">{{ $mataKuliah->reject(fn ($item) => $item->rpsSekprodi)->count() }}</h3>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-4">
        <form method="GET" action="{{ route('dosen.sekprodi.rps.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Program Studi</label>
                <select name="program_studi_id" class="form-select">
                    @foreach($managedPrograms as $program)
                    <option value="{{ $program->jurusan_id }}" @selected((string) $selectedProgramId === (string) $program->jurusan_id)>
                        {{ $program->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tahun Akademik</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                    <option value="{{ $ta->ta_id }}" @selected((string) $selectedTaId === (string) $ta->ta_id)>
                        {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Kelas</label>
                <select name="jenis_kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <option value="reguler" @selected(request('jenis_kelas') === 'reguler')>Reguler A</option>
                    <option value="karyawan" @selected(request('jenis_kelas') === 'karyawan')>Reguler B</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Pencarian</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Mata kuliah, kode, atau dosen">
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" title="Terapkan filter"><i class="bx bx-search"></i></button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Mata Kuliah</th>
                    <th>Kelas</th>
                    <th>Dosen Pengampu</th>
                    <th>Status</th>
                    <th>Pengunggah</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mataKuliah as $item)
                @php($rps = $item->rpsSekprodi)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->kurikulum?->mataKuliah?->nama ?? '-' }}</strong>
                        <small class="d-block text-muted">
                            {{ $item->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}
                            &bull; Semester {{ $item->kurikulum?->mataKuliah?->smt ?: '-' }}
                        </small>
                    </td>
                    <td><span class="badge bg-label-info">{{ jenis_kelas_label($item->jenis_kelas) }}</span></td>
                    <td>{{ $item->dosen_pengampu->implode(', ') ?: '-' }}</td>
                    <td>
                        @if($rps)
                        <span class="badge bg-label-success"><i class="bx bx-check me-1"></i>Tersedia</span>
                        <small class="d-block text-muted mt-1">{{ $rps->updated_at?->format('d/m/Y H:i') }}</small>
                        @else
                        <span class="badge bg-label-danger"><i class="bx bx-x me-1"></i>Belum upload</span>
                        @endif
                    </td>
                    <td>{{ $rps?->dosen?->nama ?: '-' }}</td>
                    <td class="text-center">
                        @if($rps)
                        <a href="{{ route('dosen.sekprodi.rps.download', $rps) }}"
                            class="btn btn-sm btn-primary">
                            <i class="bx bx-download me-1"></i>Download RPS
                        </a>
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Tidak ada mata kuliah yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
