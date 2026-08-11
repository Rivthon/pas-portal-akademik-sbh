@extends('layouts.dosen')
@section('title', 'Buat Permintaan Helpdesk')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-9">
        <div class="d-flex align-items-center gap-3 mb-4">
            <a href="{{ route('dosen.permintaan.index') }}" class="btn btn-icon btn-outline-secondary">
                <i class="bx bx-arrow-back"></i>
            </a>
            <div>
                <h4 class="mb-1">Buat Permintaan Helpdesk</h4>
                <p class="text-muted mb-0">Jelaskan kendala dengan rinci agar tim ICT dapat membantu lebih cepat.</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>Periksa kembali data berikut:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('dosen.permintaan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="jenis_permintaan" class="form-label">Jenis Permintaan</label>
                            <select name="jenis_permintaan" id="jenis_permintaan" class="form-select" required>
                                <option value="">Pilih jenis</option>
                                <option value="bug" @selected(old('jenis_permintaan') === 'bug')>Laporan Bug</option>
                                <option value="fitur" @selected(old('jenis_permintaan') === 'fitur')>Permintaan Fitur</option>
                                <option value="akses" @selected(old('jenis_permintaan') === 'akses')>Kesulitan Akses</option>
                                <option value="lainnya" @selected(old('jenis_permintaan') === 'lainnya')>Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="prioritas" class="form-label">Prioritas</label>
                            <select name="prioritas" id="prioritas" class="form-select" required>
                                <option value="">Pilih prioritas</option>
                                <option value="rendah" @selected(old('prioritas') === 'rendah')>Rendah</option>
                                <option value="sedang" @selected(old('prioritas') === 'sedang')>Sedang</option>
                                <option value="tinggi" @selected(old('prioritas') === 'tinggi')>Tinggi</option>
                                <option value="urgen" @selected(old('prioritas') === 'urgen')>Sangat Mendesak</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="judul" class="form-label">Judul Permintaan</label>
                            <input type="text" name="judul" id="judul" class="form-control" value="{{ old('judul') }}"
                                maxlength="255" required placeholder="Contoh: Tidak dapat membuka materi LMS">
                        </div>
                        <div class="col-12">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea name="deskripsi" id="deskripsi" class="form-control" rows="6" required
                                placeholder="Tuliskan halaman yang bermasalah, langkah yang dilakukan, dan pesan error yang muncul.">{{ old('deskripsi') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label for="file_lampiran" class="form-label">Lampiran <span class="text-muted">(opsional)</span></label>
                            <input type="file" name="file_lampiran" id="file_lampiran" class="form-control"
                                accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.zip">
                            <div class="form-text">PDF, gambar, dokumen, atau ZIP. Maksimal 2 MB.</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('dosen.permintaan.index') }}" class="btn btn-outline-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary"><i class="bx bx-send me-1"></i>Kirim Permintaan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
