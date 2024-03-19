<?php

namespace App\Providers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\ParallelTesting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        ParallelTesting::setUpTestDatabase(function (string $database, int $token) {
            Artisan::call('db:seed');
        });

        if (App::environment('production')) {
            URL::forceRootUrl(Config::get('app.url'));
            if (str_contains(Config::get('app.url'), 'https://')) {
                URL::forceScheme('https');
            }        
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {

    }
}
