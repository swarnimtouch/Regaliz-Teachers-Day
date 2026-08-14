<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoctorReel;
use Carbon\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total' => DoctorReel::count(),
            'recordings' => DoctorReel::whereNotNull('original_video')->count(),
            'completed' => DoctorReel::where('status', 'completed')->count(),
            'processing' => DoctorReel::where('status', 'processing')->count(),
            'failed' => DoctorReel::where('status', 'failed')->count(),
            'today' => DoctorReel::whereDate('created_at', today())->count(),
        ];

        $daily = collect(range(6, 0))->map(function (int $daysAgo) {
            $date = Carbon::today()->subDays($daysAgo);

            return (object) [
                'label' => $date->format('D'),
                'date' => $date->format('d M'),
                'total' => DoctorReel::whereDate('created_at', $date)->count(),
            ];
        });

        $contentMix = collect([
            'video' => DoctorReel::whereNotNull('original_video')->count(),
            'audio' => DoctorReel::whereHas('audioMessage')->count(),
            'card' => DoctorReel::whereHas('greetingCard')->count(),
        ]);

        return view('admin.dashboard', compact('stats', 'daily', 'contentMix'));
    }
}
