<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:activity-log-list', ['only' => ['index']]);
        $this->middleware('permission:activity-log-export', ['only' => ['exportPdf']]);
    }

    public function index(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->paginate(15)->withQueryString();

        // Statistics
        $totalLogs = DB::table('activity_logs')->count();
        $todayLogs = DB::table('activity_logs')->whereDate('created_at', today())->count();
        $adminCount = DB::table('activity_logs')->where('user_type', 'admin')->count();
        $dosenCount = DB::table('activity_logs')->where('user_type', 'dosen')->count();
        $mahasiswaCount = DB::table('activity_logs')->where('user_type', 'mahasiswa')->count();
        $weekLogs = DB::table('activity_logs')->where('created_at', '>=', now()->subDays(7))->count();

        return view('admin.activity_logs.index', compact(
            'logs', 'totalLogs', 'todayLogs', 'adminCount', 'dosenCount', 'mahasiswaCount', 'weekLogs'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = ActivityLog::query();

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('aktivitas', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->latest()->get();

        $pdf = Pdf::loadView('admin.activity_logs.pdf', compact('logs'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan_Aktivitas_User.pdf');
    }
}
