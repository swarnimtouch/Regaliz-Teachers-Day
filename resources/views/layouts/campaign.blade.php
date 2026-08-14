<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $campaignTitle) - {{ $campaignTitle }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="ambient ambient-one"></div>
    <div class="ambient ambient-two"></div>
    <div class="teacher-layout-quote teacher-layout-quote-left" aria-hidden="true"><span>✦</span> Great teachers awaken possibility.</div>
    <div class="teacher-layout-quote" aria-hidden="true"><span>✦</span> Every lesson leaves a little light behind.</div>
    <header class="site-header">
        <a class="brand" href="{{ route('campaign.landing') }}">
            <img class="company-logo" src="{{ $campaignLogoUrl }}" alt="Campaign logo">
            <span><b>{{ $campaignTitle }}</b><small>{{ $campaignSubtitle }}</small></span>
        </a>
        @if(session('campaign_reel_id'))
            <form method="POST" action="{{ route('campaign.logout') }}">@csrf<button class="header-logout" type="submit">Logout</button></form>
        @endif
    </header>
    <main>@yield('content')</main>
    <footer><span>Made with gratitude for the teachers who shape tomorrow.</span><span>© {{ date('Y') }} Teacher's Day</span></footer>
    @stack('scripts')
</body>
</html>
