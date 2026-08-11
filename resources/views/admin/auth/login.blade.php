<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Sign In - {{ $campaignTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;600;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="admin-login">
    <div class="login-brand">
        <span class="brand-mark">MR</span>
        <h1>{{ $campaignTitle }}</h1>
        <p>Campaign administration</p>
    </div>

    <form method="POST" action="{{ route('admin.login.store') }}" class="login-card">
        @csrf
        <h2>Welcome back</h2>
        <p>Sign in to manage your Teacher's Day campaign.</p>

        @if(session('error'))
            <div class="alert-error">{{ session('error') }}</div>
        @endif

        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required autofocus>
        </label>
        <label>
            Password
            <input type="password" name="password" required>
        </label>

        @error('email')
            <div class="alert-error">{{ $message }}</div>
        @enderror

        <label class="consent">
            <input type="checkbox" name="remember">
            <span>Keep me signed in</span>
        </label>
        <button class="btn-gold wide">Sign in →</button>
        <a href="{{ route('campaign.landing') }}">← Back to campaign</a>
    </form>
</body>
</html>
