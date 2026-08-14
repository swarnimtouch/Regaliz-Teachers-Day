@extends('layouts.campaign')

@section('title', 'Choose Your Message')

@section('content')
<section class="format-page section-wrap">
    <div class="eyebrow"><span>02</span> Choose your message</div>
    <h1>How would you like to say <em>thank you?</em></h1>
    <p>Hello <strong>{{ $reel->doctor_name }}</strong>, select one format to continue.</p>
    <div class="format-grid">
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="video"><button class="format-card"><span class="format-icon">▣</span><b>Video message</b><small>Record your face and voice in a circular Teacher's Day reel.</small><i>Continue →</i></button></form>
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="audio"><button class="format-card"><span class="format-icon">◉</span><b>Audio message</b><small>Record your voice over the official Teacher's Day banner.</small><i>Continue →</i></button></form>
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="card"><button class="format-card"><span class="format-icon">▤</span><b>Personalized card</b><small>Create a unique chalkboard greeting with your teacher's name and message.</small><i>Customize →</i></button></form>
    </div>
</section>
@endsection
