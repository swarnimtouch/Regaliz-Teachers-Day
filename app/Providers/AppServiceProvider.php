<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $settings = Schema::hasTable('settings')
                ? Setting::whereIn('key', ['campaign_logo', 'campaign_title', 'campaign_subtitle'])->pluck('value', 'key')
                : collect();

            $logo = $settings->get('campaign_logo');
            $view->with([
                'campaignLogoUrl' => $logo ? asset($logo) : asset('images/regaliz-logo.png'),
                'campaignTitle' => $settings->get('campaign_title', "Teacher's Day Tribute"),
                'campaignSubtitle' => $settings->get('campaign_subtitle', 'A heartfelt mentor message'),
            ]);
        });
    }
}
