<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioMessage;
use App\Models\Doctor;
use App\Models\DoctorReel;
use App\Models\GreetingCard;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'doctors' => Doctor::count(),
            'videos' => DoctorReel::where('content_type', 'video')->whereNotNull('original_video')->count(),
            'audios' => AudioMessage::whereHas('doctorReel', fn ($query) => $query->where('content_type', 'audio'))->count(),
            'cards' => GreetingCard::whereHas('doctorReel', fn ($query) => $query->where('content_type', 'card'))->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
