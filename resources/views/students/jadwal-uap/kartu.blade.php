<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujuan Akhir Praktik</title>
    @php
    $settings = \App\Models\Setting::first();
    @endphp
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: -10px;
            /* Margin bisa diatur minus */
            padding: 0;
            /* Tidak ada padding */
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
            margin: 20px 30px;
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
            padding: 10px 15px;
        }

        .info-table th {
            width: 25%;
            font-weight: bold;
            background-color: #ffffff;
            /* Warna latar untuk header kolom */
        }

        .info-table td {
            background-color: #ffffff;
            /* Putih bersih */
        }

        .info-table tr:nth-child(even) td {
            background-color: #ffffff;
            /* Striping pada baris genap */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;

        }

        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        .footer {
            margin-top: 30px;
            text-align: right;
            /* Posisi tanda tangan di sebelah kanan */
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td {
            padding: 5px;
            text-align: left;
        }

        .info-table th {
            color: black;
            /* Warna teks header */
        }
    </style>
</head>
<div class="header">
    @if($logoBase64)
    <div class="logo-container">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image">
    </div>
    @endif
    <div class="header-text">
        <h1 class="title">Kartu Ujian Akhir Praktik</h1>
        <h3 class="subtitle">{{ $activeTA->nama }} - {{ $activeTA->semester }}</h3>
    </div>
</div>
<div class="info">
    <table class="info-table">
        <tr>
            <th>Nama</th>
            <td>{{ auth('mahasiswa')->user()->nama }}</td>
            <th>NIM</th>
            <td>{{ auth('mahasiswa')->user()->nim }}</td>
        </tr>
        <tr>
            <th>Prog. Studi</th>
            <td>{{ auth('mahasiswa')->user()->programStudi->nama }}</td>
            <th>Semester</th>
            <td>{{ auth('mahasiswa')->user()->semester }}</td>
        </tr>
    </table>
</div>

<table class="table">
    <tr>
        <th>#</th>
        <th>Tahun Akademik</th>
        <th>Program Studi</th>
        <th>Nama</th>
        <th>Jam Mulai</th>
        <th>Jam Selesai</th>
        <th>Tanggal</th>
    </tr>
    </thead>
    <tbody>
        @forelse($jadwalUap as $index => $jadwal)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $jadwal->tahunAkademik->nama ?? '-' }}</td>
            <td>{{ $jadwal->programStudi->nama ?? '-' }}</td>
            <td>{{ $jadwal->nama }}</td>
            <td>{{ \Carbon\Carbon::parse($jadwal->jam_mulai)->format('H:i') }}</td>
            <td>{{ \Carbon\Carbon::parse($jadwal->jam_selesai)->format('H:i') }}</td>
            <td>{{ \Carbon\Carbon::parse($jadwal->tanggal)->translatedFormat('d F Y') }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">Tidak ada data jadwal UAP.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    <strong style="display: block; text-align: left; font-weight: bold; margin-bottom: 10px;">Perhatian:</strong>
    <ul style="text-align: left; font-weight: bold;">
        <li>Peserta Ujian wajib menggunakan atribut lengkap</li>
        <li>Untuk PRODI Kebidanan menggunakan Seragam dan Name Tag</li>
        <li>Untuk PRODI Farmasi & Gizi Menggunakan Almamater dan Name Tag</li>
        <li>Membawa alat tulis sendiri</li>
        <li>Kartu Ujian ini wajib dibawa setiap pelaksanaan ujian</li>
    </ul>

    <div style="text-align: right; margin-top: 30px;">
        <p>Ketua Program Studi

        </p>
        @if ($ttd)
        <img src="data:image/png;base64,{{ $ttd }}" alt="Tanda Tangan Kaprodi" style="height: 60px;">
        @else
        <div class="placeholder"></div>
        @endif
        <p>
            {{ auth('mahasiswa')->user()->programStudi->kaprod }}
        </p>
    </div>
</div>