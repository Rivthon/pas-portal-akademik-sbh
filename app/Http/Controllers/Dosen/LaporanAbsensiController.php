<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Setting;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class LaporanAbsensiController extends Controller
{
    public function index()
    {
        $dosen = auth('dosen')->user();
        $activeTA = TahunAkademik::where('status_ta', 1)->first(['ta_id', 'nama', 'semester']);

        if (! $activeTA) {
            return back()->with('error', 'Tidak ada Tahun Akademik aktif.');
        }

        $jadwalTeori = $this->queryJadwalTeori($dosen->dosen_id)
            ->where('ta_id', $activeTA->ta_id)
            ->with(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'kurikulum.dosenToMatakuliah.dosen', 'ruangan'])
            ->orderBy('hari')->orderBy('jam_mulai')->get();

        $jadwalPraktik = $this->queryJadwalPraktik($dosen->dosen_id)
            ->where('ta_id', $activeTA->ta_id)
            ->with(['kurikulum.mataKuliah', 'kurikulum.programStudi', 'kurikulum.dosenToMatakuliah.dosen', 'ruangan'])
            ->orderBy('hari')->orderBy('jam_mulai')->get();

        $jadwalList = $this->formatJadwal($jadwalTeori, false);
        $jadwalListPraktik = $this->formatJadwal($jadwalPraktik, true);

        return view('dosen.absensi.cetak', compact('jadwalList', 'jadwalListPraktik', 'activeTA'));
    }

    public function generatePDF(Request $request)
    {
        $request->validate(['jadwal_id' => ['required', 'integer', 'exists:jadwal,id']]);
        $jadwal = $this->jadwalTeoriMilikDosen((int) $request->jadwal_id);
        [$mahasiswa, $rekapAbsensi, $totalPertemuan] = $this->rekapJadwal($jadwal);

        if ($totalPertemuan === 0 || $mahasiswa->isEmpty()) {
            return back()->with('error', 'Data absensi belum tersedia untuk jadwal ini.');
        }

        return $this->rekapPdf($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan, false);
    }

    public function generatePertemuan(Request $request)
    {
        $request->validate(['jadwal_id' => ['required', 'integer', 'exists:jadwal,id']]);
        $jadwal = $this->jadwalTeoriMilikDosen((int) $request->jadwal_id);

        return Pdf::loadView('dosen.absensi.laporan-pdf', compact('jadwal'))
            ->setPaper('a4', 'landscape')
            ->stream('BAP-Teori-'.$this->namaFile($jadwal).'.pdf');
    }

    public function generatePraktikPDF(Request $request)
    {
        $request->validate(['jadwal_praktik_id' => ['required', 'integer', 'exists:jadwal_praktik,id']]);
        $jadwal = $this->jadwalPraktikMilikDosen((int) $request->jadwal_praktik_id);
        [$mahasiswa, $rekapAbsensi, $totalPertemuan] = $this->rekapJadwal($jadwal);

        if ($totalPertemuan === 0 || $mahasiswa->isEmpty()) {
            return back()->with('error', 'Data absensi praktik belum tersedia untuk jadwal ini.');
        }

        return $this->rekapPdf($jadwal, $mahasiswa, $rekapAbsensi, $totalPertemuan, true);
    }

    public function generatePraktikPertemuan(Request $request)
    {
        $request->validate(['jadwal_praktik_id' => ['required', 'integer', 'exists:jadwal_praktik,id']]);
        $jadwal = $this->jadwalPraktikMilikDosen((int) $request->jadwal_praktik_id);

        return Pdf::loadView('dosen.absensi.laporan-praktik-pdf', compact('jadwal'))
            ->setPaper('a4', 'landscape')
            ->stream('BAP-Praktik-'.$this->namaFile($jadwal).'.pdf');
    }

    private function queryJadwalTeori($dosenId)
    {
        return Jadwal::query()->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosenId) {
            $query->where('dosen_id', $dosenId)->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
        });
    }

    private function queryJadwalPraktik($dosenId)
    {
        return JadwalPraktik::query()->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosenId) {
            $query->where('dosen_id', $dosenId)->whereRaw('LOWER(jenis_dosen) = ?', ['praktik']);
        });
    }

    private function jadwalTeoriMilikDosen(int $jadwalId): Jadwal
    {
        return $this->queryJadwalTeori(auth('dosen')->id())
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'pertemuan' => fn ($query) => $query
                    ->with('absensi.mahasiswa')->orderBy('tanggal_pertemuan')->orderBy('jam_mulai'),
            ])->findOrFail($jadwalId);
    }

    private function jadwalPraktikMilikDosen(int $jadwalId): JadwalPraktik
    {
        return $this->queryJadwalPraktik(auth('dosen')->id())
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'kurikulum.dosenToMatakuliah.dosen',
                'pertemuan' => fn ($query) => $query
                    ->with('absensi.mahasiswa')->orderBy('tanggal_pertemuan')->orderBy('jam_mulai'),
            ])->findOrFail($jadwalId);
    }

    private function formatJadwal($jadwal, bool $praktik)
    {
        return $jadwal
            ->groupBy(fn ($item) => $item->kurikulum?->mataKuliah?->smt ?? 'Tidak Ada Semester')
            ->map(fn ($items) => $items->map(fn ($item) => [
                $praktik ? 'jadwal_praktik_id' : 'jadwal_id' => $item->id,
                'hari' => $item->hari ?? '-',
                'jam_mulai' => $item->jam_mulai ?? '-',
                'jam_selesai' => $item->jam_selesai ?? '-',
                'jenis_kelas' => strtolower((string) $item->jenis_kelas),
                'kode_matakuliah' => $item->kurikulum?->mataKuliah?->matakuliah_id,
                'nama_matakuliah' => $item->kurikulum?->mataKuliah?->nama ?? '-',
                'nama_prodi' => $item->kurikulum?->programStudi?->nama ?? '-',
                'ruangan' => $item->ruangan?->nama ?? '-',
            ]));
    }

    private function rekapJadwal($jadwal): array
    {
        $rekapAbsensi = $jadwal->pertemuan->map(fn ($pertemuan) => [
            'topik' => $pertemuan->topik,
            'tanggal' => $pertemuan->tanggal_pertemuan,
            'absensi' => $pertemuan->absensi->mapWithKeys(fn ($absensi) => [
                $absensi->mahasiswa_id => $this->mapStatus($absensi->status),
            ]),
        ]);

        $mahasiswa = $jadwal->pertemuan
            ->flatMap(fn ($pertemuan) => $pertemuan->absensi->pluck('mahasiswa'))
            ->filter()->unique('mahasiswa_id')->sortBy('nama')->values();

        return [$mahasiswa, $rekapAbsensi, $jadwal->pertemuan->count()];
    }

    private function rekapPdf($jadwal, $mahasiswa, $rekapAbsensi, int $totalPertemuan, bool $praktik)
    {
        $settings = Setting::first();
        $qrTarget = $settings?->website_url ?: config('app.url');
        $qrFilePath = 'data:image/svg+xml;base64,'.base64_encode(
            (string) QrCode::size(160)->margin(1)->generate($qrTarget)
        );
        $jenis = $praktik ? 'praktik' : 'teori';
        $dosenMatakuliah = $jadwal->kurikulum->dosenToMatakuliah
            ->filter(fn ($item) => strtolower((string) $item->jenis_dosen) === $jenis
                && strtolower((string) $item->jenis_kelas) === strtolower((string) $jadwal->jenis_kelas))
            ->pluck('dosen')->filter()->unique('dosen_id')->values();
        if ($dosenMatakuliah->isEmpty() && auth('dosen')->user()) {
            $dosenMatakuliah = collect([auth('dosen')->user()]);
        }
        $kaprodiSignature = $this->signatureData(
            $jadwal->kurikulum?->programStudi?->ttd
        );
        $view = $praktik ? 'dosen.absensi.pdf-praktik' : 'dosen.absensi.pdf';

        return Pdf::loadView($view, compact(
            'jadwal', 'mahasiswa', 'rekapAbsensi', 'totalPertemuan',
            'settings', 'qrFilePath', 'dosenMatakuliah', 'kaprodiSignature'
        ))->setPaper('a4', 'landscape')
            ->stream('rekap-absensi-'.$jenis.'-'.$this->namaFile($jadwal).'.pdf');
    }

    private function mapStatus(?string $status): string
    {
        return match (strtolower((string) $status)) {
            'hadir' => 'H',
            'izin' => 'I',
            'sakit' => 'S',
            'tidak hadir', 'alpha', 'alpa', 'alfa' => 'A',
            default => '-',
        };
    }

    private function namaFile($jadwal): string
    {
        return str($jadwal->kurikulum?->mataKuliah?->nama ?? 'mata-kuliah')->slug()->toString();
    }

    private function signatureData(?string $relativePath): ?string
    {
        if (! filled($relativePath)) {
            return null;
        }

        $path = storage_path('app/public/'.$relativePath);
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
