<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Evaluasi Dosen Mengajar (EDOM)</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: -10px;
            padding: 0;
        }

        /* Header Styling */
        .header {
            text-align: center;
            width: 100%;
        }

        .header img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0;
            padding: 0;
        }

        .header-text {
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 1.2rem;
            font-weight: bold;
            margin: 0;
            color: #333;
            text-align: center;
        }

        .subtitle {
            font-size: 0.8rem;
            margin: 5px 0 0;
            color: #666;
            text-align: center;
        }

        /* Info table styling */
        .info {
            margin: 15px 30px;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            color: #333;
        }

        .info-table th,
        .info-table td {
            text-align: left;
            padding: 5px 15px;
        }

        .info-table th {
            width: 25%;
            font-weight: bold;
            background-color: #ffffff;
        }

        .info-table td {
            background-color: #ffffff;
        }

        /* Data table */
        .data-table {
            width: calc(100% - 60px);
            margin: 10px 30px;
            border-collapse: collapse;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        .data-table th {
            background-color: #e8e8e8;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 18px 30px 5px 30px;
            padding-bottom: 4px;
            border-bottom: 1px solid #333;
        }

        .summary-box {
            border: 1px solid #333;
            padding: 8px 15px;
            margin: 10px 30px;
            background-color: #f9f9f9;
            font-size: 12px;
        }

        .summary-box p {
            margin: 3px 0;
        }

        .saran-list {
            margin: 5px 30px;
            font-size: 11px;
        }

        .saran-item {
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.8em;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    {{-- Header dengan Gambar Kop sama seperti UTS/UAS --}}
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image">
    </div>

    <div class="header-text">
        <h1 class="title">Laporan Evaluasi Dosen Mengajar (EDOM)</h1>
        @if(isset($tahunAjaranNama))
        <p class="subtitle">Tahun Ajaran {{ $tahunAjaranNama }}</p>
        @endif
    </div>

    <div class="info">
        <table class="info-table">
            <tr>
                <th>Program Studi</th>
                <td>: {{ $programStudiNama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Mata Kuliah</th>
                <td>: {{ $mataKuliahNama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Dosen</th>
                <td>: {{ $dosenNama ?? '-' }}</td>
            </tr>
            <tr>
                <th>Metode Ajar</th>
                <td>: {{ $jenisDosen ? ucfirst($jenisDosen) : '-' }}</td>
            </tr>
            <tr>
                <th>Tanggal Cetak</th>
                <td>: {{ now()->format('d F Y H:i') }}</td>
            </tr>
        </table>
    </div>

    {{-- Tabel Hasil Penilaian --}}
    @if(isset($penilaian) && $penilaian->count() > 0)
    <div class="section-title">Hasil Penilaian</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Aspek Penilaian</th>
                <th style="width: 90px;">Rata-rata Nilai</th>
                <th style="width: 90px;">Kriteria</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rataRataNilai as $evaluasiId => $nilai)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $penilaian->firstWhere('evaluasi_id', $evaluasiId)?->evaluasi?->nama ?? '-' }}</td>
                <td class="text-center">{{ number_format($nilai, 2) }}</td>
                <td class="text-center">{{ $kriteria($nilai) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Ringkasan --}}
    @php
        $rataRataTotal = $rataRataNilai->avg();
    @endphp
    <div class="summary-box">
        <p><strong>Rata-rata Keseluruhan:</strong> {{ number_format($rataRataTotal, 2) }} — <strong>{{ $kriteria($rataRataTotal) }}</strong></p>
        <p><strong>Jumlah Aspek Penilaian:</strong> {{ $rataRataNilai->count() }}</p>
    </div>
    @else
    <p style="margin: 15px 30px;"><em>Tidak ada data penilaian.</em></p>
    @endif

    {{-- List Saran (tanpa nomor) --}}
    @if(isset($sarans) && $sarans->count() > 0)
    <div class="section-title">Saran Mahasiswa</div>
    <div class="saran-list">
        @foreach ($sarans as $saran)
            @if($saran->saran && $saran->saran !== '-')
            <div class="saran-item">• {{ $saran->saran }}</div>
            @endif
        @endforeach
    </div>
    @endif

    {{-- List Mahasiswa --}}
    @if(isset($sarans) && $sarans->count() > 0)
    <div class="section-title">Mahasiswa Yang Sudah Mengisi EDOM</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35px;">No</th>
                <th>Nama Mahasiswa</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($sarans->unique('mahasiswa_id') as $saran)
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td>{{ $saran->mahasiswa->nama ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Footer --}}
    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ $settings ? $settings->footer_name : 'STIKes Bogor Husada' }}.
            {{ $settings ? $settings->copyright : 'All rights reserved.' }}
        </p>
    </div>
</body>

</html>
