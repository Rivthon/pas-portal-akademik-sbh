<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAP Pengajaran {{ $dosen->nama }}</title>
    <style>
        @page { margin: 22px 26px; }
        body { font-family: Arial, sans-serif; font-size: 9px; color: #222; }
        .header { text-align: center; margin-bottom: 8px; }
        .header img { width: 100%; max-height: 90px; object-fit: contain; }
        h2 { text-align: center; margin: 8px 0 12px; font-size: 16px; }
        h3 { margin: 12px 0 6px; font-size: 12px; }
        .info, .summary, .detail { width: 100%; border-collapse: collapse; }
        .info { margin-bottom: 10px; }
        .info td { padding: 2px 4px; vertical-align: top; }
        .label { width: 12%; color: #555; }
        .summary { margin-bottom: 12px; }
        .summary th, .summary td, .detail th, .detail td {
            border: 1px solid #444; padding: 4px; vertical-align: top;
        }
        .summary th, .detail th { background: #e9eef5; text-align: center; }
        .center { text-align: center; }
        .course-row td { background: #f5f7fa; font-weight: bold; }
        .muted { color: #666; font-size: 8px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header">
    </div>
    <h2>LAPORAN BAP PENGAJARAN DOSEN</h2>

    <table class="info">
        <tr>
            <td class="label">Nama Dosen</td><td>: <strong>{{ $dosen->nama }}</strong></td>
            <td class="label">Tahun Akademik</td><td>: {{ $selectedTa?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIDN</td><td>: {{ $dosen->nidn ?: '-' }}</td>
            <td class="label">Semester</td><td>: {{ ucfirst($selectedTa?->semester ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Kode Dosen</td><td>: {{ $dosen->kd_dosen ?: '-' }}</td>
            <td class="label">Dicetak</td><td>: {{ now()->translatedFormat('d F Y H:i') }}</td>
        </tr>
    </table>

    <table class="summary">
        <thead>
            <tr>
                <th>Jumlah Kelas</th>
                <th>Pengajaran Teori</th>
                <th>Pengajaran Praktik</th>
                <th>Total Pengajaran Diakui</th>
                <th>Batas per Kelas</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="center">{{ $jadwalTeori->count() + $jadwalPraktik->count() }}</td>
                <td class="center">{{ $jumlahTeori }} kali</td>
                <td class="center">{{ $jumlahPraktik }} kali</td>
                <td class="center"><strong>{{ $jumlahTeori + $jumlahPraktik }} kali</strong></td>
                <td class="center">{{ $maksimalPertemuan }} pertemuan</td>
            </tr>
        </tbody>
    </table>

    @foreach([
        ['title' => 'PENGAJARAN TEORI', 'items' => $jadwalTeori],
        ['title' => 'PENGAJARAN PRAKTIK', 'items' => $jadwalPraktik],
    ] as $section)
        <h3>{{ $section['title'] }}</h3>
        <table class="detail">
            <thead>
                <tr>
                    <th style="width:3%">No</th>
                    <th style="width:18%">Mata Kuliah / Prodi</th>
                    <th style="width:8%">Kelas</th>
                    <th style="width:8%">Pertemuan</th>
                    <th style="width:8%">Hari</th>
                    <th style="width:10%">Tanggal</th>
                    <th style="width:7%">Mulai</th>
                    <th style="width:7%">Selesai</th>
                    <th style="width:8%">Durasi</th>
                    <th style="width:8%">Metode</th>
                    <th>Topik</th>
                </tr>
            </thead>
            <tbody>
                @forelse($section['items'] as $jadwal)
                    @if($jadwal->pertemuan->isEmpty())
                        <tr class="course-row">
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>
                                {{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}
                                <div class="muted">{{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</div>
                            </td>
                            <td class="center">{{ ucfirst($jadwal->jenis_kelas ?: '-') }}</td>
                            <td class="center">0 / {{ $maksimalPertemuan }}</td>
                            <td colspan="7" class="center">Belum ada sesi mengajar yang tercatat.</td>
                        </tr>
                    @else
                        @foreach($jadwal->pertemuan as $pertemuan)
                            @php
                                $tanggal = $pertemuan->tanggal_pertemuan
                                    ? \Carbon\Carbon::parse($pertemuan->tanggal_pertemuan)
                                    : null;
                                $jamMulai = $pertemuan->jam_mulai ?: $jadwal->jam_mulai;
                                $jamSelesai = $pertemuan->jam_selesai ?: $jadwal->jam_selesai;
                                $durasiMenit = ($jamMulai && $jamSelesai)
                                    ? \Carbon\Carbon::parse($jamMulai)->diffInMinutes(\Carbon\Carbon::parse($jamSelesai), false)
                                    : null;
                                $durasi = $durasiMenit > 0 ? $durasiMenit.' menit' : '-';
                            @endphp
                            <tr>
                                @if($loop->first)
                                    <td class="center" rowspan="{{ $jadwal->pertemuan->count() }}">{{ $loop->parent->iteration }}</td>
                                    <td rowspan="{{ $jadwal->pertemuan->count() }}">
                                        <strong>{{ $jadwal->kurikulum?->mataKuliah?->nama ?? '-' }}</strong>
                                        <div class="muted">{{ $jadwal->kurikulum?->programStudi?->nama ?? '-' }}</div>
                                    </td>
                                    <td class="center" rowspan="{{ $jadwal->pertemuan->count() }}">{{ ucfirst($jadwal->jenis_kelas ?: '-') }}</td>
                                @endif
                                <td class="center">{{ $loop->iteration }} / {{ $jadwal->jumlah_diakui }}</td>
                                <td>{{ $tanggal?->translatedFormat('l') ?? '-' }}</td>
                                <td>{{ $tanggal?->format('d/m/Y') ?? '-' }}</td>
                                <td class="center">{{ $jamMulai ? substr($jamMulai, 0, 5) : '-' }}</td>
                                <td class="center">{{ $jamSelesai ? substr($jamSelesai, 0, 5) : '-' }}</td>
                                <td class="center">{{ $durasi }}</td>
                                <td class="center">{{ ucfirst($pertemuan->metode_pbm ?: 'offline') }}</td>
                                <td>{{ $pertemuan->topik ?: '-' }}</td>
                            </tr>
                        @endforeach
                    @endif
                @empty
                    <tr><td colspan="11" class="center">Tidak ada kelas pada semester ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    @endforeach
</body>
</html>
