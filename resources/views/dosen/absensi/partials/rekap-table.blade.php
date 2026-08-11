<table class="rekap-table">
    <thead>
        <tr>
            <th rowspan="2" style="text-align: center; vertical-align: middle;">No</th>
            <th rowspan="2" style="text-align: center; vertical-align: middle;">Nama Mahasiswa</th>
            <th colspan="{{ $totalPertemuan }}" style="text-align: center;">Pertemuan</th>
            <th rowspan="2" style="text-align: center; vertical-align: middle;">Total H</th>
        </tr>
        <tr>
            @foreach ($rekapAbsensi as $pertemuan)
                <th style="text-align: center; padding: 5px 3px;">
                    <strong>P{{ $loop->iteration }}</strong>
                    <span style="display: block; margin-top: 3px; font-size: 8px; font-weight: normal; white-space: nowrap;">
                        {{ date('d/m/Y', strtotime((string) $pertemuan['tanggal'])) }}
                    </span>
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($mahasiswa as $index => $mhs)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td style="text-align: left; padding: 5px;">{{ $mhs->nama }}</td>
                @foreach ($rekapAbsensi as $pertemuan)
                    @php
                        $status = $pertemuan['absensi'][$mhs->mahasiswa_id] ?? '-';
                        $alfaStyle = $status === 'A'
                            ? 'background-color: #f8d7da; color: #b42318; font-weight: bold;'
                            : '';
                    @endphp
                    <td style="text-align: center; {{ $alfaStyle }}">{{ $status }}</td>
                @endforeach
                <td style="text-align: center;">
                    {{
                        collect($rekapAbsensi)
                            ->filter(fn ($pertemuan) => ($pertemuan['absensi'][$mhs->mahasiswa_id] ?? null) === 'H')
                            ->count()
                    }}
                </td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="margin-top: 7px; font-size: 9px; color: #444;">
    <strong>Keterangan:</strong>
    H = Hadir &nbsp;|&nbsp; S = Sakit &nbsp;|&nbsp; I = Izin &nbsp;|&nbsp;
    <span style="color: #b42318; font-weight: bold;">A = Alfa</span>
</div>
