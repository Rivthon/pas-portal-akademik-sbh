@php
    $programStudi = $jadwal->kurikulum?->programStudi;
    $namaKaprodi = $programStudi?->kaprod ?: 'Ketua Program Studi';
    $pengampu = collect($dosenMatakuliah ?? [])
        ->filter()
        ->unique(fn ($dosen) => $dosen->dosen_id ?? $dosen->nama ?? spl_object_id($dosen))
        ->values();
@endphp

<table style="width: 100%; border-collapse: collapse; margin-top: 22px; page-break-inside: avoid;">
    <tr>
        <td style="width: 50%; border: 0; text-align: center; vertical-align: top; padding: 5px 25px;">
            <div style="font-size: 11px;">Dosen Pengampu/Pengajar</div>
            <div style="height: 62px;"></div>
            @forelse($pengampu as $dosen)
                <div style="font-size: 11px; font-weight: bold; text-decoration: underline; margin-top: 2px;">
                    {{ $dosen->nama }}
                </div>
                @if(!empty($dosen->nidn))
                    <div style="font-size: 9px;">NIDN. {{ $dosen->nidn }}</div>
                @endif
            @empty
                <div style="font-size: 11px; font-weight: bold; text-decoration: underline;">Nama Dosen Pengampu</div>
            @endforelse
        </td>
        <td style="width: 50%; border: 0; text-align: center; vertical-align: top; padding: 5px 25px;">
            <div style="font-size: 11px;">Mengetahui,</div>
            <div style="font-size: 11px;">Ketua Program Studi {{ $programStudi?->nama ?? '' }}</div>
            <div style="height: 62px;">
                @if(!empty($kaprodiSignature))
                    <img src="{{ $kaprodiSignature }}" alt="Tanda tangan Kaprodi"
                        style="max-height: 58px; max-width: 150px; margin-top: 3px;">
                @endif
            </div>
            <div style="font-size: 11px; font-weight: bold; text-decoration: underline;">{{ $namaKaprodi }}</div>
        </td>
    </tr>
</table>
