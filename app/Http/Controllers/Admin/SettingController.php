<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', ['settings' => Setting::pluck('value', 'key')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'campaign_title' => ['required', 'string', 'max:100'], 'campaign_subtitle' => ['required', 'string', 'max:255'],
            'campaign_quote' => ['nullable', 'string', 'max:500'], 'recording_min_seconds' => ['required', 'integer', 'between:1,20'],
            'recording_max_seconds' => ['required', 'integer', 'between:5,90', 'gte:recording_min_seconds'], 'upload_max_mb' => ['required', 'integer', 'between:1,200'],
            'campaign_active' => ['nullable', 'boolean'],
            'campaign_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        unset($data['campaign_logo']);

        if ($request->hasFile('campaign_logo')) {
            $oldLogo = Setting::where('key', 'campaign_logo')->value('value');
            if ($oldLogo && str_starts_with($oldLogo, 'uploads/branding/')) {
                File::delete(public_path($oldLogo));
            }

            $file = $request->file('campaign_logo');
            $name = 'campaign-logo-'.now()->timestamp.'.'.$file->extension();
            File::ensureDirectoryExists(public_path('uploads/branding'));
            $file->move(public_path('uploads/branding'), $name);
            Setting::updateOrCreate(['key' => 'campaign_logo'], ['value' => 'uploads/branding/'.$name, 'type' => 'image']);
        }
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => (string) $value, 'type' => is_bool($value) ? 'boolean' : 'string']);
        }
        Setting::updateOrCreate(['key' => 'campaign_active'], ['value' => $request->boolean('campaign_active') ? '1' : '0', 'type' => 'boolean']);

        return back()->with('success', 'Settings saved.');
    }
}
