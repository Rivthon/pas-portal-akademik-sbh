@extends('layouts.master')
@section('title', 'Laporan Penggunaan PAS')

@section('content')
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#6979f8)">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div><span class="badge bg-white text-primary mb-2">LAPORAN PENGGUNAAN</span><h3 class="text-white fw-bold mb-1">Aktivitas Dosen & Mahasiswa</h3><p class="text-white-50 mb-0">Ringkasan penggunaan PAS berdasarkan aktivitas yang tercatat pada sistem.</p></div>
            <i class="bx bx-line-chart d-none d-md-block" style="font-size:5rem;opacity:.3"></i>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4"><div class="card-body">
    <form method="GET" action="{{ route('admin.usage-report.index') }}" class="row g-3 align-items-end">
        <div class="col-lg-2 col-md-4"><label class="form-label">Dari Tanggal</label><input type="date" name="start_date" class="form-control" value="{{ request('start_date', $startDate->toDateString()) }}"></div>
        <div class="col-lg-2 col-md-4"><label class="form-label">Sampai Tanggal</label><input type="date" name="end_date" class="form-control" value="{{ request('end_date', $endDate->toDateString()) }}"></div>
        <div class="col-lg-2 col-md-4"><label class="form-label">Jenis Pengguna</label><select name="user_type" class="form-select"><option value="">Dosen & Mahasiswa</option><option value="dosen" @selected(request('user_type') === 'dosen')>Dosen</option><option value="mahasiswa" @selected(request('user_type') === 'mahasiswa')>Mahasiswa</option></select></div>
        <div class="col-lg-2 col-md-4"><label class="form-label">Program Studi</label><select name="program_studi_id" class="form-select"><option value="">Semua Program Studi</option>@foreach($programStudis as $program)<option value="{{ $program->jurusan_id }}" @selected((string) request('program_studi_id') === (string) $program->jurusan_id)>{{ $program->nama }}</option>@endforeach</select></div>
        <div class="col-lg-3 col-md-5"><label class="form-label">Cari Pengguna</label><input type="search" name="search" class="form-control" value="{{ request('search') }}" placeholder="Nama, NIM, NIDN, atau email"></div>
        <div class="col-lg-1 col-md-3"><button class="btn btn-primary w-100"><i class="bx bx-filter-alt"></i></button></div>
    </form>
</div></div>

<div class="row g-3 mb-4">
    @php($cards = [
        ['label' => 'Total Aktivitas', 'value' => $statistics['total_aktivitas'], 'icon' => 'bx-pulse', 'color' => 'primary'],
        ['label' => 'Pengguna Aktif', 'value' => $statistics['pengguna_aktif'], 'icon' => 'bx-user-check', 'color' => 'success'],
        ['label' => 'Dosen Aktif', 'value' => $statistics['dosen_aktif'], 'icon' => 'bx-chalkboard', 'color' => 'info'],
        ['label' => 'Mahasiswa Aktif', 'value' => $statistics['mahasiswa_aktif'], 'icon' => 'bxs-graduation', 'color' => 'warning'],
        ['label' => 'Total Login', 'value' => $statistics['total_login'], 'icon' => 'bx-log-in-circle', 'color' => 'secondary'],
    ])
    @foreach($cards as $card)
    <div class="col-lg col-md-4 col-6"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="d-flex justify-content-between gap-2"><div><small class="text-muted">{{ $card['label'] }}</small><h3 class="mb-0">{{ number_format($card['value']) }}</h3></div><span class="avatar-initial rounded bg-label-{{ $card['color'] }} p-2"><i class="bx {{ $card['icon'] }} fs-4"></i></span></div></div></div></div>
    @endforeach
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white"><h5 class="mb-0">Aktivitas Fitur Terbanyak</h5></div>
    <div class="card-body d-flex flex-wrap gap-2">@forelse($topActivities as $activity)<span class="badge bg-label-{{ $activity->user_type === 'dosen' ? 'info' : 'primary' }} p-2">{{ ucfirst($activity->user_type) }} &bull; {{ str_replace('_', ' ', ucfirst($activity->aktivitas)) }}: {{ number_format($activity->total) }}</span>@empty<span class="text-muted">Belum ada aktivitas pada periode ini.</span>@endforelse</div>
