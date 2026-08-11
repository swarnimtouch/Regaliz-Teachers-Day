@extends('admin.layout')

@section('title', 'My Profile')
@section('heading', 'Edit profile')

@section('content')
    <div class="page-heading-actions">
        <p>Manage your admin account details and profile image.</p>
        <a class="btn-outline" href="{{ route('admin.profile.password.edit') }}"><i class="fa-solid fa-key"></i> Change password</a>
    </div>

    <section class="admin-panel profile-edit-panel">
        <div class="profile-head profile-head-large">
            @if(auth()->user()->avatar)
                <img src="{{ asset(auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}">
            @else
                <span>{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            @endif
            <div>
                <h2>{{ auth()->user()->name }}</h2>
                <p>{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</p>
            </div>
        </div>

        <form id="profileForm" class="admin-form profile-form-grid" method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" novalidate>
            @csrf
            @method('PUT')
            <label>Full name<input name="name" value="{{ old('name', auth()->user()->name) }}" maxlength="255" required><small class="js-error" data-error-for="name"></small></label>
            <label>Email address<input type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required><small class="js-error" data-error-for="email"></small></label>
            <label>Profile photo<input type="file" name="avatar" accept="image/png,image/jpeg,image/webp"><small class="js-error" data-error-for="avatar"></small><small class="upload-note">JPG, PNG or WebP. Maximum 2 MB.</small></label>
            <div class="form-submit-row"><button class="btn-gold" type="submit"><i class="fa-solid fa-floppy-disk"></i> Save profile</button></div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        (() => {
            const form = document.querySelector('#profileForm');
            const showError = (name, message) => {
                const field = form.elements[name];
                const error = form.querySelector(`[data-error-for="${name}"]`);
                field?.classList.toggle('is-invalid', Boolean(message));
                if (error) error.textContent = message;
                return Boolean(message);
            };

            form.addEventListener('submit', event => {
                const name = form.elements.name.value.trim();
                const email = form.elements.email.value.trim();
                const avatar = form.elements.avatar.files[0];
                let invalid = false;

                invalid = showError('name', name.length < 2 ? 'Please enter at least 2 characters.' : '') || invalid;
                invalid = showError('email', /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) ? '' : 'Please enter a valid email address.') || invalid;
                invalid = showError('avatar', avatar && (!['image/jpeg', 'image/png', 'image/webp'].includes(avatar.type) || avatar.size > 2 * 1024 * 1024) ? 'Choose a JPG, PNG or WebP image under 2 MB.' : '') || invalid;

                if (invalid) event.preventDefault();
            });
        })();
    </script>
@endpush
