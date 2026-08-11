@extends('layouts.campaign')
@section('title', "A Tribute to Teachers")
@section('content')
<section class="hero section-wrap">
    <div class="hero-copy">
        <div class="eyebrow"><span>✦</span> A Teacher's Day Celebration</div>
        <h1>For the mentors who made us <em>who we are.</em></h1>
        <p>Record a heartfelt message and turn it into a beautiful, shareable tribute—in less than a minute.</p>
        <div class="hero-actions"><a class="btn-gold" href="{{ route('campaign.create') }}">Create your reel <span>→</span></a><span class="tiny-note">No app needed · Takes 60 seconds</span></div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="orbit orbit-a"></div><div class="orbit orbit-b"></div>
        <div class="portrait-card"><div class="portrait-ring"><div class="teacher-icon">✦<span>MENTOR</span></div></div><p>“A teacher takes a hand,<br>opens a mind & touches a heart.”</p></div>
        <span class="float-icon book">▰</span><span class="float-icon star">✦</span><span class="float-icon pen">✎</span>
    </div>
</section>
<section class="how section-wrap"><div class="section-heading"><span>Simple & meaningful</span><h2>Your tribute in three steps</h2></div><div class="steps">
    <article><b>01</b><div class="step-icon">✍</div><h3>Tell us about you</h3><p>Add your name, speciality and city so your reel feels personal.</p></article>
    <article><b>02</b><div class="step-icon">◉</div><h3>Record your message</h3><p>Speak from the heart for 5–20 seconds using your front camera.</p></article>
    <article><b>03</b><div class="step-icon">✦</div><h3>Share your gratitude</h3><p>We craft a vertical Teacher's Day reel, ready to download and share.</p></article>
</div></section>
<section class="quote-band"><span>“</span><p>The influence of a good teacher can never be erased.</p><a href="{{ route('campaign.create') }}">Begin your tribute →</a></section>
@endsection
