<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Arsip KRS {{ $mahasiswa->nim }} Semester {{ $semester }}</title>
    <style>
        @page { margin: 28px 34px; }
        body { font-family: DejaVu Sans, sans-serif; color: #2f3542; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #4b55c8; padding-bottom: 12px; margin-bottom: 18px; }
        .header img { width: 100%; max-height: 105px; object-fit: contain; }
        .title { font-size: 18px; font-weight: bold; margin: 4px 0; color: #30366b; }
        .subtitle { color: #6b7280; }
        .info { width: 100%; margin-bottom: 16px; border-collapse: collapse; }
        .info td { padding: 3px 5px; vertical-align: top; }
        .info .label { width: 19%; color: #6b7280; }
        .course { width: 100%; border-collapse: collapse; }
        .course th { background: #eef0ff; color: #30366b; border: 1px solid #cdd1ef; padding: 7px; }
        .course td { border: 1px solid #d9dce8; padding: 7px; }
        .center { text-align: center; }
        .right { text-align: right; }
        .status { display: inline-block; padding: 4px 9px; border-radius: 12px; font-weight: bold; }
        .approved { background: #e8f7ee; color: #18794e; }
        .pending { background: #fff4df; color: #9a6700; }
        .signature { width: 100%; margin-top: 30px; border-collapse: collapse; }
        .signature td { width: 50%; text-align: center; vertical-align: top; }
        .signature-space { height: 48px; }
        .name { font-weight: bold; text-decoration: underline; }
        .footer { position: fixed; bottom: -12px; left: 0; right: 0; text-align: center; color: #8a91a3; font-size: 9px; }
        .watermark { position: fixed; top: 34%; left: 27%; width: 46%; opacity: .045; z-index: -1; }
    </style>
</head>
<body>
    @if ($logo)
        <img class="watermark" src="data:image/png;base64,{{ $logo }}" alt="">
    @endif

    <div class="header">
        @if ($headerKrs)
            <img src="data:image/png;base64,{{ $headerKrs }}" alt="Header BAAK">
        @else
            <div class="title">{{ $settings?->name ?? 'Portal Akademik' }}</div>
        @endif
        <div class="title">ARSIP KARTU RENCANA STUDI</div>
        <div class="subtitle">Semester {{ $semester }} &bull; Tahun Akademik {{ $ta->nama }} {{ $ta->semester ? '('.$ta->semester.')' : '' }}</div>
    </div>

    <table class="info">
        <tr>
            <td class="label">Nama Mahasiswa</td><td>: <strong>{{ $mahasiswa->nama }}</strong></td>
            <td class="label">Angkatan</td><td>: {{ $mahasiswa->tahun_masuk ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">NIM</td><td>: {{ $mahasiswa->nim }}</td>
            <td class="label">Kelas</td><td>: {{ $mahasiswa->kelas ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Program Studi</td><td>: {{ $mahasiswa->programStudi?->nama ?? '-' }}</td>
            <td class="label">Status</td>
            <td>: <span class="status {{ $sudahDisetujui ? 'approved' : 'pending' }}">{{ $sudahDisetujui ? 'Disetujui Dospem' : 'Belum Disetujui' }}</span></td>
        </tr>
    </table>

    <table class="course">
        <thead>
            <tr>
                <th style="width: 7%">No</th>
                <th style="width: 20%">Kode MK</th>
                <th>Mata Kuliah</th>
                <th style="width: 10%">SKS</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalSks = 0;
            @endphp
            @foreach ($krs as $index => $item)
                @php
                    $mataKuliah = $item->kurikulum?->mataKuliah;
                    $totalSks += (int) ($mataKuliah?->sks ?? 0);
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $mataKuliah?->matakuliah_id ?? $item->matakuliah_id }}</td>
                    <td>{{ $mataKuliah?->nama ?? '-' }}</td>
                    <td class="center">{{ $mataKuliah?->sks ?? 0 }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="3" class="right"><strong>Total SKS</strong></td>
                <td class="center"><strong>{{ $totalSks }}</strong></td>
            </tr>
        </tbody>
    </table>

    <table class="signature">
        <tr>
            <td>
                <div>Mahasiswa</div><div class="signature-space"></div>
                <div class="name">{{ $mahasiswa->nama }}</div><div>{{ $mahasiswa->nim }}</div>
            </td>
            <td>
                <div>Dosen Pembimbing Akademik</div><div class="signature-space"></div>
                <div class="name">{{ $mahasiswa->dosen?->nama ?? 'Belum ditentukan' }}</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Arsip KRS dibuat dari Portal Akademik pada {{ now()->translatedFormat('d F Y H:i') }}
    </div>
</body>
</html>
