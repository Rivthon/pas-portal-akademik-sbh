<div class="row">
    @forelse ($jadwalUts as $key => $item)
    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
        <div class="card">
            <div class="card-body d-flex align-items-center">
                <!-- Program Studi -->
                <div class="flex-grow-1">
                    <h5 class="mb-1">{{ $item->programStudi->nama ?? '-' }} <span class="text-muted">{{
                            $item->mataKuliah->smt ?? '-' }}({{
                            $item->mataKuliah->semester ?? '-' }})</span></h5>
                    {{-- <p class="mb-1 text-bold">
                        <i class="bx bx-book"></i>Semester {{ $item->mataKuliah->smt ?? '-' }}
                    </p> --}}
                    <p class="mb-1 text-muted">

                        <i class="bx bx-book"></i> {{ $item->mataKuliah->matakuliah_id ?? '-' }} - {{
                        $item->mataKuliah->nama ?? '-' }}
                    </p>
                    <p class="mb-1 text-muted">
                        <strong>Tanggal:</strong> {{ $item->tanggal ?? '-' }}-({{ $item->jam ?? '-' }})<br>
                        <strong>Tempat:</strong> {{ $item->ruangan->nama ?? '-' }}<br>
                    </p>

                </div>

                <!-- Actions -->
                <div>
                    <form action="{{ route('admin.jadwal-uts.destroy', $item->id) }}" method="POST"
                        style="display:inline;">
                        @csrf
                        @method('DELETE')

                        @can('jadwal-uts-edit')
                        <a class="btn btn-primary btn-sm mb-1" href="{{ route('admin.jadwal-uts.edit', $item->id) }}">
                            <i class="bx bx-edit"></i>
                        </a>
                        @endcan

                        @can('jadwal-uts-delete')
                        <button type="submit" class="btn btn-danger btn-sm mb-1"
                            onclick="return confirm('Apakah Anda yakin?')">
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
@if ($jadwalUts->hasPages())
<div class="d-flex justify-content-center mt-3 pagination-links">
    {{ $jadwalUts->links('pagination::bootstrap-4') }}
</div>
@endif