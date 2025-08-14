<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Utility;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\App;

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
        Schema::defaultStringLength(191);
        
        // Register custom Slack OAuth v2 provider
        Socialite::extend('slack', function ($app) {
            $config = $app['config']['services.slack'];
            
            return new \App\Services\SlackSocialiteProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });
        
        // Register Zoom OAuth provider (since it's not built into Socialite)
        Socialite::extend('zoom', function ($app) {
            $config = $app['config']['services.zoom'];
            
            return new \App\Services\ZoomSocialiteProvider(
                $app['request'],
                $config['client_id'],
                $config['client_secret'],
                $config['redirect']
            );
        });

        if (!$this->app->runningInConsole()) {

            $supportedLocales = ['en', 'za-en', 'en-ke'];
            $locale = 'en'; // Default locale

            // 1. Check for locale in the URL first
            $urlLocale = request()->segment(1);
            if (in_array($urlLocale, $supportedLocales)) {
                $locale = $urlLocale;
            }
            // 2. If no locale in URL, check user settings
            else if (Auth::check()) {
                $user = Auth::user();
                $settings = Utility::settingsById($user->id);

                $currency = $settings['site_currency'] ?? 'USD';
                $country = $settings['company_country'] ?? 'USA';

                $mapping = [
                    'ZAR' => ['South Africa' => 'za-en'],
                    'KES' => ['Kenya' => 'en-ke'],
                ];

                if (isset($mapping[$currency][$country])) {
                    $locale = $mapping[$currency][$country];
                }
            }

            App::setLocale($locale);
            URL::defaults(['locale' => $locale]);
        }
    }
}