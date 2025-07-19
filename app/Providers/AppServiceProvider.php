<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Laravel\Socialite\Facades\Socialite;

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
    }
}