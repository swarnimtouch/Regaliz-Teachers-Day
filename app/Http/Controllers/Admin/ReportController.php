<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DoctorReelsExport;
use App\Http\Controllers\Controller;
use App\Models\DoctorReel;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $from = $request->date('from')?->startOfDay() ?? now()->subDays(29)->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $base = DoctorReel::whereBetween('created_at', [$from, $to]);
        $summary = ['total' => (clone $base)->count(), 'completed' => (clone $base)->where('status', 'completed')->count(), 'failed' => (clone $base)->where('status', 'failed')->count(), 'downloads' => (clone $base)->sum('download_count')];
        $byType = (clone $base)->selectRaw('content_type, COUNT(*) total')->groupBy('content_type')->pluck('total', 'content_type');
        $byCity = (clone $base)->selectRaw('city, COUNT(*) total')->groupBy('city')->orderByDesc('total')->limit(10)->get();
        $daily = (clone $base)->selectRaw('DATE(created_at) day, COUNT(*) total')->groupBy('day')->orderBy('day')->get();

        return view('admin.reports.index', compact('summary', 'byType', 'byCity', 'daily', 'from', 'to'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(new DoctorReelsExport($request->only(['from', 'to'])), 'campaign-report-'.now()->format('Y-m-d').'.xlsx');
    }
}
