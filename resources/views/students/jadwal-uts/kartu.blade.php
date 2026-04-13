<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Ujuan Tengah Semester</title>
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
    <h1 class="title">Kartu Ujian Tengah Semester</h1>
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
        @foreach($jadwalUts as $key => $item)
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
    <strong style="display: block; text-align: left; font-weight: bold; margin-bottom: 5px;">Perhatian:</strong>
    <ul style="text-align: left; font-weight: bold; margin-bottom: 20px;">
        <li>Peserta Ujian wajib menggunakan atribut lengkap</li>
        <li>Untuk PRODI Kebidanan menggunakan Seragam dan Name Tag</li>
        <li>Untuk PRODI Farmasi & Gizi Menggunakan Almamater dan Name Tag</li>
        <li>Membawa alat tulis sendiri</li>
        <li>Kartu Ujian ini wajib dibawa setiap pelaksanaan ujian</li>
    </ul>

    <table style="width: 100%; border: none; margin-top: 20px;">
        <tr>
            <!-- Kolom QR Code -->
            <td style="width: 50%; text-align: left; vertical-align: top; border: none;">
                @if(isset($qrBase64))
                <p style="font-size: 10px; margin-bottom: 5px;">Scan kode QR ini untuk verifikasi<br>keabsahan data Kartu Ujian:</p>
                <img src="data:image/png;base64,{{ $qrBase64 }}" alt="QR Code Verifikasi" style="width: 80px; height: 80px; border: 1px solid #ccc; padding: 3px;">
                @endif
            </td>
            
            <!-- Kolom Tanda Tangan -->
            <td style="width: 50%; text-align: right; vertical-align: top; border: none;">
                <p style="margin-bottom: 5px;">Ketua Program Studi</p>
                @php
                $jurusanId = auth('mahasiswa')->user()->jurusan_id;
                @endphp

                @if ($jurusanId == 13211)
                <img src="{{ public_path('assets/img/header/gizi.png') }}" alt="Tanda Tangan Gizi" style="height: 60px; margin: 5px 0;">
                @elseif ($jurusanId == 48201)
                <img src="{{ public_path('assets/img/header/farmasi.png') }}" alt="Tanda Tangan Farmasi" style="height: 60px; margin: 5px 0;">
                @elseif ($jurusanId == 15401)
                <img src="{{ public_path('assets/img/header/bidan.png') }}" alt="Tanda Tangan Bidan" style="height: 60px; margin: 5px 0;">
                @else
                <p style="margin: 20px 0;">(Tidak ada TTD)</p>
                @endif
                
                <p style="text-decoration: underline; font-weight: bold; margin-top: 5px;">
                    {{ auth('mahasiswa')->user()->programStudi->kaprod }}
                </p>
            </td>
        </tr>
    </table>
</div>
