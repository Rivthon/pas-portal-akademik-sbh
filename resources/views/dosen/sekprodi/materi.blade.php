@extends('layouts.dosen')
@section('title', 'Materi Program Studi')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#6979f8)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <span class="badge bg-white text-primary mb-2">SEKPRODI</span>
                <h3 class="text-white fw-bold mb-1">Materi Program Studi</h3>
                <p class="text-white-50 mb-0">Lihat dan unduh materi yang diunggah dosen pengajar pada program studi yang Anda kelola.</p>
            </div>
            <i class="bx bx-library d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white p-4">
        <form method="GET" action="{{ route('dosen.sekprodi.materi.index') }}" class="row g-3 align-items-end">
            <div class="col-lg-3 col-md-6">
                <label class="form-label">Program Studi</label>
                <select name="program_studi_id" class="form-select">
                    @foreach($managedPrograms as $program)
                    <option value="{{ $program->jurusan_id }}" @selected((string) $selectedProgramId === (string) $program->jurusan_id)>
                        {{ $program->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Tahun Akademik</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                    <option value="{{ $ta->ta_id }}" @selected((string) $selectedTaId === (string) $ta->ta_id)>
                        {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Kelas</label>
                <select name="jenis_kelas" class="form-select">
                    <option value="">Semua Kelas</option>
                    <option value="reguler" @selected(request('jenis_kelas') === 'reguler')>Reguler A</option>
                    <option value="karyawan" @selected(request('jenis_kelas') === 'karyawan')>Reguler B</option>
                </select>
            </div>
            <div class="col-lg-2 col-md-6">
                <label class="form-label">Dosen</label>
                <select name="dosen_id" class="form-select">
                    <option value="">Semua Dosen</option>
                    @foreach($dosenPengunggah as $dosen)
                    <option value="{{ $dosen->dosen_id }}" @selected((string) request('dosen_id') === (string) $dosen->dosen_id)>
                        {{ $dosen->nama }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 col-md-9">
                <label class="form-label">Pencarian</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Materi atau mata kuliah">
            </div>
            <div class="col-lg-1 col-md-3">
                <button class="btn btn-primary w-100" title="Terapkan filter"><i class="bx bx-search"></i></button>
            </div>
        </form>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Materi</th>
                    <th>Mata Kuliah</th>
                    <th>Dosen</th>
                    <th>Pertemuan</th>
                    <th>Diunggah</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($materi as $item)
                <tr>
                    <td>{{ $materi->firstItem() + $loop->index }}</td>
                    <td>
                        <strong>{{ $item->judul }}</strong>
                        <small class="d-block text-muted">{{ strtoupper($item->tipe ?: 'FILE') }}</small>
                    </td>
                    <td>
                        {{ $item->jadwal?->kurikulum?->mataKuliah?->nama ?? '-' }}
                        <small class="d-block text-muted">
                            {{ $item->jadwal?->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }}
                            &bull; {{ jenis_kelas_label($item->jadwal?->jenis_kelas) }}
                        </small>
                    </td>
                    <td>{{ $item->dosen?->nama ?? '-' }}</td>
                    <td>
                        {{ $item->pertemuan?->topik ?: '-' }}
                        @if($item->pertemuan?->tanggal_pertemuan)
                        <small class="d-block text-muted">{{ \Carbon\Carbon::parse($item->pertemuan->tanggal_pertemuan)->format('d/m/Y') }}</small>
                        @endif
                    </td>
                    <td>{{ $item->created_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td class="text-center">
                        @if($item->file)
                        <a href="{{ route('dosen.sekprodi.materi.download', $item) }}" class="btn btn-sm btn-primary">
                            <i class="bx bx-download me-1"></i>Download
                        </a>
                        @elseif($item->youtube_url)
                        <a href="{{ Str::startsWith($item->youtube_url, ['http://', 'https://']) ? $item->youtube_url : 'https://'.$item->youtube_url }}"
                            target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-danger">
                            <i class="bx bxl-youtube me-1"></i>Buka
                        </a>
                        @else
                        <span class="badge bg-label-secondary">Tidak ada file</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Belum ada materi yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($materi->hasPages())
    <div class="card-footer bg-white">{{ $materi->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
