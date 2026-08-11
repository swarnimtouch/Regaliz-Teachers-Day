<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'campaign_title' => ['Happy Teacher\'s Day', 'string'],
            'campaign_subtitle' => ['Celebrate the mentors who shape every healer.', 'string'],
            'campaign_quote' => ['A good teacher inspires hope and ignites imagination.', 'string'],
            'recording_min_seconds' => ['5', 'integer'],
            'recording_max_seconds' => ['20', 'integer'],
            'upload_max_mb' => ['50', 'integer'],
            'campaign_active' => ['1', 'boolean'],
        ];

        foreach ($settings as $key => [$value, $type]) {
            Setting::query()->updateOrCreate(['key' => $key], compact('value', 'type'));
        }
    }
}
