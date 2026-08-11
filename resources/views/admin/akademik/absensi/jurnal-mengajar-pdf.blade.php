<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jurnal Mengajar {{ $jadwal->kurikulum?->mataKuliah?->nama }}</title>
    <style>
        @page { margin: 24px 28px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #222; }
        .header { text-align: center; margin-bottom: 10px; }
        .header img { width: 100%; max-height: 100px; object-fit: contain; }
        h2 { text-align: center; margin: 8px 0 12px; font-size: 17px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .info-table td { padding: 2px 5px; vertical-align: top; }
        .info-label { width: 13%; color: #555; }
        .bap-table, .rekap-table { width: 100%; border-collapse: collapse; }
        .bap-table th, .bap-table td, .rekap-table th, .rekap-table td {
            border: 1px solid #333; padding: 5px 4px;
        }
        .bap-table th, .rekap-table th { background: #edf0f5; text-align: center; }
        .center { text-align: center; }
        .page-break { page-break-after: always; }
        .lecturer-list { margin: 0; padding-left: 15px; }
    </style>
</head>
<body>
    <section class="page-break">
        <div class="header">
            <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header">
        </div>
        <h2>JURNAL MENGAJAR / LAPORAN BAP</h2>

        <table class="info-table">
            <tr>
                <td class="info-label">Mata Kuliah</td><td>: <strong>{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</strong></td>
                <td class="info-label">Program Studi</td><td>: {{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Kode / SKS</td><td>: {{ $jadwal->kurikulum?->mataKuliah?->matakuliah_id ?? '-' }} / {{ $jadwal->kurikulum?->mataKuliah?->sks ?? '-' }} SKS</td>
                <td class="info-label">Semester / Kelas</td><td>: {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }} / {{ ucfirst($jadwal->jenis_kelas ?: '-') }}</td>
            </tr>
            <tr>
                <td class="info-label">Tahun Akademik</td><td>: {{ $jadwal->tahunAjaran?->nama ?? '-' }} {{ $jadwal->tahunAjaran?->semester ? '('.$jadwal->tahunAjaran->semester.')' : '' }}</td>
                <td class="info-label">Dosen Pengampu</td>
                <td>:
                    @foreach($dosenMatakuliah as $dosen)
                        {{ $dosen->nama }}{{ !$loop->last ? '; ' : '' }}
                    @endforeach
                </td>
            </tr>
        </table>

        <table class="bap-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Dosen Pengajar</th>
                    <th>Hari / Tanggal</th>
                    <th>Pokok Pembahasan</th>
                    <th>Sub Pokok Pembahasan</th>
                    <th>Jam Mulai</th>
                    <th>Jam Selesai</th>
                    <th>Durasi</th>
                    <th>Jml Mhs</th>
                    <th>H</th>
                    <th>S</th>
                    <th>I</th>
                    <th style="color:#b42318">A</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwal->pertemuan as $pertemuan)
                    @php
                        $mulai = strtotime((string) $pertemuan->jam_mulai);
                        $selesai = strtotime((string) $pertemuan->jam_selesai);
                        $durasi = $mulai && $selesai && $selesai > $mulai
                            ? (int) (($selesai - $mulai) / 60)
                            : 0;
                        $hadir = $pertemuan->absensi->where('status', 'hadir')->count();
                        $sakit = $pertemuan->absensi->where('status', 'sakit')->count();
                        $izin = $pertemuan->absensi->where('status', 'izin')->count();
                        $alfa = $pertemuan->absensi->whereIn('status', ['tidak hadir', 'alpha', 'alpa', 'alfa'])->count();
                        $tanggalTimestamp = strtotime((string) $pertemuan->tanggal_pertemuan);
                        $namaHari = [
                            1 => 'Senin',
                            2 => 'Selasa',
                            3 => 'Rabu',
                            4 => 'Kamis',
                            5 => 'Jumat',
                            6 => 'Sabtu',
                            7 => 'Minggu',
                        ][(int) date('N', $tanggalTimestamp)] ?? '-';
                    @endphp
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $pertemuan->dosen?->nama ?? '-' }}</td>
                        <td>{{ $namaHari }}, {{ date('d/m/Y', $tanggalTimestamp) }}</td>
                        <td>{{ $pertemuan->topik ?: '-' }}</td>
                        <td>{{ $pertemuan->sub_topik ?: '-' }}</td>
                        <td class="center">{{ substr((string) $pertemuan->jam_mulai, 0, 5) }}</td>
                        <td class="center">{{ substr((string) $pertemuan->jam_selesai, 0, 5) }}</td>
                        <td class="center">{{ $durasi }} menit</td>
                        <td class="center">{{ $pertemuan->absensi->count() }}</td>
                        <td class="center">{{ $hadir }}</td>
                        <td class="center">{{ $sakit }}</td>
                        <td class="center">{{ $izin }}</td>
                        <td class="center" style="{{ $alfa > 0 ? 'background:#f8d7da;color:#b42318;font-weight:bold' : '' }}">{{ $alfa }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:6px;font-size:9px"><strong>Keterangan:</strong> H = Hadir, S = Sakit, I = Izin, <span style="color:#b42318;font-weight:bold">A = Alfa</span>. Maksimal 14 pertemuan yang sudah berlangsung.</div>
    </section>

    <section>
        <div class="header">
            <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header">
        </div>
        <h2>REKAP ABSENSI MAHASISWA</h2>
        <table class="info-table">
            <tr>
                <td class="info-label">Mata Kuliah</td><td>: <strong>{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</strong></td>
                <td class="info-label">Program Studi</td><td>: {{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Semester / Kelas</td><td>: {{ $jadwal->kurikulum?->mataKuliah?->smt ?? '-' }} / {{ ucfirst($jadwal->jenis_kelas ?: '-') }}</td>
                <td class="info-label">Jumlah Pertemuan</td><td>: {{ $totalPertemuan }}</td>
            </tr>
        </table>

        @include('dosen.absensi.partials.rekap-table')
        @include('dosen.absensi.partials.signatures')
    </section>
</body>
</html>
