@extends('layouts.campaign')

@section('title', 'Create Personalized Card')

@section('content')
    <section class="card-maker section-wrap">
        <div class="card-form-copy">
            <div class="eyebrow"><span>03</span> Personalized greeting</div>
            <h1>Create something <em>only they can receive.</em></h1>
            <p>Your teacher's name, your own words and your professional details will create a unique downloadable card.</p>
            <form id="cardForm" class="premium-form" method="POST" action="{{ route('campaign.store-card') }}">
                @csrf
                <label>Teacher or mentor name <span>*</span><input id="teacherName" name="teacher_name" maxlength="80" placeholder="Prof. Mehta" required></label>
                <label>Your message <span>*</span><textarea id="cardMessage" name="card_message" maxlength="240" rows="5" placeholder="Thank you for believing in me and guiding my journey..." required></textarea><small><output id="messageCount">0</output>/240 characters</small></label>
                <button class="btn-gold wide">Create my card →</button>
            </form>
        </div>
        <div class="unique-card-preview">
            <span class="card-kicker">HAPPY TEACHER'S DAY</span>
            <div class="guru-seal">GURU</div>
            <h2 id="previewTeacher">Dear Teacher,</h2>
            <p id="previewMessage">Your personal message will appear here as you type.</p>
            <small>With gratitude,</small>
            <b>{{ $reel->doctor_name }}</b>
            <i>{{ $reel->speciality }} · {{ $reel->city }}</i>
            <img class="card-company-logo" src="{{ $campaignLogoUrl }}" alt="Campaign logo">
            <footer>THE BEST TEACHERS HELP US REACH THE TOP</footer>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        const name = document.querySelector('#teacherName');
        const message = document.querySelector('#cardMessage');
        const previewName = document.querySelector('#previewTeacher');
        const previewMessage = document.querySelector('#previewMessage');
        const count = document.querySelector('#messageCount');
        name.addEventListener('input', () => previewName.textContent = `Dear ${name.value.trim() || 'Teacher'},`);
        message.addEventListener('input', () => {
            previewMessage.textContent = message.value.trim() || 'Your personal message will appear here as you type.';
            count.value = message.value.length;
        });
    </script>
@endpush
