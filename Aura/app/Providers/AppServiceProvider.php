<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
public function register(): void
{
    $helpers = app_path('helpers.php');
    if (file_exists($helpers)) {
        require_once $helpers;
    }
}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (session()->has('locale')) {
        App::setLocale(session('locale'));
         }
    }
}
