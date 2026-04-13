@extends('layouts.master')
@section('title', 'Jadwal Ujian Akhir Program')
@section('content')

<!-- Header Info -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <div class="row g-0 align-items-center">
            <div class="col-md-7">
                <h5 class="card-title text-primary mb-3 fw-bold">Manajemen Jadwal Ujian Akhir Program (UAP)</h5>
                <p class="mb-4 text-muted" style="line-height: 1.6;">
                    Halaman ini berisi daftar jadwal pelaksanaan Ujian Akhir Program (UAP) mahasiswa. Anda dapat mengelola jadwal khusus ujian akhir ini dengan menetapkan waktu spesifik.
                    <br>
                    <span class="badge bg-label-primary mt-2 fs-6">Tahun Ajaran {{ $tahunAjaran->nama }} ({{ $tahunAjaran->semester }})</span>
                </p>
                
                @can('jadwal-uap-create')
                <div class="mt-3">
                    <a href="{{ route('admin.jadwal-uap.create') }}" class="btn btn-primary">
                        <i class="bx bx-plus me-1"></i> Tambah Jadwal Baru
                    </a>
                </div>
                @endcan
            </div>

            <div class="col-md-5 text-center mt-4 mt-md-0">
                <img src="../assets/img/illustrations/calender.png" class="img-fluid" alt="Illustration" style="max-height: 150px;">
            </div>
        </div>
    </div>
</div>

<!-- HASIL JADWAL -->
<div class="card mt-4 border-top border-5 border-info shadow-sm mb-5">
    <div class="card-header bg-white pb-0">
        <h5 class="card-title text-info mb-0"><i class="bx bx-list-ol me-1"></i> Daftar Jadwal UAP Aktif</h5>
    </div>
    <div class="card-body mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-bordered mt-2">
                <thead class="table-info border-bottom">
                    <tr>
                        <th class="text-center" style="width: 5%">#</th>
                        <th>Program Studi</th>
                        <th>Nama Kegiatan / Ujian</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Jam Mulai</th>
                        <th class="text-center">Jam Selesai</th>
                        <th class="text-center" style="width: 15%">Aksi</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($jadwalUap as $index => $jadwal)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $jadwal->programStudi->nama ?? '-' }}</td>
                        <td class="fw-semibold">{{ $jadwal->nama }}</td>
                        <td class="text-center text-primary fw-medium">{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}</td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-label-secondary">{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</span>
                        </td>
                        <td class="text-center">
                            @can('jadwal-uap-edit')
                            <a href="{{ route('admin.jadwal-uap.edit', $jadwal->id) }}" class="btn btn-sm btn-icon btn-outline-warning" title="Edit">
                                <i class="bx bx-edit"></i>
                            </a>
                            @endcan
                            
                            @can('jadwal-uap-delete')
                            <form action="{{ route('admin.jadwal-uap.destroy', $jadwal->id) }}" method="POST" class="d-inline form-delete">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-sm btn-icon btn-outline-danger btn-delete-uap" title="Hapus">
                                    <i class="bx bx-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4">
                            <i class="bx bx-info-circle text-muted mb-2" style="font-size: 2rem;"></i><br>
                            Tidak ada data jadwal UAP untuk tahun ajaran aktif ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('click', function (e) {
        let btn = e.target.closest('.btn-delete-uap');
        if (btn) {
            let form = btn.closest('.form-delete');
            Swal.fire({
                title: "Hapus Jadwal?",
                text: "Data jadwal UAP ini akan dihapus secara permanen!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#8592a3',
                confirmButtonText: "Ya, Hapus!",
                cancelButtonText: "Batal"
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Menghapus...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading() }
                    });
                    form.submit();
                }
            });
        }
    });

    @if(session('success'))
    Swal.fire({ title: 'Berhasil!', text: "{{ session('success') }}", icon: 'success', timer: 3000, showConfirmButton: false });
    @endif
    @if(session('error'))
    Swal.fire({ title: 'Gagal!', text: "{{ session('error') }}", icon: 'error', timer: 4000 });
    @endif
</script>
@endsection