</div>

@if($dosen)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="bx bx-chalkboard me-2"></i>Penggunaan Dosen</h5><span class="badge bg-label-info">{{ number_format($dosen->total()) }} dosen</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>No</th><th>Dosen</th><th>Program Studi</th><th>Login</th><th>Aktivitas</th><th>Hari Aktif</th><th>Terakhir Aktif</th></tr></thead>
        <tbody>@forelse($dosen as $item)<tr><td>{{ $dosen->firstItem() + $loop->index }}</td><td><strong>{{ $item->nama }}</strong><small class="d-block text-muted">NIDN: {{ $item->nidn ?: '-' }}</small></td><td>{{ $item->program_studi_nama ?: '-' }}</td><td>{{ number_format($item->total_login) }}</td><td><span class="badge bg-label-primary">{{ number_format($item->total_aktivitas) }}</span></td><td>{{ number_format($item->hari_aktif) }} hari</td><td>@if($item->aktivitas_terakhir){{ \Carbon\Carbon::parse($item->aktivitas_terakhir)->format('d/m/Y H:i') }}@else<span class="badge bg-label-secondary">Tidak ada aktivitas</span>@endif</td></tr>@empty<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada dosen yang sesuai filter.</td></tr>@endforelse</tbody>
    </table></div>
    @if($dosen->hasPages())<div class="card-footer bg-white">{{ $dosen->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endif

@if($mahasiswa)
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center"><h5 class="mb-0"><i class="bx bxs-graduation me-2"></i>Penggunaan Mahasiswa</h5><span class="badge bg-label-primary">{{ number_format($mahasiswa->total()) }} mahasiswa</span></div>
    <div class="table-responsive"><table class="table table-hover align-middle mb-0">
        <thead class="table-light"><tr><th>No</th><th>Mahasiswa</th><th>Program Studi</th><th>Status</th><th>Login</th><th>Aktivitas</th><th>Hari Aktif</th><th>Terakhir Aktif</th></tr></thead>
        <tbody>@forelse($mahasiswa as $item)<tr><td>{{ $mahasiswa->firstItem() + $loop->index }}</td><td><strong>{{ $item->nama }}</strong><small class="d-block text-muted">{{ $item->nim }} &bull; Semester {{ $item->semester ?: '-' }}</small></td><td>{{ $item->program_studi_nama ?: '-' }}</td><td><span class="badge bg-label-{{ strtolower((string) $item->status_mhs) === 'aktif' ? 'success' : 'secondary' }}">{{ ucfirst($item->status_mhs ?: '-') }}</span></td><td>{{ number_format($item->total_login) }}</td><td><span class="badge bg-label-primary">{{ number_format($item->total_aktivitas) }}</span></td><td>{{ number_format($item->hari_aktif) }} hari</td><td>@if($item->aktivitas_terakhir){{ \Carbon\Carbon::parse($item->aktivitas_terakhir)->format('d/m/Y H:i') }}@else<span class="badge bg-label-secondary">Tidak ada aktivitas</span>@endif</td></tr>@empty<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada mahasiswa yang sesuai filter.</td></tr>@endforelse</tbody>
    </table></div>
    @if($mahasiswa->hasPages())<div class="card-footer bg-white">{{ $mahasiswa->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endif

<div class="alert alert-info mt-4 mb-0"><i class="bx bx-info-circle me-1"></i>Laporan dihitung dari aktivitas yang tercatat pada periode {{ $startDate->format('d/m/Y') }} sampai {{ $endDate->format('d/m/Y') }}. Pengguna tanpa aktivitas tetap ditampilkan dengan nilai nol.</div>
@endsection
