<table>
    <thead>
        <tr>
            <th colspan="10" style="font-size: 16pt; font-weight: bold; text-align: center;">SEKOLAH TINGGI ILMU KESEHATAN BOGOR HUSADA</th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center;">Laporan Nilai Matakuliah</th>
        </tr>
        <tr><td colspan="10"></td></tr>
        <tr>
            <td colspan="2"><strong>Mata Kuliah</strong></td>
            <td colspan="3">{{ $mataKuliah->nama ?? '-' }}</td>
            <td colspan="2"><strong>SKS / SMT</strong></td>
            <td colspan="3">{{ $mataKuliah->sks ?? '-' }} / {{ $mataKuliah->smt ?? '-' }}</td>
        </tr>
        <tr>
            <td colspan="2"><strong>Program Studi</strong></td>
            <td colspan="3">{{ \App\Models\ProgramStudi::find($mataKuliah->jurusan_id)?->nama_prodi ?? 'Umum' }}</td>
            <td colspan="2"><strong>Tahun Ajaran</strong></td>
            <td colspan="3">{{ $tahunAjaran->nama ?? '-' }} ({{ $tahunAjaran->semester ?? '-' }})</td>
        </tr>
        <tr><td colspan="10"></td></tr>
        <tr>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">No</th>
            <th style="font-weight: bold; text-align: left; border: 1px solid #000;">Nama Mahasiswa</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">NIM</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">UTS ({{ $konfigurasi['bobot']['uts'] }}%)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">UAS ({{ $konfigurasi['bobot']['uas'] }}%)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Tugas ({{ $konfigurasi['bobot']['tugas'] }}%)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Absen ({{ $konfigurasi['bobot']['absensi'] }}%)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Praktik ({{ $konfigurasi['bobot']['praktik'] }}%)</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Nilai Akhir</th>
            <th style="font-weight: bold; text-align: center; border: 1px solid #000;">Huruf Mutu</th>
        </tr>
    </thead>
    <tbody>
        @forelse($mahasiswa as $index => $mhs)
            <tr>
                <td style="text-align: center; border: 1px solid #000;">{{ $index + 1 }}</td>
                <td style="text-align: left; border: 1px solid #000;">{{ $mhs->nama }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->nim }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->uts ?? '0' }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->uas ?? '0' }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->tugas ?? '0' }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->absen ?? '0' }}</td>
                <td style="text-align: center; border: 1px solid #000;">{{ $mhs->praktik ?? '0' }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000;">{{ $mhs->akhir ?? '0' }}</td>
                <td style="text-align: center; font-weight: bold; border: 1px solid #000;">{{ $mhs->khs ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align: center;">Tidak ada data mahasiswa.</td>
            </tr>
        @endforelse
    </tbody>
</table>
