@extends('layouts.campaign')

@section('title', 'Create Your Tribute')

@section('content')
<section class="form-page section-wrap">
    <div class="form-intro">
        <div class="eyebrow"><span>01</span> Your details</div>
        <h1>Let's make it <em>personal.</em></h1>
        <p>These details will appear beautifully on your reel.</p>
        <div class="quote-mini">"Teaching is the profession that creates all other professions."</div>
    </div>

    <form id="registrationForm" class="premium-form" method="POST" action="{{ route('campaign.store') }}" novalidate>
        @csrf
        @if ($errors->any())
            <div class="alert-error">Please check the highlighted fields.</div>
        @endif

        <label>
            Doctor name <span>*</span>
            <input name="doctor_name" value="{{ old('doctor_name') }}" placeholder="Dr. Aanya Sharma" maxlength="100" autocomplete="name">
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

        <div class="form-grid">
            <label>
                Mobile <i>Optional</i>
                <input name="mobile" value="{{ old('mobile') }}" placeholder="+91 98765 43210" inputmode="tel" maxlength="20" autocomplete="tel">
                <small class="field-error" data-error-for="mobile">@error('mobile'){{ $message }}@enderror</small>
            </label>
            <label>
                Hospital <i>Optional</i>
                <input name="hospital_name" value="{{ old('hospital_name') }}" placeholder="City Care Hospital" maxlength="150">
                <small class="field-error" data-error-for="hospital_name">@error('hospital_name'){{ $message }}@enderror</small>
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
    doctor_name: value => value.length >= 2 ? '' : 'Please enter the doctor name.',
    speciality: value => value.length >= 2 ? '' : 'Please enter the speciality.',
    city: value => value.length >= 2 ? '' : 'Please enter the city.',
    mobile: value => !value || /^[0-9+() -]{7,20}$/.test(value) ? '' : 'Enter a valid mobile number.',
    hospital_name: value => value.length <= 150 ? '' : 'Hospital name must be within 150 characters.',
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
