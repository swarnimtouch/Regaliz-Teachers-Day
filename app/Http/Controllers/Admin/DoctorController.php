<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DoctorReelsExport;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAudioReel;
use App\Jobs\GenerateDoctorReel;
use App\Models\DoctorReel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DoctorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'status', 'content_type', 'from', 'to']);
        $reels = (new DoctorReelsExport($filters))->query()->paginate(20)->withQueryString();

        return view('admin.doctors.index', ['reels' => $reels, 'filters' => $filters, 'moduleTitle' => 'All Submissions']);
    }

    public function videos(Request $request): View
    {
        return $this->typeIndex($request, 'video', 'Video Recordings');
    }

    public function audios(Request $request): View
    {
        return $this->typeIndex($request, 'audio', 'Audio Messages');
    }

    public function cards(Request $request): View
    {
        return $this->typeIndex($request, 'card', 'Greeting Cards');
    }

    public function show(DoctorReel $doctorReel): View
    {
        return view('admin.doctors.show', ['reel' => $doctorReel]);
    }

    public function regenerate(DoctorReel $doctorReel): RedirectResponse
    {
        $doctorReel->update(['status' => 'processing', 'error_message' => null, 'processing_started_at' => now()]);
        $doctorReel->content_type === 'audio' ? GenerateAudioReel::dispatch($doctorReel) : GenerateDoctorReel::dispatch($doctorReel);

        return back()->with('success', 'Regeneration completed or queued successfully.');
    }

    public function download(DoctorReel $doctorReel): BinaryFileResponse
    {
        $path = $doctorReel->content_type === 'card' ? $doctorReel->generated_card : $doctorReel->generated_video;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->download(Storage::disk('local')->path($path));
    }

    public function media(DoctorReel $doctorReel, string $kind): BinaryFileResponse
    {
        $path = match ($kind) {
            'original-video' => $doctorReel->original_video,
            'original-audio' => $doctorReel->original_audio,
            'generated-video' => $doctorReel->generated_video,
            'card' => $doctorReel->generated_card,
            default => null,
        };
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function destroy(DoctorReel $doctorReel): RedirectResponse
    {
        foreach ([$doctorReel->original_video, $doctorReel->original_audio, $doctorReel->generated_video, $doctorReel->generated_card, $doctorReel->details_image] as $path) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
        }
        $doctorReel->delete();

        return redirect()->route('admin.doctors.index')->with('success', 'Record deleted.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(new DoctorReelsExport($request->only(['search', 'status', 'content_type', 'from', 'to'])), 'doctor-reels-'.now()->format('Y-m-d-His').'.xlsx');
    }

    private function typeIndex(Request $request, string $type, string $title): View
    {
        $filters = $request->only(['search', 'status', 'from', 'to']);
        $filters['content_type'] = $type;
        $reels = (new DoctorReelsExport($filters))->query()->paginate(20)->withQueryString();

        return view('admin.doctors.index', ['reels' => $reels, 'filters' => $filters, 'moduleTitle' => $title]);
    }
}
