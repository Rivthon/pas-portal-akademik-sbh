<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UsageReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:usage-report-list');
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'user_type' => ['nullable', 'in:dosen,mahasiswa'],
            'program_studi_id' => ['nullable', 'exists:program_studi,jurusan_id'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $startDate = Carbon::parse($validated['start_date'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $endDate = Carbon::parse($validated['end_date'] ?? now()->toDateString())->endOfDay();
        $selectedType = $validated['user_type'] ?? null;
        $search = trim((string) ($validated['search'] ?? ''));
        $programStudiId = $validated['program_studi_id'] ?? null;

        $dosen = null;
        if (! $selectedType || $selectedType === 'dosen') {
            $dosenUsage = $this->usageSubquery('dosen', $startDate, $endDate);
            $dosen = Dosen::query()
                ->leftJoinSub($dosenUsage, 'usage', fn ($join) => $join->on('usage.user_id', '=', 'dosen.dosen_id'))
                ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'dosen.jurusan_id')
                ->select([
                    'dosen.dosen_id', 'dosen.nama', 'dosen.nidn', 'dosen.email', 'dosen.status_dosen',
                    'program_studi.nama as program_studi_nama',
                ])
                ->selectRaw('COALESCE(usage.total_aktivitas, 0) as total_aktivitas')
                ->selectRaw('COALESCE(usage.total_login, 0) as total_login')
                ->selectRaw('COALESCE(usage.hari_aktif, 0) as hari_aktif')
                ->addSelect('usage.aktivitas_terakhir')
                ->when($programStudiId, fn ($query) => $query->where('dosen.jurusan_id', $programStudiId))
                ->when($search !== '', fn ($query) => $query->where(function ($filter) use ($search) {
                    $filter->where('dosen.nama', 'like', '%'.$search.'%')
                        ->orWhere('dosen.nidn', 'like', '%'.$search.'%')
                        ->orWhere('dosen.email', 'like', '%'.$search.'%');
                }))
                ->orderByRaw('usage.aktivitas_terakhir IS NULL')
                ->orderByDesc('usage.aktivitas_terakhir')
                ->orderBy('dosen.nama')
                ->paginate(15, ['*'], 'dosen_page')
                ->withQueryString();
        }

        $mahasiswa = null;
        if (! $selectedType || $selectedType === 'mahasiswa') {
            $mahasiswaUsage = $this->usageSubquery('mahasiswa', $startDate, $endDate);
            $mahasiswa = Mahasiswa::query()
                ->leftJoinSub($mahasiswaUsage, 'usage', fn ($join) => $join->on('usage.user_id', '=', 'mahasiswa.mahasiswa_id'))
                ->leftJoin('program_studi', 'program_studi.jurusan_id', '=', 'mahasiswa.jurusan_id')
                ->select([
                    'mahasiswa.mahasiswa_id', 'mahasiswa.nim', 'mahasiswa.nama', 'mahasiswa.email',
                    'mahasiswa.status_mhs', 'mahasiswa.semester', 'mahasiswa.kelas',
                    'program_studi.nama as program_studi_nama',
                ])
                ->selectRaw('COALESCE(usage.total_aktivitas, 0) as total_aktivitas')
                ->selectRaw('COALESCE(usage.total_login, 0) as total_login')
                ->selectRaw('COALESCE(usage.hari_aktif, 0) as hari_aktif')
                ->addSelect('usage.aktivitas_terakhir')
                ->when($programStudiId, fn ($query) => $query->where('mahasiswa.jurusan_id', $programStudiId))
                ->when($search !== '', fn ($query) => $query->where(function ($filter) use ($search) {
                    $filter->where('mahasiswa.nama', 'like', '%'.$search.'%')
                        ->orWhere('mahasiswa.nim', 'like', '%'.$search.'%')
                        ->orWhere('mahasiswa.email', 'like', '%'.$search.'%');
                }))
                ->orderByRaw('usage.aktivitas_terakhir IS NULL')
                ->orderByDesc('usage.aktivitas_terakhir')
                ->orderBy('mahasiswa.nama')
                ->paginate(15, ['*'], 'mahasiswa_page')
                ->withQueryString();
        }

        $periodLogs = ActivityLog::query()->whereBetween('created_at', [$startDate, $endDate]);
        $statistics = [
            'total_aktivitas' => (clone $periodLogs)->whereIn('user_type', ['dosen', 'mahasiswa'])->count(),
            'pengguna_aktif' => (clone $periodLogs)->whereIn('user_type', ['dosen', 'mahasiswa'])
                ->selectRaw("COUNT(DISTINCT CONCAT(user_type, ':', user_id)) as aggregate")->value('aggregate') ?: 0,
            'dosen_aktif' => (clone $periodLogs)->where('user_type', 'dosen')->distinct('user_id')->count('user_id'),
            'mahasiswa_aktif' => (clone $periodLogs)->where('user_type', 'mahasiswa')->distinct('user_id')->count('user_id'),
            'total_login' => (clone $periodLogs)->whereIn('user_type', ['dosen', 'mahasiswa'])->where('aktivitas', 'login')->count(),
        ];

        $topActivities = (clone $periodLogs)
            ->whereIn('user_type', ['dosen', 'mahasiswa'])
            ->select('user_type', 'aktivitas', DB::raw('COUNT(*) as total'))
            ->groupBy('user_type', 'aktivitas')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        activity_log('lihat_laporan_penggunaan', 'Admin melihat laporan penggunaan dosen dan mahasiswa.');

        return view('admin.usage-report.index', [
            'dosen' => $dosen,
            'mahasiswa' => $mahasiswa,
            'statistics' => $statistics,
            'topActivities' => $topActivities,
            'programStudis' => ProgramStudi::query()->orderBy('nama')->get(),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedType' => $selectedType,
        ]);
    }

    private function usageSubquery(string $userType, Carbon $startDate, Carbon $endDate): Builder
    {
        return DB::table('activity_logs')
            ->where('user_type', $userType)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id')
            ->selectRaw('COUNT(*) as total_aktivitas')
            ->selectRaw("SUM(CASE WHEN aktivitas = 'login' THEN 1 ELSE 0 END) as total_login")
            ->selectRaw('COUNT(DISTINCT DATE(created_at)) as hari_aktif')
            ->selectRaw('MAX(created_at) as aktivitas_terakhir')
            ->groupBy('user_id');
    }
}
