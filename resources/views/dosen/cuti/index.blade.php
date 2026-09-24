@extends('layouts.dosen')
@section('title', 'Cuti Mahasiswa Bimbingan')
@section('content')
<div class="card border-0 shadow-sm mb-4 overflow-hidden"><div class="card-body p-4 text-white" style="background:linear-gradient(135deg,#3156a3,#5a72d8)"><h4 class="text-white mb-1"><i class="bx bx-calendar-check me-2"></i>Cuti Mahasiswa Bimbingan</h4><p class="mb-0 text-white-50">Persetujuan Anda diperlukan sebelum pengajuan diteruskan kepada Kaprodi.</p></div></div>
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="card border-0 shadow-sm"><div class="card-header bg-transparent d-flex justify-content-between align-items-center"><h5 class="mb-0">Daftar Pengajuan</h5><div class="btn-group"><a href="?status=menunggu" class="btn btn-sm {{ $status==='menunggu'?'btn-primary':'btn-outline-primary' }}">Menunggu</a><a href="?status=selesai" class="btn btn-sm {{ $status==='selesai'?'btn-primary':'btn-outline-primary' }}">Riwayat</a></div></div><div class="card-body">
@forelse($pengajuan as $item)<div class="border rounded-3 p-3 mb-3"><div class="d-flex flex-wrap justify-content-between gap-2"><div><h6 class="mb-1">{{ $item->mahasiswa?->nama }}</h6><span class="text-muted small">{{ $item->mahasiswa?->nim }} · {{ $item->programStudi?->nama }} · {{ $item->tahunAkademik?->nama }}</span></div><span class="badge bg-label-{{ $item->status_color }} align-self-start">{{ $item->status_label }}</span></div><hr><p>{{ $item->alasan }}</p>
@if($item->lampiran)<a target="_blank" href="{{ route('dosen.cuti.attachment',$item) }}" class="btn btn-sm btn-outline-primary"><i class="bx bx-paperclip"></i> Lampiran</a>@endif
@if($item->status === \App\Models\PengajuanCuti::MENUNGGU_DOSPEM)<form method="POST" action="{{ route('dosen.cuti.decide',$item) }}" class="mt-3">@csrf @method('PATCH')<textarea name="catatan" class="form-control mb-2" rows="2" maxlength="2000" placeholder="Catatan (wajib bila ditolak)"></textarea><div class="d-flex gap-2"><button name="keputusan" value="setujui" class="btn btn-success"><i class="bx bx-check"></i> Setujui & Teruskan</button><button name="keputusan" value="tolak" class="btn btn-outline-danger"><i class="bx bx-x"></i> Tolak</button></div></form>
@elseif($item->catatan_dospem)<div class="bg-light rounded p-2 mt-3 small"><strong>Catatan Anda:</strong> {{ $item->catatan_dospem }}</div>@endif</div>
@empty<div class="text-center text-muted py-5">Tidak ada pengajuan pada kategori ini.</div>@endforelse
{{ $pengajuan->links('pagination::bootstrap-5') }}</div></div>
@endsection
