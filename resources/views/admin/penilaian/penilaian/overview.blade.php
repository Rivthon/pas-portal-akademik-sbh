@extends('layouts.master')
@section('title', 'Ringkasan EDOM')
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="fw-bold text-primary mb-1">Ringkasan EDOM</h4>
        <p class="text-muted mb-0">Nilai dosen dan pengisian evaluasi dari seluruh tahun ajaran.</p>
    </div>
    <a href="{{ route('admin.penilaian.index') }}" class="btn btn-outline-primary"><i class="bx bx-list-ul me-1"></i>Detail per Mata Kuliah</a>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['Total kewajiban EDOM', number_format($statistics['total']), 'primary', 'bx-list-check'],
        ['Sudah terisi', number_format($statistics['filled']), 'success', 'bx-check-circle'],
        ['Belum terisi', number_format($statistics['unfilled']), 'warning', 'bx-time-five'],
        ['Persentase terisi', $statistics['percent'] !== null ? $statistics['percent'].'%' : '—', 'info', 'bx-pie-chart-alt-2'],
    ] as [$label, $value, $color, $icon])
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 shadow-sm border-0"><div class="card-body">
            <span class="badge bg-label-{{ $color }} mb-3"><i class="bx {{ $icon }} fs-4"></i></span>
            <div class="text-muted mb-1">{{ $label }}</div><h3 class="mb-0">{{ $value }}</h3>
        </div></div>
    </div>
    @endforeach
</div>

<div class="card shadow-sm mb-4">
    <div class="card-header"><h5 class="mb-1">Statistik Pengisian per Tahun Ajaran</h5>
        <small class="text-muted">Satu kewajiban = satu formulir mahasiswa untuk satu dosen, mata kuliah, metode, dan kelas.</small>
    </div>
    <div class="card-body">
        <p class="small text-muted">Terisi berarti sudah ada jawaban EDOM tersimpan. Jumlah kewajiban dihitung dari KRS, kelas mahasiswa, dan penugasan dosen yang tersedia saat ini. Perubahan kelas atau penugasan dapat memengaruhi persentase tahun sebelumnya. Statistik ini mencakup semua prodi dan tidak mengikuti filter daftar dosen di bawah.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>Tahun Ajaran</th><th>Total</th><th>Terisi</th><th>Belum</th><th style="min-width:200px">Persentase Terisi</th></tr></thead>
                <tbody>
                @forelse($statistics['years'] as $year)
                <tr>
                    <td>{{ $year['label'] }} @if($year['active'])<span class="badge bg-label-success ms-1">Aktif</span>@endif</td>
                    <td>{{ number_format($year['total']) }}</td>
                    <td class="text-success">{{ number_format($year['filled']) }}</td>
                    <td class="text-warning">{{ number_format($year['unfilled']) }}</td>
                    <td>
                        @if($year['percent'] !== null)
                            <div class="d-flex justify-content-between small mb-1"><span>{{ $year['percent'] }}% terisi</span><span class="text-muted">{{ round(100 - $year['percent'], 1) }}% belum</span></div>
                            <div class="progress" style="height:8px"><div class="progress-bar bg-success" role="progressbar" style="width:{{ $year['percent'] }}%" aria-valuenow="{{ $year['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div></div>
                        @else
                            <span class="text-muted">Belum ada kewajiban</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">Belum ada tahun ajaran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header"><h5 class="mb-1">Nilai EDOM Dosen — Seluruh Tahun Ajaran</h5>
        <small class="text-muted">Rata-rata seluruh skor jawaban yang tersimpan, skala 1–5. Dosen tanpa jawaban ditandai “Belum ada nilai”.</small>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.penilaian.overview') }}" class="row g-3 align-items-end mb-4">
            <div class="col-md-4"><label for="search" class="form-label">Nama / NIDN / Kode Dosen</label><input id="search" name="search" value="{{ $search }}" class="form-control" maxlength="100" placeholder="Cari dosen..."></div>
            <div class="col-md-3"><label for="jurusan_id" class="form-label">Prodi Asal Dosen</label><select id="jurusan_id" name="jurusan_id" class="form-select">
                <option value="">Semua Prodi</option>
                @foreach($programStudi as $prodi)<option value="{{ $prodi->jurusan_id }}" @selected($jurusanId == $prodi->jurusan_id)>{{ $prodi->nama }}</option>@endforeach
            </select></div>
            <div class="col-md-3"><label for="sort" class="form-label">Urutkan</label><select id="sort" name="sort" class="form-select">
                <option value="highest" @selected($sort === 'highest')>Nilai tertinggi</option><option value="lowest" @selected($sort === 'lowest')>Nilai terendah</option><option value="name" @selected($sort === 'name')>Nama A–Z</option>
            </select></div>
            <div class="col-md-2"><button class="btn btn-primary w-100" type="submit">Tampilkan</button></div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead><tr><th>No</th><th>Dosen</th><th>Prodi Asal</th><th>Nilai EDOM</th><th>Formulir</th><th>Mahasiswa Unik</th><th>TA / Semester</th></tr></thead>
                <tbody>
                @forelse($dosenRows as $dosen)
                <tr>
                    <td>{{ $dosenRows->firstItem() + $loop->index }}</td>
                    <td><div class="fw-semibold">{{ $dosen->nama }}</div><small class="text-muted">{{ $dosen->nidn ?: $dosen->kd_dosen ?: '—' }}</small></td>
                    <td>{{ $dosen->program_studi ?? '—' }}</td>
                    <td>@if($dosen->rata_rata_edom !== null)<span class="badge bg-label-primary fs-6">{{ number_format($dosen->rata_rata_edom, 2) }} / 5</span>@else<span class="text-muted">Belum ada nilai</span>@endif</td>
                    <td>{{ number_format($dosen->jumlah_formulir) }}</td>
                    <td>{{ number_format($dosen->jumlah_mahasiswa) }}</td>
                    <td>{{ $dosen->jumlah_tahun_ajaran }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Dosen tidak ditemukan.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $dosenRows->links('pagination::bootstrap-4') }}</div>
        <small class="text-muted">Identitas mahasiswa tidak ditampilkan. Satu mahasiswa dapat mengisi beberapa formulir pada mata kuliah atau tahun ajaran berbeda.</small>
    </div>
</div>
@endsection
