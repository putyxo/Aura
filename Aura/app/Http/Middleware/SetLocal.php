<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle($request, Closure $next)
    {
        // Si ya hay idioma en sesión, úsalo
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        } else {
            // Detectar idioma del navegador
            $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

            // Aceptar solo idiomas soportados
            if (in_array($locale, ['es', 'en'])) {
                App::setLocale($locale);
                Session::put('locale', $locale);
            } else {
                App::setLocale('en'); // idioma por defecto
            }
        }

        return $next($request);
    }
}
