<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorReel;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'doctors' => Doctor::count(),
            'videos' => DoctorReel::where('content_type', 'video')->count(),
            'audios' => DoctorReel::where('content_type', 'audio')->count(),
            'cards' => DoctorReel::where('content_type', 'card')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
