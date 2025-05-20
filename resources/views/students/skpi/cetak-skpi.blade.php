<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan SKPI</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            margin: 20px;
        }

        .header {
            margin-bottom: 20px;
        }

        .header img {
            width: 80px;
            height: auto;
        }

        .header h1,
        .header h3 {
            margin: 5px 0;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
            /* Remove border from the table */
        }

        .header-table td {
            border: none;
            /* Remove border from table cells */
            text-align: center;
            vertical-align: middle;
        }

        .info-table {
            width: 100%;
            font-size: 12px;
            margin-bottom: 20px;
            border-collapse: collapse;
            background-color: transparent;
            /* Transparent background */
            border: none;
            /* Remove border from the table */
        }

        .info-table td {
            border: none;
            /* Remove border from table cells */
            padding: 6px 0;
            text-align: left;
        }

        /* General Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
            /* Warna border lebih soft */
        }

        th,
        td {
            padding: 10px;
            /* Padding lebih besar untuk kenyamanan */
            text-align: left;
        }

        /* Footer Styling */
        .footer {
            text-align: right;
            margin-top: 30px;
            font-size: 12px;
            /* Ukuran lebih konsisten */
            color: #555;
        }

        /* Warna Dinamis Berdasarkan Program Studi */
        .gizi {
            background-color: #fffbea;
            /* Kuning lembut */
            color: #a68c00;
            /* Teks kontras untuk kuning */
        }

        .farmasi {
            background-color: #f3e8ff;
            /* Ungu lembut */
            color: #6b3fa0;
            /* Teks kontras untuk ungu */
        }

        .bidan {
            background-color: #eaf6ff;
            /* Biru lembut */
            color: #005a9e;
            /* Teks kontras untuk biru */
        }

        /* Hover Effects */
        table tr:hover td {
            background-color: #f1f1f1;
            /* Highlight pada hover */
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            table {
                font-size: 12px;
            }

            th,
            td {
                padding: 8px;
            }

            .footer {
                font-size: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="{{ public_path('assets/img/header/header_kop_utama.jpg') }}" alt="Header Image"
            style="width: 100%; height: auto; margin-bottom: 20px;">
        <h1 style="text-align: center; margin: 0;">LAPORAN REKAP KEGIATAN SKPI MAHASISWA </h1>
        <br></br>
        {{-- <h3 style="text-align: center; margin: 5;">Tahun Ajaran : {{ $ta->nama }} - {{ $ta->semester }}</h3> --}}
        {{-- <table class="info-table" style="margin-top: 20px;">
            <tbody>
                <tr>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333; width: 20%;">Nama Mahasiswa</td>
                    <td style="padding: 6px 10px; color: #555; width: 30%;">: {{ $mahasiswa->nama }}</td>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333; width: 20%;">NIM</td>
                    <td style="padding: 6px 10px; color: #555; width: 30%;">: {{ $mahasiswa->nim }}</td>

                </tr>

                <tr>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333;">Semester</td>
                    <td style="padding: 6px 10px; color: #555;">
                        : {{ $mahasiswa->semester }} /
                        @php
                        $semester = $mahasiswa->semester;
                        if ($semester >= 1 && $semester <= 2) { $tingkat=1; } elseif ($semester>= 3 && $semester <= 4) {
                                $tingkat=2; } elseif ($semester>= 5 && $semester <= 6) { $tingkat=3; } elseif
                                    ($semester>= 7
                                    && $semester <= 8) { $tingkat=4; } else { $tingkat='Tidak Diketahui' ; } @endphp
                                        Tingkat {{ $tingkat }} </td>
                    <td style="padding: 6px 10px; font-weight: bold; color: #333;">Program Studi</td>
                    <td style="padding: 6px 10px; color: #555;">: {{ $mahasiswa->programStudi->nama }}</td>
                </tr>
            </tbody>
        </table> --}}
        @php
        $semester = $mahasiswa->semester;
        if ($semester >= 1 && $semester <= 2) { $tingkat=1; } elseif ($semester>= 3 && $semester <= 4) { $tingkat=2; }
                elseif ($semester>= 5 && $semester <= 6) { $tingkat=3; } elseif ($semester>= 7 && $semester <= 8) {
                        $tingkat=4; } else { $tingkat='Tidak Diketahui' ; } @endphp <table class="info-table"
                        style="margin-top: 20px; font-size: 13px;">
                        <tbody>
                            <tr>
                                <td style="padding: 6px 10px; font-weight: bold; color: #333; width: 35%;">NIM</td>
                                <td style="padding: 6px 10px; color: #555; width: 65%;">: {{ $mahasiswa->nim }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 10px; font-weight: bold; color: #333;">Nama Mahasiswa</td>
                                <td style="padding: 6px 10px; color: #555;">: {{ $mahasiswa->nama }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 10px; font-weight: bold; color: #333;">Semester</td>
                                <td style="padding: 6px 10px; color: #555;">: {{ $mahasiswa->semester }} / Tingkat {{
                                    $tingkat }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 10px; font-weight: bold; color: #333;">Program Studi</td>
                                <td style="padding: 6px 10px; color: #555;">: {{ $mahasiswa->programStudi->nama }}</td>
                            </tr>
                            <tr>
                                <td style="padding: 6px 10px; font-weight: bold; color: #333;">Tahun Ajaran</td>
                                <td style="padding: 6px 10px; color: #555;">: {{ $ta->nama }} - {{ $ta->semester }}</td>
                            </tr>
                        </tbody>
                        </table>
    </div>


    <h3>A. Kegiatan Wajib</h3>
    <table>
        <thead>
            <tr style="background-color: #f5f5f5; color: #333; text-align: center; font-size: 12px;">
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">No</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Nama
                    Kegiatan</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">
                    Keterangan / Prestasi</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Nilai
                </th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Validasi
                </th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($sertifikasi as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>Sertifikasi Profesi / Kompetensi</td>
                <td>{{ $item->nama_kegiatan }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            @foreach($ppsm as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>PPSM</td>
                <td>{{ $item->keterangan }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            @foreach($bahasa as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>Penguasaan Bahasa Asing</td>
                <td>{{ $item->nama_bahasa }} - {{ $item->skor }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            @foreach($p2mw as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>Program Wirausaha Mahasiswa (P2MW)</td>
                <td>{{ $item->nama_usaha }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            @foreach($pkm as $item)
            <tr>
                <td>{{ $no++ }}</td>
                <td>Program Kreativitas Mahasiswa (PKM)</td>
                <td>{{ $item->judul_kegiatan }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            <tr style="background-color: #f5f5f5;">
                <td colspan="3"><strong>Total Skor Kegiatan Wajib</strong></td>
                <td colspan="2"><strong>{{ $totalWajib }}</strong></td>
            </tr>
        </tbody>
    </table>

    <h3>B. Kegiatan Tambahan</h3>
    <table>
        <thead>
            <tr style="background-color: #f5f5f5; color: #333; text-align: center; font-size: 12px;">
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">No</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Nama
                    Kegiatan</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Jenis
                    Partisipasi</th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Tingkat
                </th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Nilai
                </th>
                <th style="padding: 10px; border: 1px solid #888; vertical-align: middle; text-align: center;">Validasi
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($tambahan as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->nama_kegiatan }}</td>
                <td>{{ $item->bentuk_kegiatan ?? $item->peran }}</td>
                <td>{{ $item->tingkat }}</td>
                <td>{{ $item->bobot }}</td>
                <td style="text-align: center;">V</td>
            </tr>
            @endforeach
            <tr style="background-color: #f5f5f5;">
                <td colspan="4"><strong>Total Skor Kegiatan Tambahan</strong></td>
                <td colspan="2"><strong>{{ $totalTambahan }}</strong></td>
            </tr>
        </tbody>
    </table>

    @php
    // Perhitungan Kriteria Kegiatan Wajib
    if ($totalWajib >= 151) {
    $kriteriaWajib = 'Unggul';
    } elseif ($totalWajib >= 126) {
    $kriteriaWajib = 'Sangat Baik';
    } elseif ($totalWajib >= 110) {
    $kriteriaWajib = 'Baik';
    } else {
    $kriteriaWajib = 'Tidak Memenuhi';
    }

    // Perhitungan Kriteria Kegiatan Tambahan
    if ($totalTambahan > 25) {
    $kriteriaTambahan = 'Unggul';
    } elseif ($totalTambahan >= 16) {
    $kriteriaTambahan = 'Sangat Baik';
    } elseif ($totalTambahan >= 11) {
    $kriteriaTambahan = 'Baik';
    } else {
    $kriteriaTambahan = 'Tidak Memenuhi';
    }

    // Total
    $totalSkor = $totalWajib + $totalTambahan;
    @endphp

    <h3 style="margin-bottom: 10px;">Rekapitulasi Skor Akhir</h3>

    <table width="100%" cellspacing="0" cellpadding="6" border="0"
        style="font-size: 12px; margin-bottom: 10px; border-collapse: collapse;">
        <tbody>
            {{-- Kegiatan Wajib --}}
            <tr>
                <td width="50%" style="padding: 6px 4px;">Kegiatan Wajib</td>
                <td width="25%" style="padding: 6px 4px; text-align: center; font-weight: bold;">{{ $totalWajib }}</td>
                <td width="25%" style="padding: 6px 4px; text-align: center;">{{ $kriteriaWajib }}</td>
            </tr>
            {{-- Kegiatan Tambahan --}}
            <tr>
                <td style="padding: 6px 4px;">Kegiatan Tambahan</td>
                <td style="padding: 6px 4px; text-align: center; font-weight: bold;">{{ $totalTambahan }}</td>
                <td style="padding: 6px 4px; text-align: center;">{{ $kriteriaTambahan }}</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 6px 4px; text-align: center; font-weight: bold;">Total Skor</td>
                <td style="padding: 6px 4px; text-align: center; font-weight: bold; font-size: 14px;">
                    {{ $totalSkor }}
                </td>
            </tr>
        </tbody>
    </table>
    <div style="width: 100%; margin-top: 60px;">
        <div style="float: right; text-align: center;">
            <span style="font-size: 13px;">Mengetahui,<br>Wakil Ketua III Kemahasiswaan</span>
            <br><br><br>
            <div style="width: 150px; height: 30px; border-bottom: 1px solid #333; margin: 0 auto 5px auto;"></div>
            <span style="font-weight: bold;">Ilham Maulana M.Farm</span>
        </div>
        <div style="clear: both;"></div>
    </div>
    <div class="footer">
        <p>Dicetak pada: {{ now()->format('d-m-Y H:i') }}</p>
    </div>
</body>

</html>