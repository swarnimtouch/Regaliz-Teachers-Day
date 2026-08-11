@extends('admin.layout')
@section('title', 'Submission Details')
@section('heading', $reel->doctor_name)

@section('content')
<div class="detail-grid">
    <section class="admin-panel">
        <h2>Doctor details</h2>
        <dl class="detail-list">
            <dt>Doctor name</dt>
            <dd>{{ $reel->doctor_name }}</dd>
            <dt>Speciality</dt>
            <dd>{{ $reel->speciality }}</dd>
            <dt>City</dt>
            <dd>{{ $reel->city }}</dd>
            <dt>Hospital</dt>
            <dd>{{ $reel->hospital_name ?: '—' }}</dd>
            <dt>Mobile</dt>
            <dd>{{ $reel->mobile ?: '—' }}</dd>
            <dt>Type</dt>
            <dd>{{ ucfirst($reel->content_type) }}</dd>
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
            @if($reel->generated_video || $reel->generated_card)
                <a class="btn-gold" href="{{ route('admin.doctors.download', $reel) }}">Download</a>
            @endif
            @if(in_array($reel->content_type, ['video', 'audio']))
                <form method="POST" action="{{ route('admin.doctors.regenerate', $reel) }}">
                    @csrf
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
            @if($reel->original_video)
                <label>Original recording</label>
                <video controls src="{{ route('admin.doctors.media', [$reel, 'original-video']) }}"></video>
            @endif

            @if($reel->original_audio)
                <label>Original audio</label>
                <audio controls src="{{ route('admin.doctors.media', [$reel, 'original-audio']) }}"></audio>
            @endif

            @if($reel->generated_video)
                <label>Generated reel</label>
                <video controls src="{{ route('admin.doctors.media', [$reel, 'generated-video']) }}"></video>
            @endif

            @if($reel->generated_card)
                <label>Generated card</label>
                <img src="{{ route('admin.doctors.media', [$reel, 'card']) }}" alt="Generated card">
            @endif

            @if(!$reel->original_video && !$reel->original_audio && !$reel->generated_video && !$reel->generated_card)
                <p>No media uploaded yet.</p>
            @endif
        </div>
    </section>
</div>
@endsection
