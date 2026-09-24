@extends('layouts.mahasiswa')
@section('title', 'Pengajuan Cuti Akademik')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#696cff)">
            <h4 class="text-white mb-1"><i class="bx bx-calendar-minus me-2"></i>Pengajuan Cuti Akademik</h4>
            <p class="mb-0 text-white-50">Pengajuan diproses berurutan oleh Dosen Pembimbing, Kaprodi, lalu BAAK.</p>
        </div>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><strong>Pengajuan belum dapat diproses.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <div class="row g-4">
        <div class="col-lg-5"><div class="card border-0 shadow-sm h-100"><div class="card-header bg-transparent"><h5 class="mb-0">Form Pengajuan</h5></div><div class="card-body">
            <div class="rounded bg-label-primary p-3 mb-3"><div class="small text-muted">Tahun Akademik</div><strong>{{ $activeTa?->nama ?? 'Belum tersedia' }}</strong><hr class="my-2"><div class="small text-muted">Dosen Pembimbing</div><strong>{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</strong></div>
            @if($pengajuanAktif)<div class="alert alert-{{ $pengajuanAktif->status_color }} mb-0">Pengajuan periode ini sudah tercatat: <strong>{{ $pengajuanAktif->status_label }}</strong>.</div>
            @elseif(strtolower((string)$mahasiswa->status_mhs) !== 'aktif')<div class="alert alert-warning mb-0">Form hanya tersedia bagi mahasiswa dengan status aktif.</div>
            @else<form method="POST" action="{{ route('mahasiswa.cuti.store') }}" enctype="multipart/form-data">@csrf
                <div class="mb-3"><label class="form-label fw-semibold">Alasan Cuti</label><textarea name="alasan" class="form-control" rows="6" minlength="10" maxlength="5000" required>{{ old('alasan') }}</textarea></div>
                <div class="mb-3"><label class="form-label fw-semibold">Lampiran Pendukung <span class="text-muted">(opsional)</span></label><input type="file" name="lampiran" class="form-control" accept=".pdf,.jpg,.jpeg,.png"><div class="form-text">PDF/JPG/PNG, maksimal 5 MB. File tersimpan secara privat.</div></div>
                <button class="btn btn-primary w-100"><i class="bx bx-send me-1"></i>Kirim ke Dosen Pembimbing</button>
            </form>@endif
        </div></div></div>
        <div class="col-lg-7"><div class="card border-0 shadow-sm"><div class="card-header bg-transparent"><h5 class="mb-0">Riwayat Pengajuan</h5></div><div class="card-body">
            @forelse($pengajuan as $item)<div class="border rounded-3 p-3 mb-3">
                <div class="d-flex flex-wrap justify-content-between gap-2 mb-2"><div><strong>{{ $item->tahunAkademik?->nama }}</strong><div class="small text-muted">{{ $item->diajukan_pada?->format('d M Y H:i') }}</div></div><span class="badge bg-label-{{ $item->status_color }}">{{ $item->status_label }}</span></div>
                <p class="mb-2">{{ $item->alasan }}</p>
                @if($item->lampiran)<a class="btn btn-sm btn-outline-primary mb-2" target="_blank" href="{{ route('mahasiswa.cuti.attachment', $item) }}"><i class="bx bx-paperclip"></i> Lihat lampiran</a>@endif
                @foreach([['Dospem',$item->catatan_dospem],['Kaprodi',$item->catatan_kaprodi],['BAAK',$item->catatan_baak]] as [$label,$note])@if($note)<div class="small bg-light rounded p-2 mt-2"><strong>Catatan {{ $label }}:</strong> {{ $note }}</div>@endif @endforeach
                @if($item->status === \App\Models\PengajuanCuti::MENUNGGU_DOSPEM)<form method="POST" action="{{ route('mahasiswa.cuti.cancel', $item) }}" class="mt-3" onsubmit="return confirm('Batalkan pengajuan cuti ini?')">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-danger">Batalkan Pengajuan</button></form>@endif
            </div>@empty<div class="text-center text-muted py-5"><i class="bx bx-file fs-1"></i><p class="mb-0">Belum ada riwayat pengajuan cuti.</p></div>@endforelse
        </div></div></div>
    </div>
</div>
@endsection
