<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorReelRequest;
use App\Http\Requests\UploadAudioRequest;
use App\Http\Requests\UploadRecordingRequest;
use App\Jobs\GenerateAudioReel;
use App\Jobs\GenerateDoctorReel;
use App\Models\DoctorReel;
use App\Models\AudioMessage;
use App\Models\GreetingCard;
use App\Models\ReelStatusHistory;
use App\Services\Reel\PersonalizedCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CampaignController extends Controller
{
    public function index(): View
    {
        return view('frontend.form');
    }

    public function store(StoreDoctorReelRequest $request): RedirectResponse
    {
        Log::info('Campaign registration submitted', [
            'ip' => $request->ip(),
            'session_id' => $request->session()->getId(),
        ]);

        $reel = DoctorReel::query()->create($request->validated() + [
            'speciality' => 'Not specified',
            'reference_id' => 'TDR-'.now()->format('ymd').'-'.Str::upper(Str::random(6)),
            'status' => 'awaiting_recording',
        ]);
        $reel->statusHistories()->create(['status' => 'awaiting_recording', 'message' => 'Doctor details submitted']);
        $request->session()->put('campaign_reel_id', $reel->id);

        Log::info('Campaign registration stored', [
            'reference_id' => $reel->reference_id,
            'reel_id' => $reel->id,
        ]);

        return redirect()->route('campaign.choose-format');
    }

    public function chooseFormat(): View
    {
        return view('frontend.choose-format', ['reel' => $this->currentReel()]);
    }

    public function logoutCampaign(Request $request): RedirectResponse
    {
        $request->session()->forget('campaign_reel_id');

        return redirect()->route('campaign.landing');
    }

    public function selectFormat(Request $request): RedirectResponse
    {
        $validated = $request->validate(['content_type' => ['required', 'in:video,audio,card']]);
        $this->currentReel()->update(['content_type' => $validated['content_type']]);

        $route = match ($validated['content_type']) {
            'audio' => 'campaign.record-audio',
            'card' => 'campaign.create-card',
            default => 'campaign.record',
        };

        return redirect()->route($route);
    }

    public function record(): View
    {
        return view('frontend.record', ['reel' => $this->currentReel()]);
    }

    public function upload(UploadRecordingRequest $request): RedirectResponse
    {
        $doctorReel = $this->currentReel();
        $file = $request->file('recording');
        $path = $file->storeAs('recordings/'.now()->format('Y/m'), $doctorReel->reference_id.'.'.$file->extension(), 'local');
        $doctorReel->update([
            'original_video' => $path,
            'video_zoom' => $request->float('video_zoom'),
            'status' => 'processing',
            'processing_started_at' => now(),
            'error_message' => null,
        ]);
        ReelStatusHistory::query()->create(['doctor_reel_id' => $doctorReel->id, 'status' => 'processing', 'message' => 'Recording uploaded securely']);

        Log::info('Video recording stored; dispatching reel job', [
            'reference_id' => $doctorReel->reference_id,
            'recording_path' => $path,
            'queue_connection' => config('queue.default'),
        ]);

        try {
            if (config('queue.default') === 'sync' && function_exists('set_time_limit')) {
                set_time_limit(360);
            }
            GenerateDoctorReel::dispatch($doctorReel);
            Log::info('Video reel job dispatched', ['reference_id' => $doctorReel->reference_id]);
        } catch (\Throwable $exception) {
            report($exception);
            $this->markDispatchFailed($doctorReel, $exception);
        }

        return redirect()->route('campaign.processing');
    }

    public function recordAudio(): View
    {
        return view('frontend.record-audio', ['reel' => $this->currentReel()]);
    }

    public function uploadAudio(UploadAudioRequest $request): RedirectResponse
    {
        $doctorReel = $this->currentReel();
        $file = $request->file('audio');
        $path = $file->storeAs('audio/'.now()->format('Y/m'), $doctorReel->reference_id.'.'.$file->extension(), 'local');
        AudioMessage::query()->updateOrCreate(
            ['doctor_reel_id' => $doctorReel->id],
            ['original_audio' => $path, 'generated_video' => null, 'status' => 'processing', 'error_message' => null, 'processing_started_at' => now(), 'processing_completed_at' => null]
        );
        $doctorReel->update(['original_audio' => $path, 'content_type' => 'audio', 'status' => 'processing', 'processing_started_at' => now(), 'error_message' => null]);
        $doctorReel->statusHistories()->create(['status' => 'processing', 'message' => 'Audio message uploaded securely']);

        Log::info('Audio recording stored; dispatching reel job', [
            'reference_id' => $doctorReel->reference_id,
            'audio_path' => $path,
            'queue_connection' => config('queue.default'),
        ]);

        try {
            if (config('queue.default') === 'sync' && function_exists('set_time_limit')) {
                set_time_limit(360);
            }
            GenerateAudioReel::dispatch($doctorReel);
            Log::info('Audio reel job dispatched', ['reference_id' => $doctorReel->reference_id]);
        } catch (\Throwable $exception) {
            report($exception);
            $this->markDispatchFailed($doctorReel, $exception);
        }

        return redirect()->route('campaign.processing');
    }

    public function createCard(): View
    {
        return view('frontend.create-card', ['reel' => $this->currentReel()]);
    }

    public function storeCard(Request $request, PersonalizedCard $card): RedirectResponse
    {
        $validated = $request->validate([
            'teacher_name' => ['required', 'string', 'max:80'],
            'card_message' => ['required', 'string', 'max:240'],
            'card_template' => ['required', 'in:chalkboard,golden,notebook'],
        ]);
        $reel = $this->currentReel();
        $reel->update([
            'teacher_name' => $validated['teacher_name'],
            'card_message' => $validated['card_message'],
            'content_type' => 'card',
        ]);
        $generatedCard = $card->generate($reel, $validated['card_template']);
        GreetingCard::query()->updateOrCreate(
            ['doctor_reel_id' => $reel->id],
            ['teacher_name' => $validated['teacher_name'], 'message' => $validated['card_message'], 'generated_card' => $generatedCard, 'status' => 'completed', 'processing_completed_at' => now()]
        );
        $reel->update(['generated_card' => $generatedCard, 'status' => 'completed', 'processing_completed_at' => now()]);

        return redirect()->route('campaign.result');
    }

    public function downloadCard(): BinaryFileResponse
    {
        $reel = $this->currentReel();
        abort_unless($reel->generated_card && Storage::disk('local')->exists($reel->generated_card), 404);
        $reel->increment('download_count');

        return response()->download(Storage::disk('local')->path($reel->generated_card), $reel->reference_id.'-card.png');
    }

    public function previewCard(): BinaryFileResponse
    {
        $reel = $this->currentReel();
        abort_unless($reel->generated_card && Storage::disk('local')->exists($reel->generated_card), 404);

        return response()->file(Storage::disk('local')->path($reel->generated_card));
    }

    public function processing(): View
    {
        return view('frontend.processing', ['reel' => $this->currentReel()]);
    }

    public function result(): View
    {
        return view('frontend.result', ['reel' => $this->currentReel()]);
    }

    public function status(): JsonResponse
    {
        $doctorReel = $this->currentReel();

        return response()->json(['status' => $doctorReel->status, 'result_url' => route('campaign.result')]);
    }

    public function download(): BinaryFileResponse
    {
        $doctorReel = $this->currentReel();
        $path = $doctorReel->content_type === 'audio' ? $doctorReel->audioMessage?->generated_video : $doctorReel->generated_video;
        abort_unless($doctorReel->status === 'completed' && $path && Storage::disk('local')->exists($path), 404);
        $doctorReel->increment('download_count');

        return response()->download(Storage::disk('local')->path($path), $doctorReel->reference_id.'.mp4');
    }

    public function previewReel(): BinaryFileResponse
    {
        $doctorReel = $this->currentReel();
        $path = $doctorReel->content_type === 'audio' ? $doctorReel->audioMessage?->generated_video : $doctorReel->generated_video;
        abort_unless($doctorReel->status === 'completed' && $path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    private function currentReel(): DoctorReel
    {
        $reel = DoctorReel::query()->find(session('campaign_reel_id'));
        abort_unless($reel, 404, 'Please start by entering your details.');

        return $reel;
    }

    private function markDispatchFailed(DoctorReel $reel, \Throwable $exception): void
    {
        Log::error('Reel job dispatch failed', [
            'reference_id' => $reel->reference_id,
            'exception' => $exception,
        ]);
        $reel->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'processing_failed_at' => now(),
        ]);
        $reel->statusHistories()->create([
            'status' => 'failed',
            'message' => 'Reel generation could not be started.',
        ]);
    }
}
