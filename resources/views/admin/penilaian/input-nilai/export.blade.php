<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <title>Laporan Nilai Mahasiswa</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #333;
        }

        .header {
            text-align: center;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }

        .header-text {
            margin-top: 15px;
            margin-bottom: 20px;
        }

        .title {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
            color: #333;
            text-align: center;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 0.8rem;
            margin: 5px 0 0;
            color: #666;
            text-align: center;
        }

        .info-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .info-table td {
            padding: 4px;
        }

        .nilai-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        .nilai-table th,
        .nilai-table td {
            border: 1px solid #777;
            padding: 6px;
            text-align: center;
        }

        .nilai-table th {
            background-color: #f1f1f1;
            font-weight: bold;
        }

        .text-left {
            text-align: left !important;
        }

        .text-bold {
            font-weight: bold;
        }

        .ttd-box {
            float: right;
            width: 250px;
            text-align: center;
            margin-top: 30px;
            font-size: 10pt;
        }

        .ttd-space {
            height: 80px;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>

    <div class="header">
        @if(!isset($isExcel) || !$isExcel)
            <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Kop STIKES" />
        @else
            <div style="font-size: 16pt; font-weight: bold;">SEKOLAH TINGGI ILMU KESEHATAN BOGOR HUSADA</div>
            <div>Jl. Sholeh Iskandar No.4, Sindangbarang, Kota Bogor, Jawa Barat</div>
        @endif
    </div>

    <div class="header-text">
        <h1 class="title">Laporan Nilai Matakuliah</h1>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Mata Kuliah</strong></td>
            <td width="35%">: {{ $mataKuliah->nama ?? '-' }}</td>
            <td width="15%"><strong>SKS / SMT</strong></td>
            <td width="35%">: {{ $mataKuliah->sks ?? '-' }} / {{ $mataKuliah->smt ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Program Studi</strong></td>
            <td>: {{ \App\Models\ProgramStudi::find($mataKuliah->jurusan_id)?->nama_prodi ?? 'Umum' }}</td>
            <td><strong>Tahun Ajaran</strong></td>
            <td>: {{ $tahunAjaran->nama ?? '-' }} ({{ $tahunAjaran->semester ?? '-' }})</td>
        </tr>
    </table>

    <table class="nilai-table">
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" class="text-left" width="25%">Nama Mahasiswa</th>
                <th rowspan="2" width="10%">NIM</th>
                <th colspan="5">Komponen Nilai & Bobot</th>
                <th rowspan="2" width="8%">Nilai Akhir</th>
                <th rowspan="2" width="8%">Huruf Mutu</th>
            </tr>
            <tr>
                <th width="8%">UTS<br/>({{ $konfigurasi['bobot']['uts'] }}%)</th>
                <th width="8%">UAS<br/>({{ $konfigurasi['bobot']['uas'] }}%)</th>
                <th width="8%">Tugas<br/>({{ $konfigurasi['bobot']['tugas'] }}%)</th>
                <th width="8%">Absen<br/>({{ $konfigurasi['bobot']['absensi'] }}%)</th>
                <th width="8%">Praktik<br/>({{ $konfigurasi['bobot']['praktik'] }}%)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mahasiswa as $index => $mhs)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left">{{ $mhs->nama }}</td>
                    <td>{{ $mhs->nim }}</td>
                    <td>{{ $mhs->uts ?? '0' }}</td>
                    <td>{{ $mhs->uas ?? '0' }}</td>
                    <td>{{ $mhs->tugas ?? '0' }}</td>
                    <td>{{ $mhs->absen ?? '0' }}</td>
                    <td>{{ $mhs->praktik ?? '0' }}</td>
                    <td class="text-bold">{{ $mhs->akhir ?? '0' }}</td>
                    <td class="text-bold">{{ $mhs->khs ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">Tidak ada data mahasiswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="clearfix">
        <div class="ttd-box">
            <p>Bogor, {{ date('d F Y') }}</p>
            <p>Dosen Pengampu,</p>
            <div class="ttd-space"></div>
            <p>_________________________________</p>
        </div>
    </div>

</body>

</html>