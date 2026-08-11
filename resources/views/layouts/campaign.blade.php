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
    <header class="site-header">
        <a class="brand" href="{{ route('campaign.landing') }}">
            <img class="company-logo" src="{{ $campaignLogoUrl }}" alt="Campaign logo">
            <span><b>{{ $campaignTitle }}</b><small>{{ $campaignSubtitle }}</small></span>
        </a>
    </header>
    <main>@yield('content')</main>
    <footer><span>Made with gratitude for the teachers who shape tomorrow.</span><span>© {{ date('Y') }} Teacher's Day</span></footer>
    @stack('scripts')
</body>
</html>
