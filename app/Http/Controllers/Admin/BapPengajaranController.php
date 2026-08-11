<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Jadwal;
use App\Models\JadwalPraktik;
use App\Models\Pertemuan;
use App\Models\PertemuanPraktik;
use App\Models\TahunAkademik;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BapPengajaranController extends Controller
{
    private const MAKSIMAL_PERTEMUAN = 14;

    public function index(Request $request)
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);

        $dosenList = Dosen::query()
            ->whereHas('dosenMatakuliah.kurikulum', function ($query) use ($taId) {
                $query->when($taId, fn ($query) => $query->where('ta_id', $taId));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->search);
                $query->where(function ($query) use ($search) {
                    $query->where('nama', 'like', '%'.$search.'%')
                        ->orWhere('nidn', 'like', '%'.$search.'%')
                        ->orWhere('kd_dosen', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('nama')
            ->paginate(15)
            ->withQueryString();

        $rekap = $this->rekapDosen($dosenList->pluck('dosen_id'), $taId);

        $dosenList->getCollection()->each(function ($dosen) use ($rekap) {
            $data = $rekap->get($dosen->dosen_id, [
                'jumlah_mengajar' => 0,
                'jumlah_kelas' => 0,
                'teori' => 0,
                'praktik' => 0,
            ]);

            $dosen->jumlah_mengajar = $data['jumlah_mengajar'];
            $dosen->jumlah_kelas = $data['jumlah_kelas'];
            $dosen->jumlah_teori = $data['teori'];
            $dosen->jumlah_praktik = $data['praktik'];
        });

        return view('admin.bap-pengajaran.index', [
            'dosenList' => $dosenList,
            'tahunAkademik' => TahunAkademik::orderByDesc('ta_id')->get(),
            'selectedTaId' => $taId,
            'maksimalPertemuan' => self::MAKSIMAL_PERTEMUAN,
        ]);
    }

    public function show(Request $request, Dosen $dosen)
    {
        $activeTa = TahunAkademik::where('status_ta', 1)->first();
        $taId = $request->input('ta_id', $activeTa?->ta_id);
        $selectedTa = TahunAkademik::find($taId);
        $tanggalAkhir = now()->toDateString();

        $jadwalTeori = Jadwal::query()
            ->where('ta_id', $taId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['teori']);
            })
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'ruangan',
                'pertemuan' => fn ($query) => $query
                    ->where('dosen_id', $dosen->dosen_id)
                    ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)
                    ->orderBy('tanggal_pertemuan')
                    ->orderBy('jam_mulai'),
            ])
            ->withCount(['pertemuan as jumlah_pertemuan' => fn ($query) => $query
                ->where('dosen_id', $dosen->dosen_id)
                ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)])
            ->withMax(['pertemuan as terakhir_mengajar' => fn ($query) => $query
                ->where('dosen_id', $dosen->dosen_id)
                ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)], 'tanggal_pertemuan')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->each(fn ($jadwal) => $jadwal->jumlah_diakui = min(
                self::MAKSIMAL_PERTEMUAN,
                (int) $jadwal->jumlah_pertemuan
            ));

        $jadwalPraktik = JadwalPraktik::query()
            ->where('ta_id', $taId)
            ->whereHas('kurikulum.dosenToMatakuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->dosen_id)
                    ->whereRaw('LOWER(jenis_dosen) = ?', ['praktik']);
            })
            ->with([
                'kurikulum.mataKuliah',
                'kurikulum.programStudi',
                'ruangan',
                'pertemuan' => fn ($query) => $query
                    ->where('dosen_id', $dosen->dosen_id)
                    ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)
                    ->orderBy('tanggal_pertemuan')
                    ->orderBy('jam_mulai'),
            ])
            ->withCount(['pertemuan as jumlah_pertemuan' => fn ($query) => $query
                ->where('dosen_id', $dosen->dosen_id)
                ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)])
            ->withMax(['pertemuan as terakhir_mengajar' => fn ($query) => $query
                ->where('dosen_id', $dosen->dosen_id)
                ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)], 'tanggal_pertemuan')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->each(fn ($jadwal) => $jadwal->jumlah_diakui = min(
                self::MAKSIMAL_PERTEMUAN,
                (int) $jadwal->jumlah_pertemuan
            ));

        $jumlahTeori = (int) $jadwalTeori->sum('jumlah_diakui');
        $jumlahPraktik = (int) $jadwalPraktik->sum('jumlah_diakui');

        return view('admin.bap-pengajaran.show', compact(
            'dosen',
            'selectedTa',
            'jadwalTeori',
            'jadwalPraktik',
            'jumlahTeori',
            'jumlahPraktik'
        ) + ['maksimalPertemuan' => self::MAKSIMAL_PERTEMUAN]);
    }

    public function downloadPdf(Request $request, Dosen $dosen)
    {
        $detailView = $this->show($request, $dosen);
        $data = $detailView->getData();
        $namaDosen = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $dosen->nama);
        $namaSemester = preg_replace(
            '/[^A-Za-z0-9_-]+/',
            '-',
            (string) ($data['selectedTa']?->nama ?? 'semester')
        );

        activity_log(
            'download_bap_pengajaran_dosen',
            'BAUK/Admin mengunduh BAP pengajaran dosen '.$dosen->nama
        );

        return Pdf::loadView('admin.bap-pengajaran.pdf', $data)
            ->setPaper('a4', 'landscape')
            ->download('BAP-'.$namaDosen.'-'.$namaSemester.'.pdf');
    }

    private function rekapDosen($dosenIds, $taId)
    {
        $rekap = collect();
        $tanggalAkhir = now()->toDateString();

        $teori = Pertemuan::query()
            ->whereIn('dosen_id', $dosenIds)
            ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $taId))
            ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)
            ->selectRaw('dosen_id, jadwal_id, COUNT(*) as jumlah')
            ->groupBy('dosen_id', 'jadwal_id')
            ->get();

        $praktik = PertemuanPraktik::query()
            ->whereIn('dosen_id', $dosenIds)
            ->whereHas('jadwal', fn ($query) => $query->where('ta_id', $taId))
            ->whereDate('tanggal_pertemuan', '<=', $tanggalAkhir)
            ->selectRaw('dosen_id, jadwal_praktik_id, COUNT(*) as jumlah')
            ->groupBy('dosen_id', 'jadwal_praktik_id')
            ->get();

        foreach ($dosenIds as $dosenId) {
            $teoriDosen = $teori->where('dosen_id', $dosenId);
            $praktikDosen = $praktik->where('dosen_id', $dosenId);
            $jumlahTeori = $teoriDosen->sum(fn ($item) => min(
                self::MAKSIMAL_PERTEMUAN,
                (int) $item->jumlah
            ));
            $jumlahPraktik = $praktikDosen->sum(fn ($item) => min(
                self::MAKSIMAL_PERTEMUAN,
                (int) $item->jumlah
            ));

            $rekap->put($dosenId, [
                'jumlah_mengajar' => $jumlahTeori + $jumlahPraktik,
                'jumlah_kelas' => $teoriDosen->count() + $praktikDosen->count(),
                'teori' => $jumlahTeori,
                'praktik' => $jumlahPraktik,
            ]);
        }

        return $rekap;
    }
}
