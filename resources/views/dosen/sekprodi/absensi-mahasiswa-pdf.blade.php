<!DOCTYPE html>
<html lang="id"><head><meta charset="UTF-8"><title>Rekap Absensi Mahasiswa</title>
<style>
body{font-family:DejaVu Sans,sans-serif;font-size:9px;color:#222}h2,h3,p{margin:0 0 5px}.meta{margin:12px 0}.meta td{padding:2px 8px 2px 0}.summary{margin:10px 0;width:100%}.summary td{border:1px solid #bbb;padding:6px;text-align:center}.course{page-break-inside:avoid;margin-top:14px}.course-title{font-size:11px;font-weight:bold;margin-bottom:5px}.data{border-collapse:collapse;width:100%}.data th,.data td{border:1px solid #aaa;padding:4px}.data th{background:#e9eef8}.center{text-align:center}.muted{color:#666}
</style></head><body>
<h2>REKAP ABSENSI MAHASISWA</h2>
<p>{{ $tahunAkademik->nama }} - {{ $tahunAkademik->semester }}</p>
<table class="meta"><tr><td>Nama</td><td>: <strong>{{ $mahasiswa->nama }}</strong></td><td>NIM</td><td>: {{ $mahasiswa->nim }}</td></tr><tr><td>Program Studi</td><td>: {{ $mahasiswa->programStudi?->nama }}</td><td>Kelas</td><td>: {{ $mahasiswa->label_kelas }}</td></tr></table>
<table class="summary"><tr><td>Mata Kuliah<br><strong>{{ $ringkasan['mata_kuliah'] }}</strong></td><td>Hadir<br><strong>{{ $ringkasan['hadir'] }}</strong></td><td>Izin<br><strong>{{ $ringkasan['izin'] }}</strong></td><td>Sakit<br><strong>{{ $ringkasan['sakit'] }}</strong></td><td>Alpha<br><strong>{{ $ringkasan['alpha'] }}</strong></td><td>Belum Diabsen<br><strong>{{ $ringkasan['belum_diabsen'] }}</strong></td><td>Kehadiran<br><strong>{{ $ringkasan['persentase'] }}%</strong></td></tr></table>
@forelse($rekap as $item)
<div class="course"><div class="course-title">{{ $loop->iteration }}. {{ $item->nama_matakuliah }} <span class="muted">({{ $item->matakuliah_id }} · {{ $item->sks }} SKS · {{ $item->kelas }})</span></div>
<table class="data"><thead><tr><th width="4%">No</th><th width="8%">Jenis</th><th width="11%">Tanggal</th><th>Topik</th><th width="12%">Status</th><th width="20%">Keterangan</th></tr></thead><tbody>
@forelse($item->pertemuan as $meeting)<tr><td class="center">{{ $loop->iteration }}</td><td class="center">{{ $meeting->jenis }}</td><td class="center">{{ $meeting->tanggal ? \Carbon\Carbon::parse($meeting->tanggal)->format('d/m/Y') : '-' }}</td><td>{{ $meeting->topik }}</td><td class="center">{{ $meeting->status }}</td><td>{{ $meeting->keterangan ?: '-' }}</td></tr>@empty<tr><td colspan="6" class="center">Belum ada pertemuan.</td></tr>@endforelse
</tbody></table></div>
@empty<p>Belum ada KRS yang disetujui pada tahun akademik ini.</p>@endforelse
</body></html>
