@extends('layouts.campaign')
@section('title', 'Your Tribute Is Ready')
@section('content')
<section class="center-page">
    <div class="eyebrow">✦ Your tribute</div>
    <h1>{{ $reel->status === 'completed' ? 'Ready to inspire.' : 'Still being crafted.' }}</h1>
    <p>Reference {{ $reel->reference_id }}</p>

    @if($reel->content_type === 'card' && $reel->generated_card)
        <img class="result-card" src="{{ route('campaign.preview-card') }}" alt="Personalized Teacher's Day card">
        <a class="btn-gold" href="{{ route('campaign.download-card') }}">Download card ↓</a>
    @elseif($reel->status === 'completed' && $reel->generated_video)
        <video class="result-video {{ $reel->content_type === 'audio' ? 'audio-result' : '' }}" controls preload="metadata" src="{{ route('campaign.preview-reel', ['v' => $reel->processing_completed_at?->timestamp ?? now()->timestamp]) }}"></video>
        <a class="btn-gold" href="{{ route('campaign.download') }}">Download reel ↓</a>
    @else
        <div class="waiting-card">Your recording is safe. Please try recording again if generation did not complete.</div>
        <a class="btn-gold" href="{{ $reel->content_type === 'audio' ? route('campaign.record-audio') : route('campaign.record') }}">Record again</a>
    @endif
</section>
@endsection
