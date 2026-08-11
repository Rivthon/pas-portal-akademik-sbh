@extends('layouts.master')
@section('title', 'Edit Dosen')
@section('content')
<div class="row">
    <div class="col-lg-10 col-md-12 mx-auto">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Edit Data Dosen</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> Ada beberapa masalah dengan input Anda.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                <form action="{{ route('admin.dosen.update', $dosen->dosen_id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <!-- Nama -->
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label"><strong>Nama: <span class="text-danger">*</span></strong></label>
                            <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $dosen->nama) }}" required>
                            @error('nama')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- NIDN -->
                        <div class="col-md-6 mb-3">
                            <label for="nidn" class="form-label"><strong>NIDN: <span class="text-danger">*</span></strong></label>
                            <input type="text" name="nidn" id="nidn" class="form-control @error('nidn') is-invalid @enderror" value="{{ old('nidn', $dosen->nidn) }}" required>
                            @error('nidn')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jenis Kelamin -->
                        <div class="col-md-6 mb-3">
                            <label for="jenis_kelamin" class="form-label"><strong>Jenis Kelamin: <span class="text-danger">*</span></strong></label>
                            <select name="jenis_kelamin" id="jenis_kelamin" class="form-control @error('jenis_kelamin') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Jenis Kelamin</option>
                                <option value="L" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $dosen->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                            @error('jenis_kelamin')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Jurusan -->
                        <div class="col-md-6 mb-3">
                            <label for="jurusan_id" class="form-label"><strong>Jurusan: <span class="text-danger">*</span></strong></label>
                            <select name="jurusan_id" id="jurusan_id" class="form-control @error('jurusan_id') is-invalid @enderror" required>
                                <option value="" disabled selected>Pilih Jurusan</option>
                                @foreach($programStudi as $j)
                                <option value="{{ $j->jurusan_id }}" {{ old('jurusan_id', $dosen->jurusan_id) == $j->jurusan_id ? 'selected' : '' }}>
                                    {{ $j->nama }}
                                </option>
                                @endforeach
                            </select>
                            @error('jurusan_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label"><strong>Email: <span class="text-danger">*</span></strong></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $dosen->email) }}" required>
                            @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label"><strong>Password:</strong></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan jika tidak ingin diubah">
                            @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tempat Lahir -->
                        <div class="col-md-6 mb-3">
                            <label for="tempat" class="form-label"><strong>Tempat Lahir:</strong></label>
                            <input type="text" name="tempat" id="tempat" class="form-control @error('tempat') is-invalid @enderror" value="{{ old('tempat', $dosen->tempat) }}">
                            @error('tempat')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div class="col-md-6 mb-3">
                            <label for="tanggal_lahir" class="form-label"><strong>Tanggal Lahir:</strong></label>
                            <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-control @error('tanggal_lahir') is-invalid @enderror" value="{{ old('tanggal_lahir', isset($dosen->tanggal_lahir) ? \Carbon\Carbon::parse($dosen->tanggal_lahir)->format('Y-m-d') : '') }}">
                            @error('tanggal_lahir')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- No Telp -->
                        <div class="col-md-6 mb-3">
                            <label for="no_telp" class="form-label"><strong>Nomor Telepon:</strong></label>
                            <input type="number" name="no_telp" id="no_telp" class="form-control @error('no_telp') is-invalid @enderror" value="{{ old('no_telp', $dosen->no_telp) }}">
                            @error('no_telp')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Avatar -->
                        <div class="col-md-6 mb-3">
                            <label for="avatar" class="form-label"><strong>Avatar:</strong></label>
                            <input type="file" name="avatar" id="avatar" class="form-control @error('avatar') is-invalid @enderror">
                            @if ($dosen->avatar)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $dosen->avatar) }}" alt="Avatar" width="100" class="img-thumbnail" onerror="this.src='{{ asset('dashboard_assets/assets/img/avatars/1.png') }}'">
                            </div>
                            @endif
                            @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Alamat -->
                        <div class="col-md-12 mb-3">
                            <label for="alamat" class="form-label"><strong>Alamat:</strong></label>
                            <textarea name="alamat" id="alamat" class="form-control @error('alamat') is-invalid @enderror" rows="3">{{ old('alamat', $dosen->alamat) }}</textarea>
                            @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Buttons -->
                        <div class="col-12 text-center mt-4">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="fa-solid fa-floppy-disk"></i> Update
                            </button>
                            <a href="{{ route('admin.dosen.index') }}" class="btn btn-secondary">
                                <i class="fa-solid fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
$(document).ready(function() {
    $('form').on('submit', function(e) {
        let isValid = true;

        // Reset semua state invalid sebelumnya
        $(this).find('.is-invalid').removeClass('is-invalid');
        $(this).find('.invalid-feedback.js-feedback').remove();

        // Cek semua field required
        $(this).find('[required]').each(function() {
            const $field = $(this);
            const value = $field.val();

            if (!value || value === '') {
                isValid = false;
                $field.addClass('is-invalid');

                // Tambahkan pesan jika belum ada
                if ($field.next('.invalid-feedback').length === 0) {
                    const label = $field.closest('.mb-3').find('label').text().replace('*', '').replace(':', '').trim();
                    $field.after('<div class="invalid-feedback js-feedback">' + label + ' wajib diisi.</div>');
                }
            }
        });

        if (!isValid) {
            e.preventDefault();

            // Scroll ke field pertama yang error
            const $firstError = $(this).find('.is-invalid').first();
            if ($firstError.length) {
                $('html, body').animate({
                    scrollTop: $firstError.offset().top - 100
                }, 300);
                $firstError.focus();
            }

            // Tampilkan notifikasi
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Form Belum Lengkap',
                    text: 'Silakan isi semua field yang bertanda bintang (*) terlebih dahulu.',
                    confirmButtonText: 'Mengerti'
                });
            }
        }
    });

    // Hapus state invalid saat user mulai mengisi
    $(document).on('input change', '[required]', function() {
        if ($(this).val()) {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback.js-feedback').remove();
        }
    });
});
</script>
@endpush