@extends('layouts.campaign')
@section('title', 'Creating Your Reel')
@section('content')<section class="center-page"><div class="processing-orb"><span>✦</span></div><div class="eyebrow">A little magic is happening</div><h1>Creating your <em>message...</em></h1><p>We’re combining your recording with our Teacher’s Day design. This may take a moment.</p><div class="progress-track"><i></i></div><a class="btn-outline" href="{{ route('campaign.record') }}">Record again</a></section>@endsection
@push('scripts')<script>setInterval(async()=>{const r=await fetch(@json(route('campaign.status')));const d=await r.json();if(['completed','failed'].includes(d.status))location.href=d.result_url},3000)</script>@endpush
