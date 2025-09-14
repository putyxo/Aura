<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SetUserLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Invitado con idioma en sesión
        if (Session::has('locale') && Auth::guest()) {
            App::setLocale(Session::get('locale'));

        // Usuario autenticado con idioma en BD
        } elseif (Auth::check() && Auth::user()->idioma) {
            App::setLocale(Auth::user()->idioma);
        }

        return $next($request);
    }
}
