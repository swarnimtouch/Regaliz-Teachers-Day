@extends('layouts.campaign')
@section('title', 'Your Message Is Ready')
@section('content')
@php($generatedReel = $reel->content_type === 'audio' ? $reel->audioMessage?->generated_video : $reel->generated_video)
<section class="center-page">
    <div class="eyebrow">✦ Your message</div>
    <h1>{{ $reel->status === 'completed' ? 'Ready to inspire.' : 'Still being crafted.' }}</h1>
    @if($reel->content_type === 'card' && $reel->generated_card)
        <img class="result-card" src="{{ route('campaign.preview-card') }}" alt="Personalized Teacher's Day card">
        <a class="btn-gold" href="{{ route('campaign.download-card') }}">Download card ↓</a>
    @elseif($reel->status === 'completed' && $generatedReel)
        <video class="result-video {{ $reel->content_type === 'audio' ? 'audio-result' : '' }}" controls preload="metadata" src="{{ route('campaign.preview-reel', ['v' => $reel->processing_completed_at?->timestamp ?? now()->timestamp]) }}"></video>
        <div class="result-actions" style="display:flex;align-items:center;justify-content:center;gap:14px;flex-wrap:wrap">
            <a class="btn-gold" href="{{ route('campaign.download') }}">Download reel ↓</a>
            <a class="btn-outline" href="{{ route('campaign.choose-format') }}">Back to formats</a>
        </div>
    @else
        <div class="waiting-card">Your recording is safe. Please try recording again if generation did not complete.</div>
        <a class="btn-gold" href="{{ $reel->content_type === 'audio' ? route('campaign.record-audio') : route('campaign.record') }}">Record again</a>
    @endif
</section>
@endsection
