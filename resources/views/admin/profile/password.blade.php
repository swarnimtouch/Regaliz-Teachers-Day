@extends('admin.layout')

@section('title', 'Change Password')
@section('heading', 'Change password')

@section('content')
    <div class="page-heading-actions">
        <p>Choose a secure password to protect your admin account.</p>
        <a class="btn-outline" href="{{ route('admin.profile.edit') }}"><i class="fa-solid fa-user-pen"></i> Edit profile</a>
    </div>

    <section class="admin-panel password-panel">
        <div class="password-guide">
            <i class="fa-solid fa-shield-halved"></i>
            <h2>Keep your account secure</h2>
            <p>A strong password helps protect campaign data and administrator access.</p>
            <ul><li>Exactly 6 digits</li><li>Numbers only</li><li>Keep your password private</li></ul>
        </div>

        <form id="passwordForm" class="admin-form" method="POST" action="{{ route('admin.profile.password') }}" novalidate>
            @csrf
            @method('PUT')
            <label><span class="field-label">Current password <b>*</b></span><input type="password" name="current_password" autocomplete="current-password" required><small class="js-error" data-error-for="current_password"></small></label>
            <label><span class="field-label">New 6-digit password <b>*</b></span><input type="password" name="password" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="new-password" required><small class="js-error" data-error-for="password"></small></label>
            <label><span class="field-label">Confirm 6-digit password <b>*</b></span><input type="password" name="password_confirmation" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" autocomplete="new-password" required><small class="js-error" data-error-for="password_confirmation"></small></label>
            <div class="form-submit-row"><button class="btn-gold" type="submit"><i class="fa-solid fa-shield-halved"></i> Update password</button></div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('#passwordForm');
            const showError = (name, message) => {
                const field = form.elements[name];
                const error = form.querySelector(`[data-error-for="${name}"]`);
                field?.classList.toggle('is-invalid', Boolean(message));
                if (error) error.textContent = message;
                return Boolean(message);
            };

            form.addEventListener('submit', event => {
                const current = form.elements.current_password.value;
                const password = form.elements.password.value;
                const confirmation = form.elements.password_confirmation.value;
                const validPassword = /^\d{6}$/.test(password);
                let invalid = false;

                invalid = showError('current_password', current ? '' : 'Please enter your current password.') || invalid;
                invalid = showError('password', validPassword ? '' : 'Password must contain exactly 6 numeric digits.') || invalid;
                let confirmationError = '';
                if (!confirmation) {
                    confirmationError = 'Confirm 6-digit password is required.';
                } else if (confirmation !== password) {
                    confirmationError = 'Password confirmation does not match.';
                }
                invalid = showError('password_confirmation', confirmationError) || invalid;

                if (invalid) event.preventDefault();
            });
        })();
    </script>
@endpush
