@extends('layouts.dosen')
@section('title', 'Kelola Soal Tugas PG')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
        <a href="{{ route('dosen.lms.kelola', $tugas->jadwal) }}" class="btn btn-sm btn-label-secondary">
            <i class="bx bx-arrow-back me-1"></i>Kembali ke Kelas
        </a>
        <a href="{{ route('dosen.lms.tugas.pengumpulan', $tugas) }}" class="btn btn-sm btn-primary">
            <i class="bx bx-bar-chart me-1"></i>Pengumpulan & Nilai
        </a>
    </div>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 d-flex flex-wrap justify-content-between gap-3">
            <div>
                <span class="badge bg-label-info mb-2">TUGAS PILIHAN GANDA A-E</span>
                <h3 class="fw-bold mb-1">{{ $tugas->judul }}</h3>
                <p class="text-muted mb-0">{{ $tugas->jadwal?->kurikulum?->mataKuliah?->nama }} &bull; {{ $tugas->soal->count() }} soal</p>
            </div>
            <div class="text-end"><span class="badge bg-label-secondary">Nilai Maksimal {{ $tugas->nilai_maksimal }}</span><small class="d-block text-muted mt-2">Deadline {{ $tugas->deadline?->translatedFormat('d M Y H:i') }}</small></div>
        </div>
    </div>

    @if($tugas->pengumpulan_count > 0)
        <div class="alert alert-warning"><i class="bx bx-lock me-1"></i>Soal dikunci karena sudah ada mahasiswa yang mengerjakan.</div>
    @endif

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm sticky-xl-top" style="top:1rem">
                <div class="card-header bg-white"><h5 class="fw-bold mb-0"><i class="bx bx-plus-circle text-primary me-2"></i>Tambah Soal</h5></div>
                <div class="card-body">
                    <form action="{{ route('dosen.lms.tugas.soal.store', $tugas) }}" method="POST">@csrf
                        <div class="mb-3"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="4" required {{ $tugas->pengumpulan_count ? 'disabled' : '' }}></textarea></div>
                        @foreach(['A','B','C','D','E'] as $label)
                            <div class="input-group mb-2"><span class="input-group-text">{{ $label }}</span><input name="opsi[]" class="form-control" required {{ $tugas->pengumpulan_count ? 'disabled' : '' }}></div>
                        @endforeach
                        <div class="row g-2 mt-3 mb-3">
                            <div class="col-6"><label class="form-label">Kunci</label><select name="kunci_jawaban" class="form-select" {{ $tugas->pengumpulan_count ? 'disabled' : '' }}>@foreach(['A','B','C','D','E'] as $i=>$label)<option value="{{ $i }}">{{ $label }}</option>@endforeach</select></div>
                            <div class="col-3"><label class="form-label">Bobot</label><input type="number" step=".01" min=".01" name="bobot" value="1" class="form-control" required {{ $tugas->pengumpulan_count ? 'disabled' : '' }}></div>
                            <div class="col-3"><label class="form-label">Urutan</label><input type="number" min="1" name="urutan" value="{{ $tugas->soal->count()+1 }}" class="form-control" {{ $tugas->pengumpulan_count ? 'disabled' : '' }}></div>
                        </div>
                        <button class="btn btn-primary w-100" {{ $tugas->pengumpulan_count ? 'disabled' : '' }}><i class="bx bx-plus me-1"></i>Tambahkan Soal</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            @forelse($tugas->soal as $soal)
                <div class="card border-0 shadow-sm mb-3"><div class="card-body p-4">
                    <div class="d-flex justify-content-between gap-3">
                        <div class="d-flex gap-3"><span class="avatar-initial rounded-circle bg-label-primary question-number">{{ $loop->iteration }}</span><div><span class="badge bg-label-secondary mb-2">Bobot {{ $soal->bobot }}</span><h6 class="fw-bold mb-2">{!! nl2br(e($soal->pertanyaan)) !!}</h6></div></div>
                        @if(!$tugas->pengumpulan_count)<div class="d-flex gap-1"><button class="btn btn-sm btn-icon btn-label-warning" data-bs-toggle="modal" data-bs-target="#editSoal{{ $soal->soal_id }}"><i class="bx bx-edit"></i></button><form method="POST" action="{{ route('dosen.lms.tugas.soal.destroy', $soal) }}" onsubmit="return confirm('Hapus soal ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-icon btn-label-danger"><i class="bx bx-trash"></i></button></form></div>@endif
                    </div>
                    <div class="row g-2 mt-2">@foreach($soal->opsi as $i=>$opsi)<div class="col-md-6"><div class="border rounded p-2 {{ (int)$i === (int)$soal->kunci_jawaban ? 'border-success bg-label-success' : '' }}"><strong>{{ chr(65+$i) }}.</strong> {{ $opsi }} @if((int)$i === (int)$soal->kunci_jawaban)<i class="bx bx-check float-end"></i>@endif</div></div>@endforeach</div>
                </div></div>
                <div class="modal fade" id="editSoal{{ $soal->soal_id }}" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><form action="{{ route('dosen.lms.tugas.soal.update', $soal) }}" method="POST">@csrf @method('PUT')
                    <div class="modal-header"><h5 class="modal-title">Edit Soal Tugas</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><div class="mb-3"><label class="form-label">Pertanyaan</label><textarea name="pertanyaan" class="form-control" rows="3" required>{{ $soal->pertanyaan }}</textarea></div>@foreach(['A','B','C','D','E'] as $i=>$label)<div class="input-group mb-2"><span class="input-group-text">{{ $label }}</span><input name="opsi[]" value="{{ $soal->opsi[$i] ?? '' }}" class="form-control" required></div>@endforeach<div class="row g-3 mt-1"><div class="col-md-4"><label class="form-label">Kunci</label><select name="kunci_jawaban" class="form-select">@foreach(['A','B','C','D','E'] as $i=>$label)<option value="{{ $i }}" @selected((int)$soal->kunci_jawaban === $i)>{{ $label }}</option>@endforeach</select></div><div class="col-md-4"><label class="form-label">Bobot</label><input type="number" step=".01" min=".01" name="bobot" value="{{ $soal->bobot }}" class="form-control" required></div><div class="col-md-4"><label class="form-label">Urutan</label><input type="number" min="1" name="urutan" value="{{ $soal->urutan }}" class="form-control"></div></div></div>
                    <div class="modal-footer"><button class="btn btn-primary">Simpan Perubahan</button></div>
                </form></div></div></div>
            @empty
                <div class="card border-0 shadow-sm"><div class="card-body text-center py-5 text-muted"><i class="bx bx-list-plus" style="font-size:4rem"></i><h5>Belum ada soal tugas</h5><p class="mb-0">Tambahkan soal beserta pilihan A sampai E.</p></div></div>
            @endforelse
        </div>
    </div>
</div>
<style>.question-number{width:42px;height:42px;display:inline-flex;align-items:center;justify-content:center;flex:none}</style>
@endsection
