@extends('admin.layout')

@section('title', 'Settings')
@section('heading', 'Campaign settings')

@section('content')
    <section class="admin-panel settings-panel">
        <form class="admin-form" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <label>Campaign title<input name="campaign_title" value="{{ $settings['campaign_title'] ?? '' }}" required></label>
            <label>Subtitle<input name="campaign_subtitle" value="{{ $settings['campaign_subtitle'] ?? '' }}" required></label>
            <label>Quote<textarea name="campaign_quote">{{ $settings['campaign_quote'] ?? '' }}</textarea></label>
            <div class="logo-setting">
                <img src="{{ $campaignLogoUrl }}" alt="Current campaign logo">
                <label>Campaign logo<input type="file" name="campaign_logo" accept="image/png,image/jpeg,image/webp"><small>This logo will update across the admin panel and frontend. Maximum 3 MB.</small></label>
            </div>

            <div class="form-grid">
                <label>Minimum recording seconds<input type="number" name="recording_min_seconds" value="{{ $settings['recording_min_seconds'] ?? 5 }}" min="1" max="20"></label>
                <label>Maximum recording seconds<input type="number" name="recording_max_seconds" value="{{ $settings['recording_max_seconds'] ?? 20 }}" min="5" max="90"></label>
            </div>

            <label>Upload limit (MB)<input type="number" name="upload_max_mb" value="{{ $settings['upload_max_mb'] ?? 50 }}" min="1" max="200"></label>
            <label class="check-line"><input type="checkbox" name="campaign_active" value="1" @checked(($settings['campaign_active'] ?? '1') === '1')> Campaign active</label>
            <button class="btn-gold">Save settings</button>
        </form>
    </section>
@endsection
