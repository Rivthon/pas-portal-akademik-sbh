@extends('layouts.master')
@section('title', 'Validasi Cuti Mahasiswa')
@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden"><div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#696cff)"><h4 class="text-white mb-1"><i class="bx bx-calendar-check me-2"></i>Validasi Cuti Mahasiswa</h4><p class="mb-0 text-white-50">Persetujuan BAAK merupakan tahap final dan otomatis mengubah status mahasiswa menjadi Cuti.</p></div></div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card border-0 shadow-sm mb-4"><div class="card-body"><form method="GET" class="row g-2 align-items-end">
        <div class="col-md-4"><label class="form-label">Cari Mahasiswa</label><input name="search" value="{{ $search }}" class="form-control" placeholder="Nama atau NIM"></div>
        <div class="col-md-3"><label class="form-label">Tahun Akademik</label><select name="ta_id" class="form-select"><option value="">Semua Tahun</option>@foreach($tahunAkademik as $ta)<option value="{{ $ta->ta_id }}" @selected((int)$taId===(int)$ta->ta_id)>{{ $ta->nama }}</option>@endforeach</select></div>
        <div class="col-md-3"><label class="form-label">Tahap</label><select name="status" class="form-select"><option value="menunggu" @selected($status==='menunggu')>Menunggu BAAK</option><option value="proses" @selected($status==='proses')>Masih di Dospem/Kaprodi</option><option value="selesai" @selected($status==='selesai')>Selesai</option><option value="semua" @selected($status==='semua')>Semua</option></select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bx bx-filter"></i> Filter</button></div>
    </form></div></div>
    <div class="card border-0 shadow-sm"><div class="card-body">
        @forelse($pengajuan as $item)<div class="border rounded-3 p-3 mb-3"><div class="d-flex flex-wrap justify-content-between gap-2"><div><h6 class="mb-1">{{ $item->mahasiswa?->nama }} <span class="text-muted fw-normal">({{ $item->mahasiswa?->nim }})</span></h6><div class="small text-muted">{{ $item->programStudi?->nama }} · {{ $item->tahunAkademik?->nama }} · Diajukan {{ $item->diajukan_pada?->format('d M Y H:i') }}</div></div><span class="badge bg-label-{{ $item->status_color }} align-self-start">{{ $item->status_label }}</span></div><hr><p>{{ $item->alasan }}</p>
        <div class="row g-2"><div class="col-md-6"><div class="bg-light rounded p-2 small h-100"><strong>Dospem:</strong> {{ $item->dospem?->nama }}<br>{{ $item->catatan_dospem ?: 'Disetujui tanpa catatan' }}</div></div><div class="col-md-6"><div class="bg-light rounded p-2 small h-100"><strong>Kaprodi:</strong> {{ $item->kaprodi?->nama ?? '-' }}<br>{{ $item->catatan_kaprodi ?: ($item->diproses_kaprodi_pada ? 'Disetujui tanpa catatan' : 'Belum diproses') }}</div></div></div>
        @if($item->lampiran)<a target="_blank" href="{{ route('admin.cuti.attachment',$item) }}" class="btn btn-sm btn-outline-primary mt-3"><i class="bx bx-paperclip"></i> Lihat Lampiran</a>@endif
        @if($item->status === \App\Models\PengajuanCuti::MENUNGGU_BAAK && auth()->user()->can('cuti-validasi'))<form method="POST" action="{{ route('admin.cuti.decide',$item) }}" class="mt-3">@csrf @method('PATCH')<textarea name="catatan" class="form-control mb-2" rows="2" maxlength="2000" placeholder="Catatan BAAK (wajib bila ditolak)"></textarea><div class="d-flex flex-wrap gap-2"><button name="keputusan" value="setujui" class="btn btn-success" onclick="return confirm('Sahkan cuti dan ubah status mahasiswa menjadi Cuti?')"><i class="bx bx-check-circle"></i> Validasi & Aktifkan Cuti</button><button name="keputusan" value="tolak" class="btn btn-outline-danger"><i class="bx bx-x"></i> Tolak</button></div></form>@elseif($item->catatan_baak)<div class="alert alert-secondary py-2 mt-3 mb-0"><strong>Catatan BAAK:</strong> {{ $item->catatan_baak }}</div>@endif
        </div>@empty<div class="text-center text-muted py-5"><i class="bx bx-file fs-1"></i><p>Tidak ada pengajuan pada filter ini.</p></div>@endforelse
        {{ $pengajuan->links('pagination::bootstrap-5') }}
    </div></div>
</div>
@endsection
