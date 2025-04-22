

    <div class="row">
          @forelse ($kurikulums as $key => $kurikulum)
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    <!-- Program Studi -->
                    <div class="flex-grow-1">
                        <h5 class="mb-1">{{ $kurikulum->programStudi->nama ?? '-' }}</h5>
                         <p class="mb-1 text-bold">
                                <i class="bx bx-book"></i>Semester  {{ $kurikulum->mataKuliah->smt ?? '-' }}
                        </p>
                        <p class="mb-1 text-muted">

                            <i class="bx bx-book"></i> {{ $kurikulum->mataKuliah->matakuliah_id ?? '-' }} - {{ $kurikulum->mataKuliah->nama ?? '-'  }}
                        </p>
                         <p class="mb-1 text-muted">
                            <i class="bx bx-calendar"></i> {{ $kurikulum->tahunAjaran->nama ?? '-'  }} - {{ $kurikulum->tahunAjaran->semester ?? '-'  }}
                        </p>
                        {{-- <span class="badge bg-{{ $kurikulum->status_aktif === 'aktif' ? 'success' : 'danger' }}">
                            {{ ucfirst($kurikulum->status_aktif) }}
                        </span> --}}
                    </div>

                    <!-- Actions -->
                    <div>
                        <form action="{{ route('admin.kurikulum.destroy', $kurikulum->kurikulum_id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')

                            @can('kurikulum-edit')
                            <a class="btn btn-primary btn-sm mb-1" href="{{ route('admin.kurikulum.edit', $kurikulum->kurikulum_id) }}">
                                <i class="bx bx-edit"></i>
                            </a>
                            @endcan

                            @can('kurikulum-delete')
                            <button type="submit" class="btn btn-danger btn-sm mb-1" onclick="return confirm('Apakah Anda yakin?')">
                                <i class="bx bx-trash"></i>
                            </button>
                            @endcan

                        </form>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning text-center">
                Tidak ada data ditemukan.
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if ($kurikulums->hasPages())
    <div class="d-flex justify-content-center mt-3 pagination-links">
        {{ $kurikulums->links('pagination::bootstrap-4') }}
    </div>
@endif
