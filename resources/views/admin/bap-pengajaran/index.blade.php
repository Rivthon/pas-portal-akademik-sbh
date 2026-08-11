@extends('layouts.master')
@section('title', 'BAP Pengajaran Dosen')

@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden">
    <div class="card-body p-4 text-white" style="background: linear-gradient(135deg,#0f766e,#0d9488)">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-white text-success mb-2">BAUK</span>
                <h3 class="fw-bold text-white mb-1">BAP Pengajaran Dosen</h3>
                <p class="text-white-50 mb-0">
                    Rekap jumlah pertemuan mengajar untuk administrasi honorarium.
                </p>
            </div>
            <i class="bx bx-clipboard d-none d-md-block" style="font-size: 5rem; opacity: .3"></i>
        </div>
    </div>
</div>

<div class="alert alert-info border-0">
    <i class="bx bx-info-circle me-2"></i>
    Jumlah yang diakui dibatasi maksimal <strong>{{ $maksimalPertemuan }} pertemuan per kelas</strong>.
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tahun Akademik / Semester</label>
                <select name="ta_id" class="form-select">
                    @foreach($tahunAkademik as $ta)
                        <option value="{{ $ta->ta_id }}" @selected((string) $selectedTaId === (string) $ta->ta_id)>
                            {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cari Dosen</label>
                <input type="search" name="search" value="{{ request('search') }}" class="form-control"
                    placeholder="Nama, NIDN, atau kode dosen">
            </div>
            <div class="col-md-2">
                <button class="btn btn-primary w-100"><i class="bx bx-search me-1"></i>Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0"><i class="bx bx-user-voice text-primary me-2"></i>Daftar Pengajaran Dosen</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Dosen</th>
                    <th class="text-center">Kelas Sudah Berjalan</th>
                    <th class="text-center">Teori</th>
                    <th class="text-center">Praktik</th>
                    <th class="text-center">Jumlah Mengajar</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dosenList as $dosen)
                    <tr>
                        <td class="text-center">{{ $dosenList->firstItem() + $loop->index }}</td>
                        <td>
                            <strong>{{ $dosen->nama }}</strong>
                            <small class="d-block text-muted">
                                NIDN: {{ $dosen->nidn ?: '-' }} &bull; {{ $dosen->kd_dosen ?: '-' }}
                            </small>
                        </td>
                        <td class="text-center"><span class="badge bg-label-secondary">{{ $dosen->jumlah_kelas }}</span></td>
                        <td class="text-center"><span class="badge bg-label-primary">{{ $dosen->jumlah_teori }} kali</span></td>
                        <td class="text-center"><span class="badge bg-label-warning">{{ $dosen->jumlah_praktik }} kali</span></td>
                        <td class="text-center">
                            <span class="badge bg-label-success fs-6">{{ $dosen->jumlah_mengajar }} kali</span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex flex-wrap justify-content-center gap-1">
                                <a href="{{ route('admin.bap-pengajaran.show', ['dosen' => $dosen, 'ta_id' => $selectedTaId]) }}"
                                    class="btn btn-sm btn-primary">
                                    <i class="bx bx-show me-1"></i>Detail
                                </a>
                                <a href="{{ route('admin.bap-pengajaran.pdf', ['dosen' => $dosen, 'ta_id' => $selectedTaId]) }}"
                                    class="btn btn-sm btn-danger" title="Download BAP PDF">
                                    <i class="bx bxs-file-pdf me-1"></i>PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bx bx-folder-open fs-1"></i>
                            <h6 class="mt-2">Tidak ada dosen pada semester ini</h6>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($dosenList->hasPages())
        <div class="card-footer bg-white">{{ $dosenList->links('pagination::bootstrap-5') }}</div>
    @endif
</div>
@endsection
