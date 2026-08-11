<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.profile.edit');
    }

    public function editPassword(): View
    {
        return view('admin.profile.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore(auth()->id())],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $user = auth()->user();
            if ($user->avatar && str_starts_with($user->avatar, 'uploads/admin-profiles/')) {
                File::delete(public_path($user->avatar));
            }

            $file = $request->file('avatar');
            $name = 'admin-'.auth()->id().'-'.now()->timestamp.'.'.$file->extension();
            File::ensureDirectoryExists(public_path('uploads/admin-profiles'));
            $file->move(public_path('uploads/admin-profiles'), $name);
            $data['avatar'] = 'uploads/admin-profiles/'.$name;
        }

        auth()->user()->update($data);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function password(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'digits:6'],
        ]);
        auth()->user()->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Password changed successfully.');
    }
}
