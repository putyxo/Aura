<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

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
    View::composer('*', function () {
        if (Auth::check() && Auth::user()->idioma) {
            app()->setLocale(Auth::user()->idioma);
        }
    });
}
}
