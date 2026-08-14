@extends('layouts.campaign')

@section('title', 'Choose Your Message')

@section('content')
<section class="format-page section-wrap">
    <div class="eyebrow"><span>02</span> Choose your message</div>
    <h1>How would you like to say <em>thank you?</em></h1>
    <p>Hello <strong>{{ $reel->doctor_name }}</strong>, select one format to continue.</p>
    <div class="format-grid">
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="video"><button class="format-card"><span class="format-icon">▣</span><b>Video message</b><small>Thank the teacher who helped you believe in yourself.</small><i>Next →</i></button></form>
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="audio"><button class="format-card"><span class="format-icon">◉</span><b>Audio message</b><small>Share a warm memory or heartfelt words in your own voice.</small><i>Next →</i></button></form>
        <form method="POST" action="{{ route('campaign.select-format') }}">@csrf<input type="hidden" name="content_type" value="card"><button class="format-card"><span class="format-icon">▤</span><b>Personalized card</b><small>Write an inspiring note your teacher can keep and remember.</small><i>Next →</i></button></form>
    </div>
</section>
@endsection
