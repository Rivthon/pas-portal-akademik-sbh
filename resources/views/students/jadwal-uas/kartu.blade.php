<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujuan Akhir Semester</title>
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
<!-- Header dengan Gambar -->
<div class="header">
    <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image">
</div>
<div class="header-text">
    <h1 class="title">Kartu Ujian Akhir Semester</h1>
    <p class="subtitle">Tahun Ajaran {{ $activeTA->nama }} ({{ $activeTA->semester }}) </p>
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
    <thead>
        <tr>
            <th style="width: 5%; text-align: center;">No</th>
            <th style="width: 25%; text-align: center;">Mata Kuliah</th>

            <th style="width: 15%; text-align: center;">Tanggal</th>
            <th style="width: 15%; text-align: center;">Waktu</th>
            <th style="width: 15%; text-align: center;">Ruangan</th>
            <th style="width: 10%; text-align: center;">Pengawas</th>
            <th style="width: 10%; text-align: center;">Paraf</th>
        </tr>
    </thead>
    <tbody>
        @foreach($jadwalUas as $key => $item)
        <tr>
            <td style="text-align: center;">{{ $key + 1 }}</td>
            <td style="text-align: left;">{{ $item->mataKuliah->nama }}</td>

            <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</td>
            <td style="text-align: center;">{{ \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') }} - {{
                \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') }}</td>
            <td style="text-align: center;">{{ $item->ruangan->nama ?? 'Tidak ada data' }}</td>
            <td style="text-align: center;">__________</td>
            <td style="text-align: center;">__________</td>
        </tr>
        @endforeach
    </tbody>
    </tr>
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
        @php
        $jurusanId = auth('mahasiswa')->user()->jurusan_id;
        @endphp

        @if ($jurusanId == 13211)
        <img src="{{ public_path('assets/img/header/gizi.png') }}" alt="Header Gizi" style="height: 60px;">
        @elseif ($jurusanId == 48201)
        <img src="{{ public_path('assets/img/header/farmasi.png') }}" alt="Header Farmasi" style="height: 60px;">
        @elseif ($jurusanId == 15401)
        <img src="{{ public_path('assets/img/header/bidan.png') }}" alt="Header Bidan" style="height: 60px;">
        @else
        <p>Jurusan tidak dikenali</p> @endif
        <div class="placeholder"></div>

        <p>
            {{ auth('mahasiswa')->user()->programStudi->kaprod }}
        </p>
    </div>
</div>