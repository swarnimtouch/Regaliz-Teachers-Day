@extends('admin.layout')
@section('title', 'Submission Details')
@section('heading', $reel->doctor_name)

@section('content')
<div class="detail-grid">
    <section class="admin-panel">
        <h2>Doctor details</h2>
        <dl class="detail-list">
            <dt>Name</dt>
            <dd>{{ $reel->doctor_name }}</dd>
            <dt>Speciality</dt>
            <dd>{{ $reel->speciality }}</dd>
            <dt>City</dt>
            <dd>{{ $reel->city }}</dd>
            <dt>Type</dt>
            <dd>{{ ucfirst($mediaType) }}</dd>
            <dt>Status</dt>
            <dd>{{ ucfirst($reel->status) }}</dd>
            <dt>Submitted</dt>
            <dd>{{ $reel->created_at->format('d M Y, h:i A') }}</dd>
            <dt>Downloads</dt>
            <dd>{{ $reel->download_count }}</dd>

            @if($reel->teacher_name)
                <dt>Teacher name</dt>
                <dd>{{ $reel->teacher_name }}</dd>
            @endif

            @if($reel->card_message)
                <dt>Message</dt>
                <dd>{{ $reel->card_message }}</dd>
            @endif

            @if($reel->error_message)
                <dt>Error</dt>
                <dd>{{ $reel->error_message }}</dd>
            @endif
        </dl>
        <div class="admin-actions">
            @if(($mediaType === 'video' && $reel->generated_video) || ($mediaType === 'audio' && $reel->audioMessage?->generated_video) || ($mediaType === 'card' && $reel->generated_card))
                <a class="btn-gold" href="{{ route('admin.doctors.download', [$reel, 'media_type' => $mediaType]) }}">Download</a>
            @endif
            @if(in_array($mediaType, ['video', 'audio']))
                <form method="POST" action="{{ route('admin.doctors.regenerate', $reel) }}">
                    @csrf
                    <input type="hidden" name="media_type" value="{{ $mediaType }}">
                    <button class="btn-outline">Regenerate</button>
                </form>
            @endif
            <form method="POST" action="{{ route('admin.doctors.destroy', $reel) }}" onsubmit="return confirm('Delete this record and its media?')">
                @csrf
                @method('DELETE')
                <button class="danger-btn">Delete</button>
            </form>
        </div>
    </section>
    <section class="admin-panel">
        <h2>Media preview</h2>
        <div class="admin-media">
            @if($mediaType === 'video' && $reel->original_video)
                <label>Original recording</label>
                <video controls src="{{ route('admin.doctors.media', [$reel, 'original-video']) }}"></video>
            @endif

            @if($mediaType === 'audio' && $reel->audioMessage?->original_audio)
                <label>Original audio</label>
                <audio controls src="{{ route('admin.doctors.media', [$reel, 'original-audio']) }}"></audio>
            @endif

            @if($mediaType === 'video' && $reel->generated_video)
                <label>Generated reel</label>
                <video controls src="{{ route('admin.doctors.media', [$reel, 'generated-video']) }}"></video>
            @endif

            @if($mediaType === 'audio' && $reel->audioMessage?->generated_video)
                <label>Generated audio message</label>
                <video controls src="{{ route('admin.doctors.media', [$reel, 'generated-audio-video']) }}"></video>
            @endif

            @if($mediaType === 'card' && $reel->generated_card)
                <label>Generated card</label>
                <img src="{{ route('admin.doctors.media', [$reel, 'card']) }}" alt="Generated card">
            @endif

            @if(($mediaType === 'video' && !$reel->original_video && !$reel->generated_video) || ($mediaType === 'audio' && !$reel->audioMessage?->original_audio && !$reel->audioMessage?->generated_video) || ($mediaType === 'card' && !$reel->generated_card))
                <p>No media uploaded yet.</p>
            @endif
        </div>
    </section>
</div>
@endsection
