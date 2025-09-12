<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Determinar el idioma actual en cada request
        $this->app->booted(function () {
            if (Session::has('locale') && Auth::guest()) {
                App::setLocale(Session::get('locale'));
            } elseif (Auth::check() && Auth::user()->idioma) {
                App::setLocale(Auth::user()->idioma);
            }
        });

        // Registrar helper global current_locale()
        if (!function_exists('current_locale')) {
            function current_locale(): string
            {
                return App::getLocale();
            }
        }
    }
}
