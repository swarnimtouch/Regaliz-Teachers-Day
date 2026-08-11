@extends('layouts.campaign')

@section('title', 'Create Your Tribute')

@section('content')
<section class="form-page section-wrap">
    <div class="hero-art registration-mentor" aria-hidden="true">
        <div class="orbit orbit-a"></div><div class="orbit orbit-b"></div>
        <div class="portrait-card"><div class="portrait-ring"><div class="teacher-icon">✦<span>MENTOR</span></div></div><p>“A teacher takes a hand,<br>opens a mind & touches a heart.”</p></div>
        <span class="float-icon book">▰</span><span class="float-icon star">✦</span><span class="float-icon pen">✎</span>
    </div>

    <form id="registrationForm" class="premium-form registration-form" method="POST" action="{{ route('campaign.store') }}" novalidate>
        @csrf
        @if ($errors->any())
            <div class="alert-error">Please check the highlighted fields.</div>
        @endif

        <label>
            Name <span>*</span>
            <input name="doctor_name" value="{{ old('doctor_name') }}" placeholder="Enter your full name" maxlength="100" autocomplete="name">
            <small class="field-error" data-error-for="doctor_name">@error('doctor_name'){{ $message }}@enderror</small>
        </label>

        <div class="form-grid">
            <label>
                Speciality <span>*</span>
                <input name="speciality" value="{{ old('speciality') }}" placeholder="Cardiologist" maxlength="100">
                <small class="field-error" data-error-for="speciality">@error('speciality'){{ $message }}@enderror</small>
            </label>
            <label>
                City <span>*</span>
                <input name="city" value="{{ old('city') }}" placeholder="Mumbai" maxlength="100">
                <small class="field-error" data-error-for="city">@error('city'){{ $message }}@enderror</small>
            </label>
        </div>

        <label class="consent">
            <input type="checkbox" name="consent" value="1" {{ old('consent') ? 'checked' : '' }}>
            <span>I consent to recording and processing my video for this Teacher's Day tribute.</span>
        </label>
        <small class="field-error consent-error" data-error-for="consent">@error('consent'){{ $message }}@enderror</small>

        <button id="registrationSubmit" class="btn-gold wide" type="submit">Continue to recording <span>→</span></button>
        <p class="secure-note">Your recording is stored securely.</p>
    </form>
</section>
@endsection

@push('scripts')
<script>
const registrationForm = document.querySelector('#registrationForm');
const registrationSubmit = document.querySelector('#registrationSubmit');
const rules = {
    doctor_name: value => value.length >= 2 ? '' : 'Please enter your name.',
    speciality: value => value.length >= 2 ? '' : 'Please enter the speciality.',
    city: value => value.length >= 2 ? '' : 'Please enter the city.',
    consent: value => value ? '' : 'Please accept the consent checkbox to continue.',
};

function fieldValue(name) {
    const field = registrationForm.elements[name];
    return field.type === 'checkbox' ? field.checked : field.value.trim();
}

function validateField(name) {
    const field = registrationForm.elements[name];
    const error = rules[name](fieldValue(name));
    const errorElement = registrationForm.querySelector(`[data-error-for="${name}"]`);
    errorElement.textContent = error;
    field.classList.toggle('is-invalid', Boolean(error));
    field.setAttribute('aria-invalid', error ? 'true' : 'false');
    return !error;
}

Object.keys(rules).forEach(name => {
    const field = registrationForm.elements[name];
    field.addEventListener(field.type === 'checkbox' ? 'change' : 'blur', () => validateField(name));
    if (field.type !== 'checkbox') {
        field.addEventListener('input', () => {
            if (field.classList.contains('is-invalid')) validateField(name);
        });
    }
});

registrationForm.addEventListener('submit', event => {
    const names = Object.keys(rules);
    const validity = names.map(name => validateField(name));
    const invalidField = names[validity.indexOf(false)];
    if (invalidField) {
        event.preventDefault();
        registrationForm.elements[invalidField].focus();
        return;
    }

    registrationSubmit.disabled = true;
    registrationSubmit.textContent = 'Opening camera...';
});
</script>
@endpush
