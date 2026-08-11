<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - {{ $campaignTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="admin-body">
    <aside class="sidebar">
        <a class="brand" href="{{ route('admin.dashboard') }}">
            <img class="company-logo" src="{{ $campaignLogoUrl }}" alt="Campaign logo">
        </a>

        <nav>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.videos.*') ? 'active' : '' }}" href="{{ route('admin.videos.index') }}">Video recordings</a>
            <a class="{{ request()->routeIs('admin.audios.*') ? 'active' : '' }}" href="{{ route('admin.audios.index') }}">Audio messages</a>
            <a class="{{ request()->routeIs('admin.cards.*') ? 'active' : '' }}" href="{{ route('admin.cards.index') }}">Greeting cards</a>
            <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Reports</a>
            <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Settings</a>
        </nav>

        <div class="sidebar-account">
            <a class="sidebar-profile {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}" href="{{ route('admin.profile.edit') }}">
                @if(auth()->user()->avatar)
                    <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
                @else
                    <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                @endif
                <span class="sidebar-profile-copy"><b>{{ auth()->user()->name }}</b><small>My profile</small></span>
            </a>
            <form class="sidebar-logout" method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" title="Sign out" aria-label="Sign out"><i class="fa-solid fa-right-from-bracket"></i></button>
            </form>
        </div>
    </aside>

    <main class="admin-main">
        <header>
            <div>
                <small>{{ $campaignTitle }}</small>
                <h1>@yield('heading')</h1>
            </div>
        </header>

        @if(session('success'))
            <div class="admin-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
