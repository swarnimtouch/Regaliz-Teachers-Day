<?php

namespace App\Http\Controllers\Admin;

use App\Exports\DoctorReelsExport;
use App\Http\Controllers\Controller;
use App\Jobs\GenerateAudioReel;
use App\Jobs\GenerateDoctorReel;
use App\Models\DoctorReel;
use App\Services\MediaStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

    public function show(Request $request, DoctorReel $doctorReel): View
    {
        $mediaType = in_array($request->query('media_type'), ['video', 'audio', 'card'], true)
            ? $request->query('media_type')
            : $doctorReel->content_type;

        return view('admin.doctors.show', ['reel' => $doctorReel, 'mediaType' => $mediaType]);
    }

    public function regenerate(Request $request, DoctorReel $doctorReel): RedirectResponse
    {
        $mediaType = $request->input('media_type', $doctorReel->content_type);
        $doctorReel->update(['status' => 'processing', 'error_message' => null, 'processing_started_at' => now()]);
        $mediaType === 'audio' ? GenerateAudioReel::dispatch($doctorReel) : GenerateDoctorReel::dispatch($doctorReel);

        return back()->with('success', 'Regeneration completed or queued successfully.');
    }

    public function download(Request $request, DoctorReel $doctorReel, MediaStorage $media): StreamedResponse
    {
        $type = $request->query('media_type', $doctorReel->content_type);
        $path = match ($type) {
            'audio' => $doctorReel->audioMessage?->original_audio,
            'card' => $doctorReel->generated_card,
            default => $doctorReel->generated_video,
        };
        abort_unless($path && $media->disk()->exists($path), 404);

        $extension = $type === 'card' ? 'png' : ($type === 'audio' ? (pathinfo($path, PATHINFO_EXTENSION) ?: 'webm') : 'mp4');
        $name = (\Illuminate\Support\Str::slug($doctorReel->doctor_name) ?: 'teacher-message').'-'.$type.'.'.$extension;
        return $media->download($path, $name);
    }

    public function media(DoctorReel $doctorReel, string $kind, MediaStorage $media): StreamedResponse
    {
        $path = match ($kind) {
            'original-video' => $doctorReel->original_video,
            'original-audio' => $doctorReel->audioMessage?->original_audio,
            'generated-video' => $doctorReel->generated_video,
            'generated-audio-video' => $doctorReel->audioMessage?->generated_video,
            'card' => $doctorReel->generated_card,
            default => null,
        };
        abort_unless($path && $media->disk()->exists($path), 404);

        $contentType = $media->disk()->mimeType($path) ?: ($kind === 'card' ? 'image/png' : ($kind === 'original-audio' ? 'audio/webm' : 'video/mp4'));
        return $media->stream($path, $contentType);
    }

    public function destroy(DoctorReel $doctorReel, MediaStorage $media): RedirectResponse
    {
        foreach ([$doctorReel->original_video, $doctorReel->audioMessage?->original_audio, $doctorReel->generated_video, $doctorReel->audioMessage?->generated_video, $doctorReel->generated_card] as $path) {
            if ($path) {
                $media->disk()->delete($path);
            }
        }
        if ($doctorReel->details_image) {
            Storage::disk('local')->delete($doctorReel->details_image);
        }
        $doctorReel->delete();

        return redirect()->route('admin.doctors.index')->with('success', 'Record deleted.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        return Excel::download(new DoctorReelsExport($request->only(['search', 'status', 'content_type', 'media_type', 'from', 'to'])), 'doctor-reels-'.now()->format('Y-m-d-His').'.xlsx');
    }

    private function typeIndex(Request $request, string $type, string $title): View
    {
        $filters = $request->only(['search', 'status', 'from', 'to']);
        $filters['content_type'] = $type;
        $filters['media_type'] = $type;
        $reels = (new DoctorReelsExport($filters))->query()->paginate(20)->withQueryString();

        return view('admin.doctors.index', ['reels' => $reels, 'filters' => $filters, 'moduleTitle' => $title]);
    }
}
